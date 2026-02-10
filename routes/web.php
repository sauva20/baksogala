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
use App\Http\Controllers\HomeController; // Pastikan ini ada untuk Scan QR

// --- CONTROLLER ADMIN (BACKEND) ---
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\LogController;

// --- ALIAS CONTROLLER ADMIN (Agar tidak bentrok nama) ---
use App\Http\Controllers\Admin\MenuController as AdminMenuController; 
use App\Http\Controllers\Admin\OrderController as AdminOrderController; 

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

// --- [PENTING] ROUTE SCAN QR CODE ---
// Route ini menangkap hasil scan QR dari meja (Contoh: /scan/Lantai-2/5)
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

// Edit Catatan & Info Meja (AJAX)
Route::post('/cart/update-note', [CartController::class, 'updateNote'])->name('cart.updateNote');
Route::post('/cart/save-info', [CartController::class, 'saveInfo'])->name('cart.saveInfo');


// -----------------------------------------------------------
// CHECKOUT & TRANSAKSI
// -----------------------------------------------------------
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

// 1. Halaman Pembayaran / Struk (Midtrans & Status Order)
Route::get('/pesanan/{id}', [OrderController::class, 'show'])->name('orders.show');

// 2. Halaman Detail Pesanan Lengkap (Untuk melihat item & form review)
Route::get('/pesanan/{id}/detail', [OrderController::class, 'detail'])->name('orders.detail');

// -----------------------------------------------------------
// FITUR REVIEW & AI
// -----------------------------------------------------------
// Proses Kirim Review (Bintang & Foto)
Route::post('/pesanan/{id}/review', [OrderController::class, 'storeReview'])->name('orders.review.store');

// AI Polish Review (Memperbaiki kalimat review user)
Route::post('/review/polish', [OrderController::class, 'polishReview'])->name('review.polish');


// -----------------------------------------------------------
// OTENTIKASI (Login/Register/Logout)
// -----------------------------------------------------------
Route::get('/auth', [AuthController::class, 'index'])->name('login'); 
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/register', [AuthController::class, 'register'])->name('register.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// ====================================================
// 2. AREA MEMBER (WAJIB LOGIN)
// ====================================================
Route::middleware(['auth'])->group(function () {
    // Route khusus member (profil, history) bisa ditambahkan di sini
});


// ====================================================
// 3. AREA ADMIN (PANEL KELOLA)
// ====================================================

// Login Khusus Admin (Halaman Login Admin)
Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout');

// Group Route Admin (PROTECTED / WAJIB LOGIN)
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // --- MANAJEMEN PESANAN ---
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::patch('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
    
    // API Notifikasi Realtime & Detail Modal (AJAX)
    Route::get('/orders/check-new', [AdminOrderController::class, 'checkNewOrders'])->name('orders.checkNew');
    Route::get('/orders/{id}/detail', [AdminOrderController::class, 'getOrderDetail'])->name('orders.detail');

    // --- MANAJEMEN MENU ---
    Route::resource('menu', AdminMenuController::class)->except(['create', 'show', 'edit']);

    // --- GENERATOR QR CODE MEJA ---
    // Halaman untuk Admin mencetak QR Code
    Route::get('/generate-qr', function () {
        $areas = ['Lantai 2 Gym', 'Indoor More', 'Depan Utama'];
        $totalMejaPerArea = 20; 
        return view('admin.print_qr', compact('areas', 'totalMejaPerArea'));
    })->name('qr.generate');

    // --- MANAJEMEN LAINNYA ---
    Route::resource('promotions', PromotionController::class);
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    
    // Manajemen User
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');

});