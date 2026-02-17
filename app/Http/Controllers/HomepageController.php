<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuItem; 
use App\Models\Review;   

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
        // 2. AMBIL REVIEW TERBARU (Untuk Section Testimonials)
        // ----------------------------------------------------
        // - with('order'): Agar nama customer dari tabel order ikut terambil
        // - where('is_featured', true): Hanya tampilkan review yang sudah lolos seleksi AI/Admin
        // - latest(): PENTING! Ini yang bikin review TERBARU muncul duluan (urutan descending)
        // - take(10): Ambil 10 review terakhir biar slidernya panjang
        $reviews = Review::with('order')
                         ->where('is_featured', true)
                         ->latest() 
                         ->take(10)
                         ->get();

        // ----------------------------------------------------
        // 3. KIRIM DATA KE VIEW
        // ----------------------------------------------------
        // Pastikan file view Anda bernama 'beranda.blade.php' di folder resources/views/
        // Jika nama filenya 'welcome.blade.php', ganti 'beranda' jadi 'welcome'
        return view('beranda', [
            'menu_items' => $menuItems, // Di view dipanggil dengan $menu_items
            'reviews'    => $reviews    // Di view dipanggil dengan $reviews
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