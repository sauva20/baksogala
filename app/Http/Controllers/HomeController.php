<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     * Middleware 'auth' memastikan hanya user login yang bisa akses.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Fungsi untuk menyimpan Token FCM dari HP/Browser User ke Database
     */
    public function updateToken(Request $request)
    {
        try {
            // Validasi data
            $request->validate([
                'token' => 'required|string'
            ]);

            // Simpan token ke user yang sedang login saat ini
            $request->user()->update([
                'fcm_token' => $request->token
            ]);

            return response()->json(['success' => true, 'message' => 'Token updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating token'], 500);
        }
    }
}