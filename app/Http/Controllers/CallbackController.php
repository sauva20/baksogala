<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Http;
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
            $orderId = $notif->order_id; // Contoh: "12-1738500000"
            
            // 2. Ambil ID Order Asli
            $realOrderId = explode('-', $orderId)[0];
            
            // 3. Cari Order di Database
            $order = Order::find($realOrderId);

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            // Jika status database sudah lunas, jangan diproses ulang (cegah duplikasi notif)
            if ($order->payment_status == 'paid') {
                return response()->json(['message' => 'Order already processed']);
            }

            // 4. Update Status Berdasarkan Laporan Midtrans
            if ($transaction == 'capture' || $transaction == 'settlement') {
                
                // A. UPDATE STATUS JADI LUNAS
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'process' // Langsung ubah ke 'diproses' agar masuk tab dapur
                ]);

                // B. KIRIM NOTIFIKASI BERLAPIS (Hanya saat lunas)
                $this->sendNotificationToAdmin($order);
                $this->sendTelegramNotif($order);

            } 
            else if ($transaction == 'pending') {
                $order->update(['payment_status' => 'pending']);
            } 
            else if ($transaction == 'deny' || $transaction == 'expire' || $transaction == 'cancel') {
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

    /**
     * FUNGSI 1: NOTIFIKASI TELEGRAM
     */
    private function sendTelegramNotif($order)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        if (!$token || !$chatId) return;

        $message = "💰 *PESANAN LUNAS!* 💰\n\n";
        $message .= "🆔 *Order ID:* #{$order->id}\n";
        $message .= "👤 *Pelanggan:* {$order->customer_name}\n";
        $message .= "📍 *Lokasi:* {$order->shipping_address}\n";
        $message .= "🍲 *Tipe:* {$order->order_type}\n";
        $message .= "💰 *Total:* Rp " . number_format($order->total_price, 0, ',', '.') . "\n\n";
        $message .= "✅ *Segera siapkan pesanan di dapur!*";

        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }

    /**
     * FUNGSI 2: NOTIFIKASI FIREBASE (WEB PUSH)
     */
    private function sendNotificationToAdmin($order)
    {
        // Cari admin/owner yang punya fcm_token
        $tokens = User::whereIn('role', ['owner', 'admin'])
                      ->whereNotNull('fcm_token')
                      ->pluck('fcm_token')
                      ->toArray();

        if (empty($tokens)) return;

        $serverKey = env('FIREBASE_SERVER_KEY');
        $url = 'https://fcm.googleapis.com/fcm/send';

        $payload = [
            "registration_ids" => $tokens,
            "notification" => [
                "title" => "💰 PESANAN LUNAS!",
                "body" => "Order #{$order->id} dari {$order->customer_name} SUDAH DIBAYAR.",
                "icon" => asset('assets/images/GALA.png'),
                "sound" => "default",
                "click_action" => url('/admin/orders')
            ],
            "data" => [
                "order_id" => $order->id
            ],
            "priority" => "high",
            "content_available" => true
        ];

        Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ])->post($url, $payload);
    }
}