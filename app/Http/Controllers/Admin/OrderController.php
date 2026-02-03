<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order; 
use App\Models\OrderDetail;
use Carbon\Carbon; // Penting untuk logika waktu (10 menit)

class OrderController extends Controller
{
    // --- HALAMAN UTAMA PESANAN ---
    public function index(Request $request)
    {
        $status = $request->get('status');
        
        // Gunakan Eloquent untuk mengambil data
        $query = Order::query()->orderBy('created_at', 'desc');

        // Filter berdasarkan status jika ada
        if ($status && $status != 'all') {
            $query->where('status', $status);
        }

        // Pagination 10 item per halaman
        $orders = $query->paginate(10);
        
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
        
        // Jika status diubah jadi completed/ready, kita anggap sudah dibayar (opsional)
        if (in_array($request->status, ['ready', 'completed'])) {
            $order->payment_status = 'paid';
        }

        $order->save();

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui!');
    }

    // --- API: CEK PESANAN BARU & AUTO CANCEL (LOGIKA KOMPLEKS) ---
    // Dipanggil oleh Javascript (setInterval) setiap beberapa detik
    public function checkNewOrders(Request $request)
    {
        $clientLastId = $request->input('last_id', 0);

        // 1. LOGIKA AUTO-CANCEL (Cek pesanan 'unpaid' yang > 10 menit)
        // Pastikan tabel orders Anda punya kolom 'payment_status' (paid/unpaid)
        // Jika tidak ada kolom payment_status, ganti dengan logika status biasa (misal status 'new')
        $expiredCount = Order::where('payment_status', 'unpaid') 
            ->where('status', '!=', 'cancelled') // Jangan update yang sudah cancel
            ->where('created_at', '<', Carbon::now()->subMinutes(10)) // Lebih tua dari 10 menit yang lalu
            ->update(['status' => 'cancelled']);

        // 2. CEK PESANAN BARU
        // Ambil 1 pesanan terlama yang ID-nya lebih besar dari last_id di browser admin
        // Kita ambil yang terlama (ASC) agar notifikasi muncul berurutan jika ada banyak pesanan sekaligus
        $newOrder = Order::where('id', '>', $clientLastId)
            ->where('status', '!=', 'cancelled') // Jangan notif kalau statusnya cancelled
            ->orderBy('id', 'asc') 
            ->first();

        if ($newOrder) {
            // Tentukan Jenis Notifikasi untuk Frontend (SweetAlert)
            $type = 'info';
            $title = 'PESANAN BARU!';
            $msg = "Pesanan #{$newOrder->id} dari {$newOrder->customer_name} masuk.";

            // Logika Status Complex
            if ($newOrder->payment_status == 'paid') {
                $type = 'success'; // Warna Hijau di JS
                $title = '💰 SUDAH DIBAYAR!';
                $msg = "Pesanan #{$newOrder->id} LUNAS. Siapkan sekarang!";
            } elseif ($newOrder->payment_status == 'unpaid') {
                $type = 'warning'; // Warna Kuning di JS
                $title = '⏳ BELUM DIBAYAR!';
                $msg = "Pesanan #{$newOrder->id} masuk tapi BELUM LUNAS. Cek bukti bayar!";
            }

            return response()->json([
                'has_new'   => true, 
                'latest_id' => $newOrder->id,
                'type'      => $type,   // Dikirim ke JS untuk icon/warna
                'title'     => $title,  // Judul Popup
                'message'   => $msg     // Isi Pesan
            ]);
        }

        // Tidak ada pesanan baru
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
                'table_number' => $order->table_number,
                'status' => ucfirst($order->status),
                'payment_status' => ucfirst($order->payment_status),
                'total_price' => number_format($order->total_price, 0, ',', '.'),
                'created_at' => $order->created_at->format('d M Y H:i'),
                'notes' => $order->notes ?? '-'
            ],
            'items' => $items
        ]);
    }
}