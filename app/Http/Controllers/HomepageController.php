<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuItem; // Model Menu
use App\Models\Review;   // Model Review

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
        // Pastikan nama view ini ('beranda') sesuai dengan nama file di resources/views/
        // Jika file Anda bernama 'index.blade.php', ubah 'beranda' jadi 'index'.
        return view('beranda', [
            'menu_items' => $menuItems,
            'reviews'    => $reviews
        ]);
    }

    /**
     * Menangani Scan QR Code dari Meja.
     * URL: /scan/{area}/{table}
     */
    public function scanQr($area, $table)
    {
        // 1. Bersihkan format URL (mengubah %20 menjadi spasi jika ada)
        $cleanArea = urldecode($area);

        // 2. Simpan Data Meja & Area ke Session Browser Pengguna
        // Ini membuat HP pelanggan "ingat" mereka duduk di mana
        session([
            'dining_option' => 'dine_in', // Otomatis set Makan di Tempat
            'table_area'    => $cleanArea,
            'table_number'  => $table
        ]);

        // 3. LEMPAR (REDIRECT) KE HALAMAN MENU
        // Supaya pelanggan langsung bisa pesan makan
        return redirect()->route('menu.index')->with('success', "Selamat Datang di $cleanArea - Meja $table");
    }
}