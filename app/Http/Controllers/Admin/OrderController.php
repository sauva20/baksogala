<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order; 
use App\Models\OrderDetail;
use Carbon\Carbon;

class OrderController extends Controller
{
    // --- HALAMAN UTAMA PESANAN ---
    public function index(Request $request)
    {
        // Ambil status dari URL, default ke 'today' (Hari Ini)
        $status = $request->get('status', 'today');
        
        $query = Order::query()->orderBy('created_at', 'desc');

        // --- LOGIKA FILTER ---
        if ($status == 'today') {
            // Jika tab 'Hari Ini', ambil pesanan tanggal sekarang saja
            $query->whereDate('created_at', Carbon::now()->toDateString());
        } elseif ($status && $status != 'all') {
            // Jika tab status (Baru/Dimasak/dll), filter kolom status
            $query->where('status', $status);
        }

        // Pagination 10 item per halaman
        $orders = $query->paginate(10);
        
        // Pastikan parameter URL tetap ada saat pindah halaman (pagination)
        $orders->appends(['status' => $status]);

        return view('admin.orders.index', compact('orders'));
    }

    // --- UPDATE STATUS MANUAL (TOMBOL) ---
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string'
        ]);

        $order = Order::findOrFail($id);
        $order->status = $request->status;
        
        // Jika status diubah jadi 'ready' atau 'completed', otomatis lunas
        if (in_array($request->status, ['ready', 'completed'])) {
            $order->payment_status = 'paid';
        }

        $order->save();

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui!');
    }

    // --- API: CEK PESANAN BARU (KHUSUS YANG SUDAH LUNAS) ---
    // Dipanggil oleh Javascript (setInterval) setiap 5 detik
    public function checkNewOrders(Request $request)
    {
        $clientLastId = $request->input('last_id', 0);

        // 1. LOGIKA AUTO-CANCEL (Kebersihan Data)
        // Batalkan pesanan 'new' yang belum bayar lebih dari 10 menit
        Order::where('status', 'new')
             ->where('payment_status', 'pending')
             ->where('created_at', '<', Carbon::now()->subMinutes(10))
             ->update(['status' => 'cancelled']);

        // 2. CEK PESANAN BARU (HANYA YANG SUDAH PAID)
        // Kita kembalikan filter 'paid' agar tidak sembarang pesanan masuk ke dapur
        $newOrder = Order::where('id', '>', $clientLastId)
            ->where('payment_status', 'paid') // <--- FILTER UTAMA: HANYA YANG LUNAS
            ->where('status', '!=', 'cancelled')
            ->orderBy('id', 'asc')
            ->first();

        if ($newOrder) {
            return response()->json([
                'has_new'   => true, 
                'latest_id' => $newOrder->id,
                'type'      => 'success',  // Warna Hijau (Lunas)
                'title'     => '💰 PESANAN LUNAS!',
                'message'   => "Pesanan #{$newOrder->id} dari {$newOrder->customer_name} telah dibayar. Silakan siapkan pesanan!"
            ]);
        }

        return response()->json(['has_new' => false]);
    }

    // --- API: POPUP DETAIL MODAL ---
    public function getOrderDetail($id)
    {
        // Load relasi orderDetails dan menuItem
        $order = Order::with(['orderDetails.menuItem'])->find($id);
        
        if(!$order) {
            return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        $items = $order->orderDetails->map(function($detail) {
            return [
                'quantity'   => $detail->quantity,
                'menu_name'  => $detail->menuItem->name ?? 'Item Dihapus',
                'price'      => number_format($detail->price, 0, ',', '.'),
                'subtotal'   => number_format($detail->subtotal, 0, ',', '.'),
                'item_notes' => $detail->item_notes ?? '-'
            ];
        });

        return response()->json([
            'status' => 'success',
            'order' => [
                'id' => $order->id,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'shipping_address' => $order->shipping_address,
                'table_number' => $order->table_number ?? '-', 
                'status' => ucfirst($order->status),
                'payment_status' => ucfirst($order->payment_status),
                'total_price' => number_format($order->total_price, 0, ',', '.'),
                'created_at' => $order->created_at->format('d M Y H:i'),
                'notes' => $order->order_notes ?? '-'
            ],
            'items' => $items
        ]);
    }
}