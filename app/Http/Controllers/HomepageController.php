<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Review; // <-- 1. WAJIB IMPORT MODEL REVIEW
use Illuminate\Http\Request;

class HomepageController extends Controller
{
    /**
     * Menampilkan halaman utama dengan data menu DAN review pilihan AI.
     */
    public function index()
    {
        // ----------------------------------------------------
        // 1. AMBIL MENU UNGGULAN (Sesuai kode lama Anda)
        // ----------------------------------------------------
        $menuItems = MenuItem::where('show_on_homepage', true)
                               ->orderBy('name')
                               ->take(6) // Saya ubah jadi 6 agar grid terlihat lebih penuh (opsional)
                               ->get();

        // ----------------------------------------------------
        // 2. AMBIL REVIEW UNGGULAN (HASIL KURASI AI)
        // ----------------------------------------------------
        // - with('order'): Kita butuh data order untuk mengambil nama customer
        // - where('is_featured', true): Hanya ambil yang disetujui AI
        // - latest(): Urutkan dari yang paling baru
        // - take(5): Batasi 5 review saja agar slider tidak keberatan
        // ----------------------------------------------------
        $reviews = Review::with('order')
                         ->where('is_featured', true)
                         ->latest()
                         ->take(5)
                         ->get();

        // ----------------------------------------------------
        // 3. KIRIM SEMUA DATA KE VIEW
        // ----------------------------------------------------
        // Pastikan nama view sesuai dengan file Anda ('beranda' atau 'home')
        return view('beranda', [
            'menu_items' => $menuItems,
            'reviews'    => $reviews  // <-- Kirim data review ke view
        ]);
    }
}