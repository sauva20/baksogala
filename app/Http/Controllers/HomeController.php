<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Import Model User

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    /**
     * FUNGSI KHUSUS MENYIMPAN TOKEN DARI AJAX
     * Ini yang dicari oleh route /update-fcm-token
     */
    public function updateToken(Request $request)
    {
        try {
            // Update token milik user yang sedang login
            $request->user()->update([
                'fcm_token' => $request->token
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Token berhasil disimpan!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal simpan: ' . $e->getMessage()
            ], 500);
        }
    }
}