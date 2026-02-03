<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; // Untuk Pagination Bootstrap
use Illuminate\Support\Facades\View; // Untuk Mengirim Data ke Navbar
use Illuminate\Support\Facades\DB;   // Untuk Query Database
use Illuminate\Support\Facades\Auth; // Untuk Cek Login User
use Illuminate\Support\Facades\URL;  // Untuk Fix HTTPS Ngrok

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // -----------------------------------------------------------
        // 1. SOLUSI CSS HILANG DI NGROK (Force HTTPS)
        // -----------------------------------------------------------
        // Cek jika sedang di Production ATAU jika URL mengandung kata 'ngrok'
        if ($this->app->environment('production') || str_contains(request()->url(), 'ngrok')) {
            URL::forceScheme('https');
        }

        // -----------------------------------------------------------
        // 2. ATUR PAGINATION BOOTSTRAP
        // -----------------------------------------------------------
        Paginator::useBootstrap();

        // -----------------------------------------------------------
        // 3. LOGIKA HITUNG KERANJANG DI NAVBAR
        // -----------------------------------------------------------
        // Data $cartCount akan dikirim otomatis ke file: resources/views/partials/navbar.blade.php
        View::composer('partials.navbar', function ($view) {
            $cartCount = 0;

            try {
                // Cek apakah user login atau tamu (guest)
                if (Auth::check()) {
                    // User Login: Hitung item berdasarkan user_id
                    $cartCount = DB::table('cart_items')
                        ->where('user_id', Auth::id())
                        ->sum('quantity');
                } else {
                    // Tamu: Hitung item berdasarkan session_id (jika fitur guest cart aktif)
                    // Pastikan session sudah dimulai
                    $sessionId = session()->getId();
                    if ($sessionId) {
                        $cartCount = DB::table('cart_items')
                            ->where('session_id', $sessionId)
                            ->sum('quantity');
                    }
                }
            } catch (\Exception $e) {
                // Jika tabel belum ada atau error database, set 0 agar tidak error 500
                $cartCount = 0;
            }

            // Kirim variabel ke view agar bisa dipanggil dengan {{ $cartCount }}
            $view->with('cartCount', $cartCount);
        });
    }
}