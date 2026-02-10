<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuItem; // Model Menu
use App\Models\Review;   // Model Review (Penting untuk Testimonial)

class HomepageController extends Controller
{
    /**
     * Menampilkan halaman utama (Landing Page).
     */
    public function index()
    {
        // ----------------------------------------------------
        // 1. AMBIL MENU UNGGULAN (Untuk Section Menu Preview)
        // ----------------------------------------------------
        // Mengambil menu yang ditandai 'show_on_homepage', urutkan nama, ambil 6
        $menuItems = MenuItem::where('show_on_homepage', true)
                             ->orderBy('name')
                             ->take(6) 
                             ->get();

        // ----------------------------------------------------
        // 2. AMBIL REVIEW PILIHAN (Untuk Section Testimonials)
        // ----------------------------------------------------
        // - with('order'): Eager load relasi order untuk ambil nama customer
        // - where('is_featured', true): Hanya ambil review yang sudah diapprove admin/AI
        // - latest(): Urutkan dari yang terbaru
        // - take(5): Ambil 5 review saja untuk slider
        $reviews = Review::with('order')
                         ->where('is_featured', true)
                         ->latest()
                         ->take(5)
                         ->get();

        // ----------------------------------------------------
        // 3. KIRIM DATA KE VIEW
        // ----------------------------------------------------
        // Pastikan nama view ini ('index') sesuai dengan nama file di resources/views/
        // Jika file Anda bernama 'beranda.blade.php', ubah 'index' jadi 'beranda'.
        return view('index', [
            'menu_items' => $menuItems,
            'reviews'    => $reviews
        ]);
    }
}