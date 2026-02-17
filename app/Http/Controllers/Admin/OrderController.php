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
        if (in_array($request->status, ['ready', 'completed'])) {
            $order->payment_status = 'paid';
        }

        $order->save();

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui!');
    }

    // --- API: CEK PESANAN BARU (UNTUK TEST NOTIFIKASI MASUK) ---
    public function checkNewOrders(Request $request)
    {
        $clientLastId = $request->input('last_id', 0);

        // 1. LOGIKA AUTO-CANCEL (Tetap dibiarkan untuk kebersihan data)
        Order::where('status', 'new')
             ->where('payment_status', 'pending')
             ->where('created_at', '<', Carbon::now()->subMinutes(10))
             ->update(['status' => 'cancelled']);

        // 2. CEK PESANAN BARU (TRIGGER SAAT MASUK)
        // Kita hapus filter ->where('payment_status', 'paid') agar notif langsung bunyi
        $newOrder = Order::where('id', '>', $clientLastId)
            ->where('status', '!=', 'cancelled') // Jangan beri notif jika sudah batal
            ->orderBy('id', 'asc')
            ->first();

        if ($newOrder) {
            return response()->json([
                'has_new'   => true, 
                'latest_id' => $newOrder->id,
                'type'      => 'info',  // Warna Biru (Info) untuk pesanan masuk
                'title'     => '🔔 PESANAN BARU!',
                'message'   => "Pesanan #{$newOrder->id} dari {$newOrder->customer_name} baru saja masuk. Cek detailnya!"
            ]);
        }

        return response()->json(['has_new' => false]);
    }

    // --- API: POPUP DETAIL MODAL ---
    public function getOrderDetail($id)
    {
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