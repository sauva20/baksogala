<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Review; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // 1. Menampilkan Halaman Pembayaran / Struk
    public function show($id)
    {
        $order = Order::with(['orderDetails.menuItem'])->findOrFail($id);
        if ($order->user_id != Auth::id()) abort(403, 'Akses Ditolak.');
        
        // Auto cancel 10 menit
        if (in_array($order->status, ['new', 'pending']) && $order->created_at->diffInMinutes(now()) >= 10) {
            $order->status = 'cancelled';
            $order->save();
        }
        return view('orders.show', compact('order'));
    }

    // 2. Menampilkan Halaman Detail & Review
    public function detail($id)
    {
        $order = Order::with(['orderDetails.menuItem', 'review'])->findOrFail($id);
        if ($order->user_id != Auth::id()) abort(403, 'Akses Ditolak.');
        return view('orders.detail', compact('order'));
    }

    // 3. PROSES SIMPAN REVIEW + AI AUTOMATION
    public function storeReview(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $order = Order::findOrFail($id);
        if ($order->user_id != Auth::id()) abort(403);
        
        // --- PERBAIKAN LOGIKA: HANYA BOLEH REVIEW JIKA STATUS 'COMPLETED' ---
        if ($order->status !== 'completed') {
            return back()->with('error', 'Mohon tunggu pesanan selesai disajikan baru memberikan ulasan ya!');
        }

        if (Review::where('order_id', $order->id)->exists()) return back()->with('error', 'Sudah direview.');

        // Simpan Foto (Logic Hostinger Friendly)
        $photoDbPath = null;
        $base64Image = null;
        $mimeType = null;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // Simpan langsung ke folder public/uploads/reviews
            $file->move(public_path('uploads/reviews'), $fileName);
            $photoDbPath = 'uploads/reviews/' . $fileName; 
            
            // Persiapan Base64 untuk AI
            $imageData = file_get_contents(public_path($photoDbPath));
            $base64Image = base64_encode($imageData);
            $mimeType = $file->getMimeType();
        }

        // Simpan Review ke Database
        $review = Review::create([
            'order_id' => $order->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'photo' => $photoDbPath, // Path ke public/uploads/...
            'is_featured' => false 
        ]);

        // PANGGIL AI UNTUK KURASI
        $aiResult = $this->analyzeReviewWithAI($review, $base64Image, $mimeType);

        if ($aiResult === true) {
            return back()->with('success', 'Selamat! Ulasan Anda lolos kurasi AI dan TAMPIL DI BERANDA! 🎉');
        } else {
            return back()->with('success', 'Ulasan terkirim! Terima kasih atas masukan Anda.');
        }
    }

    // 4. API KECIL UNTUK AUTO RELOAD STATUS
    public function checkStatus($id)
    {
        $order = Order::select('status', 'payment_status')->findOrFail($id);
        return response()->json([
            'status' => $order->status,
            'payment_status' => $order->payment_status
        ]);
    }

    // 5. OTAK AI (GEMINI)
    private function analyzeReviewWithAI($review, $base64Image = null, $mimeType = null)
    {
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) return false;

        $promptText = "Anda adalah Admin Media Sosial. Analisis ulasan ini.\n" .
            "KRITERIA LOLOS (featured: true):\n" .
            "- Rating minimal 4.\n" .
            "- Komentar positif.\n" .
            "- FOTO: Makanan/Minuman/Selfie sopan BOLEH.\n" .
            "- TOLAK HANYA JIKA: Foto tidak senonoh atau tidak pantas.\n" .
            "Output JSON MURNI: { \"featured\": true/false, \"reason\": \"...\" }\n" .
            "Data: Rating {$review->rating}, Komentar: '{$review->comment}'";

        $parts = [['text' => $promptText]];
        if ($base64Image) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $mimeType,
                    'data' => $base64Image
                ]
            ];
        }

        try {
            $response = Http::withoutVerifying()
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}", [
                    'contents' => [['parts' => $parts]]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $jsonStr = str_replace(['```json', '```'], '', $rawText);
                $result = json_decode($jsonStr, true);

                if ($result) {
                    $review->update([
                        'is_featured' => ($result['featured'] == true) ? 1 : 0,
                        'ai_analysis' => $result['reason'] ?? 'Analyzed'
                    ]);
                    return ($result['featured'] == true);
                }
            }
        } catch (\Exception $e) {
            Log::error("Gemini Exception: " . $e->getMessage());
        }
        return false;
    }

    // 6. Fitur Polish Review
    public function polishReview(Request $request)
    {
        $request->validate(['text' => 'required|string|max:500']);
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) return response()->json(['status' => 'error', 'message' => 'API Key Missing'], 500);

        try {
            $prompt = "Perbaiki ulasan ini agar lebih asik dan menarik, tapi JANGAN terlalu baku/kaku. Pertahankan gaya bahasa santai/gaul pelanggan. Langsung berikan satu hasil terbaik saja tanpa tanda petik. Teks asli: '{$request->text}'";
            
            $response = Http::withoutVerifying()
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}", [
                    'contents' => [['parts' => [['text' => $prompt]]]]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? $request->text;
                $cleanText = trim($text, "\"' ");
                return response()->json(['status' => 'success', 'text' => $cleanText]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // 7. Cetak Struk
    public function cetakStruk($id)
    {
        // Ambil data pesanan beserta detail itemnya
        $order = Order::with(['orderDetails.menuItem'])->findOrFail($id);
        
        return view('orders.print', compact('order'));
    }
}