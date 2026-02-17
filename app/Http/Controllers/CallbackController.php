<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Midtrans\Config;
use Midtrans\Notification;

class CallbackController extends Controller
{
    public function __construct()
    {
        // Konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key', env('MIDTRANS_SERVER_KEY'));
        Config::$isProduction = config('midtrans.is_production', env('MIDTRANS_IS_PRODUCTION', false));
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function handle(Request $request)
    {
        try {
            // 1. Terima notifikasi dari Midtrans
            $notif = new Notification();

            $transaction = $notif->transaction_status;
            $type = $notif->payment_type;
            $orderId = $notif->order_id; // Contoh: "12-1738500000"
            
            // 2. Ambil ID Order Asli (Buang angka unik di belakang strip)
            $realOrderId = explode('-', $orderId)[0];
            
            // 3. Cari Order di Database
            $order = Order::find($realOrderId);

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            // Jika sudah lunas, jangan diubah lagi
            if ($order->payment_status == 'paid') {
                return response()->json(['message' => 'Order already paid']);
            }

            // 4. Update Status Berdasarkan Laporan Midtrans
            if ($transaction == 'capture' || $transaction == 'settlement') {
                
                // A. UPDATE STATUS JADI LUNAS
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'process' // Langsung ubah jadi diproses
                ]);

                // ==========================================================
                // B. [TAMBAHAN] BUNYIKAN NOTIFIKASI KE OWNER/KASIR
                // ==========================================================
                // Panggil fungsi yang sudah kita buat di Controller.php utama
                $this->sendNotifToAdmin(
                    "Pesanan Masuk #{$order->id}", 
                    "Lunas! Pesanan senilai Rp " . number_format($order->total_price) . " telah dibayar via Midtrans."
                );
                // ==========================================================

            } 
            else if ($transaction == 'pending') {
                // STATUS: MENUNGGU
                $order->update(['payment_status' => 'pending']);
            } 
            else if ($transaction == 'deny' || $transaction == 'expire' || $transaction == 'cancel') {
                // STATUS: GAGAL / KADALUARSA
                $order->update([
                    'payment_status' => 'failed',
                    'status' => 'cancelled'
                ]);
            }

            return response()->json(['message' => 'Callback success']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}