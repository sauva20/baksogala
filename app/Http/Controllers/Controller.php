<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\User; // <--- PENTING: Jangan lupa import Model User

abstract class Controller
{
    // Jika Laravel 11, trait ini mungkin tidak wajib, tapi aman dipakai
    // use AuthorizesRequests, DispatchesJobs, ValidatesRequests; 

    /**
     * FUNGSI UNTUK KIRIM NOTIFIKASI KE HP ADMIN/OWNER/KASIR
     * Fungsi ini bisa dipanggil dari controller mana saja (anaknya).
     */
    public function sendNotifToAdmin($title, $body)
    {
        // 1. Ambil token milik semua Owner, Admin, & Kasir
        // Pastikan kolom 'role' dan 'fcm_token' ada di tabel users
        $tokens = User::whereIn('role', ['owner', 'admin', 'kasir'])
                      ->whereNotNull('fcm_token')
                      ->pluck('fcm_token')
                      ->toArray();

        if (empty($tokens)) {
            return "Gagal: Tidak ada token device admin/kasir ditemukan."; 
        }

        // 2. SERVER KEY FIREBASE (Legacy)
        // GANTI DENGAN SERVER KEY ASLI DARI FIREBASE CONSOLE ANDA
        $serverKey = 'MASUKKAN_SERVER_KEY_PANJANG_ANDA_DISINI'; 

        // 3. Siapkan Data Notifikasi
        $data = [
            "registration_ids" => $tokens, // Kirim ke banyak device sekaligus
            "notification" => [
                "title" => $title,
                "body" => $body,
                "icon" => "/assets/images/GALA.png", // Ganti sesuai path logo Anda
                "sound" => "default",
                "click_action" => url('/admin/orders') // Link saat notif diklik
            ]
        ];

        // 4. Kirim ke Firebase via CURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: key=' . $serverKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Matikan verifikasi SSL sementara biar aman di localhost
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $result = curl_exec($ch);
        
        if ($result === FALSE) {
            return 'Curl failed: ' . curl_error($ch);
        }
        
        curl_close($ch);

        return $result;
    }
}