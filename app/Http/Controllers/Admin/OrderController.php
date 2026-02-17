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
        
        // Logic Tambahan: Jika status diubah jadi 'ready' atau 'completed'
        // Kita asumsikan pesanan sudah pasti lunas (aman untuk cash/manual)
        if (in_array($request->status, ['ready', 'completed'])) {
            $order->payment_status = 'paid';
        }

        $order->save();

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui!');
    }

    // --- API: CEK PESANAN BARU (KHUSUS YANG SUDAH BAYAR) ---
    // Dipanggil oleh Javascript (setInterval) setiap beberapa detik
    public function checkNewOrders(Request $request)
    {
        $clientLastId = $request->input('last_id', 0);

        // 1. LOGIKA AUTO-CANCEL (Bersihkan Data Lama)
        // Batalkan pesanan yang statusnya 'new'/'pending' DAN belum bayar > 10 menit
        Order::where('status', 'new')
             ->where('payment_status', 'pending') // Atau 'unpaid'
             ->where('created_at', '<', Carbon::now()->subMinutes(10))
             ->update(['status' => 'cancelled']);

        // 2. CEK PESANAN BARU (HANYA YANG SUDAH PAID)
        // Ambil 1 pesanan dengan ID > last_id DAN payment_status = 'paid'
        $newOrder = Order::where('id', '>', $clientLastId)
            ->where('payment_status', 'paid') // <--- FILTER UTAMA: HANYA YANG SUDAH BAYAR
            ->where('status', '!=', 'cancelled')
            ->orderBy('id', 'asc') // Ambil yang terlama dulu agar urut
            ->first();

        if ($newOrder) {
            // Siapkan Data untuk SweetAlert di Frontend
            return response()->json([
                'has_new'   => true, 
                'latest_id' => $newOrder->id,
                'type'      => 'success',  // Warna Hijau
                'title'     => '💰 PESANAN LUNAS!',
                'message'   => "Pesanan #{$newOrder->id} dari {$newOrder->customer_name} masuk & SUDAH DIBAYAR. Siapkan sekarang!"
            ]);
        }

        // Tidak ada pesanan baru yang valid
        return response()->json(['has_new' => false]);
    }

    // --- API: POPUP DETAIL MODAL ---
    public function getOrderDetail($id)
    {
        // Load relasi orderDetails dan menuItem untuk nama makanan
        $order = Order::with(['orderDetails.menuItem'])->find($id);
        
        if(!$order) {
            return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        // Format data item agar mudah dibaca JS
        $items = $order->orderDetails->map(function($detail) {
            return [
                'quantity'   => $detail->quantity,
                'menu_name'  => $detail->menuItem->name ?? 'Item Dihapus', // Handle jika menu dihapus
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
                'shipping_address' => $order->shipping_address, // Untuk Lokasi Meja
                'table_number' => $order->table_number ?? '-', 
                'status' => ucfirst($order->status),
                'payment_status' => ucfirst($order->payment_status), // Penting untuk badge LUNAS di modal
                'total_price' => number_format($order->total_price, 0, ',', '.'),
                'created_at' => $order->created_at->format('d M Y H:i'),
                'notes' => $order->order_notes ?? '-'
            ],
            'items' => $items
        ]);
    }
    // ... function lainnya ...

    public function cetakStruk($id)
    {
        // Ambil data pesanan beserta detail itemnya
        $order = Order::with(['orderDetails.menuItem'])->findOrFail($id);
        
        return view('orders.print', compact('order'));
    }

}
