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
     * Show the application dashboard.
     * FUNGSI BAWAAN LARAVEL (JANGAN DIHAPUS AGAR DASHBOARD AMAN)
     */
    public function index()
    {
        // Sesuaikan dengan view dashboard kamu, biasanya 'home' atau 'admin.dashboard'
        // Jika kamu pakai template admin, mungkin return view('admin.dashboard');
        return view('home'); 
    }

    /**
     * FUNGSI BARU: Simpan Token FCM
     */
    public function updateToken(Request $request)
    {
        try {
            // 1. Validasi data
            $request->validate([
                'token' => 'required|string'
            ]);

            // 2. Simpan token ke user yang sedang login
            $request->user()->update([
                'fcm_token' => $request->token
            ]);

            // 3. Berikan jawaban Sukses ke Javascript (Browser)
            return response()->json([
                'success' => true, 
                'message' => 'Token berhasil disimpan!'
            ]);

        } catch (\Exception $e) {
            // Jika error, kirim pesan errornya
            return response()->json([
                'success' => false, 
                'message' => 'Gagal simpan: ' . $e->getMessage()
            ], 500);
        }
    }
}