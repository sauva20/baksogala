<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Google\Client as GoogleClient; // Pastikan sudah install: composer require google/apiclient

abstract class Controller extends BaseController
{
    public function sendNotifToAdmin($title, $body)
    {
        // 1. Ambil Token dari Database
        $tokens = User::whereIn('role', ['owner', 'admin', 'kasir'])
                      ->whereNotNull('fcm_token')
                      ->pluck('fcm_token')
                      ->toArray();

        if (empty($tokens)) return "Gagal: Token di database kosong.";

        // 2. Load File JSON yang baru kamu download tadi
        // Taruh file pondasikita-465612-xxx.json di folder /storage/app/
        $path = storage_path('app/pondasikita-465612-8e7425778236.json');
        
        $client = new GoogleClient();
        $client->setAuthConfig($path);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->fetchAccessTokenWithAssertion();
        $accessToken = $client->getAccessToken()['access_token'];

        // 3. Kirim ke Firebase V1
        foreach ($tokens as $token) {
            Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ])->post('https://fcm.googleapis.com/v1/projects/pondasikita-465612/messages:send', [
                "message" => [
                    "token" => $token,
                    "notification" => [
                        "title" => $title,
                        "body"  => $body
                    ]
                ]
            ]);
        }

        return "Notifikasi Terkirim via V1!";
    }
}