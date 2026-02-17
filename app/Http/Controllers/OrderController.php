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
        
        // Auto cancel 10 menit jika masih new/pending
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

    // 3. PROSES SIMPAN REVIEW + AI AUTOMATION (DIPERBAIKI)
    public function storeReview(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $order = Order::findOrFail($id);
        if ($order->user_id != Auth::id()) abort(403);
        
        // Validasi: Hanya boleh review jika status 'completed'
        if ($order->status !== 'completed') {
            return back()->with('error', 'Mohon tunggu pesanan selesai disajikan baru memberikan ulasan ya!');
        }

        if (Review::where('order_id', $order->id)->exists()) return back()->with('error', 'Sudah direview.');

        // Logic Simpan Foto
        $photoDbPath = null;
        $base64Image = null;
        $mimeType = null;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // Simpan ke folder public/uploads/reviews
            $file->move(public_path('uploads/reviews'), $fileName);
            $photoDbPath = 'uploads/reviews/' . $fileName; 
            
            // Persiapan Base64 untuk dikirim ke AI
            try {
                $fullPath = public_path($photoDbPath);
                if (file_exists($fullPath)) {
                    $imageData = file_get_contents($fullPath);
                    $base64Image = base64_encode($imageData);
                    $mimeType = $file->getClientMimeType(); // Gunakan getClientMimeType agar lebih aman
                }
            } catch (\Exception $e) {
                Log::error("Gagal membaca gambar untuk AI: " . $e->getMessage());
            }
        }

        // Simpan Data Review ke Database TERLEBIH DAHULU
        $review = Review::create([
            'order_id' => $order->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'photo' => $photoDbPath, 
            'is_featured' => false 
        ]);

        // PANGGIL AI UNTUK KURASI (SAFE MODE)
        // Kita bungkus try-catch di sini agar jika AI error, review user TETAP MASUK
        // dan tidak menyebabkan Error 500 di layar user.
        try {
            $this->analyzeReviewWithAI($review, $base64Image, $mimeType);
        } catch (\Exception $e) {
            // Log errornya diam-diam, jangan tampilkan ke user
            Log::error("AI Review Error (Ignored): " . $e->getMessage());
        }

        return back()->with('success', 'Ulasan terkirim! Terima kasih atas masukan Anda.');
    }

    // 4. API UNTUK AUTO RELOAD STATUS
    public function checkStatus($id)
    {
        $order = Order::select('status', 'payment_status')->findOrFail($id);
        return response()->json([
            'status' => $order->status,
            'payment_status' => $order->payment_status
        ]);
    }

    // 5. OTAK AI (GEMINI 2.5 FLASH) - KURASI REVIEW (DIPERBAIKI)
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
        
        // Jika ada gambar, lampirkan ke AI
        if ($base64Image && $mimeType) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $mimeType,
                    'data' => $base64Image
                ]
            ];
        }

        try {
            // UPDATED: Tambahkan timeout(60) karena analisis gambar butuh waktu
            $response = Http::withoutVerifying()
                ->timeout(60) // Tunggu maksimal 60 detik
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [['parts' => $parts]]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                
                // Bersihkan format Markdown JSON (kadang AI membungkus dengan ```json ... ```)
                $jsonStr = str_replace(['```json', '```', "'''"], '', $rawText);
                $jsonStr = trim($jsonStr); // Hapus spasi di awal/akhir
                
                $result = json_decode($jsonStr, true);

                if (json_last_error() === JSON_ERROR_NONE && isset($result['featured'])) {
                    $review->update([
                        'is_featured' => ($result['featured'] == true) ? 1 : 0,
                        'ai_analysis' => substr($result['reason'] ?? 'Analyzed by AI', 0, 255) // Potong jika terlalu panjang
                    ]);
                    return ($result['featured'] == true);
                } else {
                    Log::warning("AI Response Invalid JSON: " . $rawText);
                }
            } else {
                Log::error("Gemini Analyze Error: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Gemini Exception in Analyze: " . $e->getMessage());
        }
        return false;
    }

    // 6. FITUR PERINDAH KATA (POLISH REVIEW)
    public function polishReview(Request $request)
    {
        $request->validate(['text' => 'required|string|max:500']);
        
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            return response()->json(['status' => 'error', 'message' => 'API Key belum disetting di .env'], 500);
        }

        try {
            $prompt = "Perbaiki ulasan ini agar lebih asik, gaul, dan menarik. JANGAN kaku. Langsung berikan hasil teksnya saja tanpa tanda petik. Teks asli: '{$request->text}'";
            
            $response = Http::withoutVerifying()
                ->timeout(30) // Tambahkan timeout juga disini
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [['parts' => [['text' => $prompt]]]]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? $request->text;
                $cleanText = trim($text, "\"' ");
                return response()->json(['status' => 'success', 'text' => $cleanText]);
            } else {
                $errorBody = $response->json();
                $errorMessage = $errorBody['error']['message'] ?? 'Unknown API Error';
                
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Gagal konek ke AI: ' . $errorMessage
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // 7. Cetak Struk
    public function cetakStruk($id)
    {
        $order = Order::with(['orderDetails.menuItem'])->findOrFail($id);
        return view('orders.print', compact('order'));
    }
}