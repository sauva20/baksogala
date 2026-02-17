<?php

use Illuminate\Support\Facades\Route; 

// --- CONTROLLER PENGUNJUNG (FRONTEND) ---
use App\Http\Controllers\HomepageController;
use App\Http\Controllers\MenuController; 
use App\Http\Controllers\CartController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AuthController; 
use App\Http\Controllers\CheckoutController; 
use App\Http\Controllers\OrderController; 
use App\Http\Controllers\HomeController; 

// --- CONTROLLER ADMIN (BACKEND) ---
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\ReviewController; // <--- TAMBAHKAN INI

// --- ALIAS CONTROLLER ADMIN ---
use App\Http\Controllers\Admin\MenuController as AdminMenuController; 
use App\Http\Controllers\Admin\OrderController as AdminOrderController; 

// --- MIDDLEWARE (PENTING: Agar tidak error saat Seeder) ---
use App\Http\Middleware\IsOwner; 

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ====================================================
// 1. AREA PUBLIK (BISA DIAKSES SIAPA SAJA / TAMU)
// ====================================================

// Halaman Utama & Statis
Route::get('/', [HomepageController::class, 'index'])->name('home');
Route::get('/tentang-kami', [PageController::class, 'about'])->name('about');

// --- ROUTE SCAN QR CODE ---
Route::get('/scan/{area}/{table}', [HomepageController::class, 'scanQr'])->name('scan.qr');

// Halaman Menu
Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');

// -----------------------------------------------------------
// KERANJANG BELANJA (CART)
// -----------------------------------------------------------
Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add'); 
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/update-note', [CartController::class, 'updateNote'])->name('cart.updateNote');
Route::post('/cart/save-info', [CartController::class, 'saveInfo'])->name('cart.saveInfo');

// -----------------------------------------------------------
// CHECKOUT & TRANSAKSI
// -----------------------------------------------------------
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

// Halaman Pembayaran & Detail
Route::get('/pesanan/{id}', [OrderController::class, 'show'])->name('orders.show');
Route::get('/pesanan/{id}/detail', [OrderController::class, 'detail'])->name('orders.detail');

// --- CETAK STRUK ---
Route::get('/pesanan/{id}/cetak', [OrderController::class, 'cetakStruk'])->name('orders.cetak');

// --- API KECIL UNTUK CEK STATUS ---
Route::get('/pesanan/{id}/status', [OrderController::class, 'checkStatus'])->name('orders.status');

// -----------------------------------------------------------
// FITUR REVIEW & AI
// -----------------------------------------------------------
Route::post('/pesanan/{id}/review', [OrderController::class, 'storeReview'])->name('orders.review.store');
Route::post('/review/polish', [OrderController::class, 'polishReview'])->name('review.polish');

// -----------------------------------------------------------
// OTENTIKASI (Login/Register/Logout User Biasa)
// -----------------------------------------------------------
Route::get('/auth', [AuthController::class, 'index'])->name('login'); 
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/register', [AuthController::class, 'register'])->name('register.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// ====================================================
// 2. AREA MEMBER (WAJIB LOGIN)
// ====================================================
Route::middleware(['auth'])->group(function () {
    // Route member bisa ditambahkan di sini
});


// ====================================================
// 3. AREA ADMIN (PANEL KELOLA)
// ====================================================

// Login Khusus Admin
Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login.submit');

// Group Route Admin (PROTECTED / WAJIB LOGIN)
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    
    // Logout Admin
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // -------------------------------------------------------
    // A. AKSES BERSAMA (OWNER & KASIR)
    // -------------------------------------------------------
    // Kasir HANYA bisa akses Pesanan, Menu, & Review
    
    // 1. Manajemen Pesanan
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::patch('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::get('/orders/check-new', [AdminOrderController::class, 'checkNewOrders'])->name('orders.checkNew');
    Route::get('/orders/{id}/detail', [AdminOrderController::class, 'getOrderDetail'])->name('orders.detail');

    // 2. Manajemen Menu
    Route::resource('menu', AdminMenuController::class)->except(['create', 'show', 'edit']);

    // 3. Manajemen Review (Monitoring Homepage) <--- TAMBAHKAN INI
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::patch('/reviews/{review}/toggle', [ReviewController::class, 'toggleFeatured'])->name('reviews.toggle');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');


    // -------------------------------------------------------
    // B. AKSES KHUSUS OWNER (KASIR DILARANG MASUK)
    // -------------------------------------------------------
    Route::middleware([IsOwner::class])->group(function () {
        
        // Dashboard Statistik
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Generate QR Code Area
        Route::get('/generate-qr', function () {
            $areas = ['Lantai 2 Gym', 'Indoor More', 'Depan Utama', 'Area Photobooth'];
            $totalMejaPerArea = 20; 
            return view('admin.print_qr', compact('areas', 'totalMejaPerArea'));
        })->name('qr.generate');

        // Manajemen Promosi
        Route::resource('promotions', PromotionController::class);
        
        // Laporan Keuangan & Analisa
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        
        // Log Aktivitas Sistem
        Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
        
        // Manajemen User (Data Pelanggan & Staff)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    });

});