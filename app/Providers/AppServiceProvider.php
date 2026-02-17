<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; 
use Illuminate\Support\Facades\View; 
use Illuminate\Support\Facades\DB;   
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\URL;  
use Illuminate\Support\Facades\Schema; // Tambahkan ini

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // -----------------------------------------------------------
        // 1. SOLUSI CSS HILANG DI NGROK (Force HTTPS)
        // -----------------------------------------------------------
        if ($this->app->environment('production') || str_contains(request()->url(), 'ngrok')) {
            URL::forceScheme('https');
        }

        // -----------------------------------------------------------
        // 2. FIX PAGINATION NGEBUG (Gunakan Bootstrap 5)
        // -----------------------------------------------------------
        // Pastikan pakai useBootstrapFive agar link pagination berjejer horizontal
        Paginator::useBootstrapFive();

        // -----------------------------------------------------------
        // 3. LOGIKA HITUNG KERANJANG DI NAVBAR (Optimasi)
        // -----------------------------------------------------------
        View::composer('partials.navbar', function ($view) {
            $cartCount = 0;

            try {
                // Cek apakah tabel cart_items sudah ada di DB untuk mencegah error saat migrasi awal
                if (Schema::hasTable('cart_items')) {
                    if (Auth::check()) {
                        // User Login
                        $cartCount = DB::table('cart_items')
                            ->where('user_id', Auth::id())
                            ->sum('quantity');
                    } else {
                        // Tamu (Guest)
                        $sessionId = session()->getId();
                        $cartCount = DB::table('cart_items')
                            ->where('session_id', $sessionId)
                            ->whereNull('user_id') // Pastikan hanya ambil punya tamu
                            ->sum('quantity');
                    }
                }
            } catch (\Exception $e) {
                $cartCount = 0;
            }

            // Kirim variabel ke navbar
            $view->with('cartCount', (int) $cartCount);
        });
    }
}