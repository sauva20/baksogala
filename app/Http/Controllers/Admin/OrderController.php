<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order; // Pastikan pakai Model Order
use App\Models\OrderDetail;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        
        // Gunakan Eloquent
        $query = Order::query()->orderBy('created_at', 'desc');

        if ($status && $status != 'all') {
            $query->where('status', $status);
        }

        $orders = $query->paginate(10);
        return view('admin.orders.index', compact('orders'));
    }

    public function updateStatus(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'status' => 'required|string'
        ]);

        // Update status menggunakan Eloquent
        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui!');
    }

    // --- API NOTIFIKASI ---
    public function checkNewOrders(Request $request)
    {
        $clientLastId = $request->input('last_id', 0);
        $latestOrder = Order::where('status', 'new')->orderBy('id', 'desc')->first();

        if ($latestOrder && $latestOrder->id > $clientLastId) {
            return response()->json(['has_new' => true, 'latest_id' => $latestOrder->id]);
        }
        return response()->json(['has_new' => false]);
    }

    // --- API POPUP DETAIL ---
    public function getOrderDetail($id)
    {
        $order = Order::with(['orderDetails.menuItem'])->find($id);
        
        if(!$order) return response()->json(['status' => 'error'], 404);

        $items = $order->orderDetails->map(function($detail) {
            return [
                'quantity' => $detail->quantity,
                'menu_name' => $detail->menuItem->name ?? 'Item Dihapus',
                'price' => $detail->price,
                'subtotal' => $detail->subtotal,
                'item_notes' => $detail->item_notes
            ];
        });

        return response()->json([
            'status' => 'success',
            'order' => $order,
            'items' => $items
        ]);
    }
}