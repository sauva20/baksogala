<?php

use Illuminate\Support\Facades\Route; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// --- CONTROLLER PENGUNJUNG ---
use App\Http\Controllers\HomepageController;
use App\Http\Controllers\MenuController; 
use App\Http\Controllers\CartController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AuthController; 
use App\Http\Controllers\CheckoutController; 
use App\Http\Controllers\OrderController; 
use App\Http\Controllers\HomeController; 

// --- CONTROLLER ADMIN ---
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\ReviewController; 

// --- ALIAS & MIDDLEWARE ---
use App\Http\Controllers\Admin\MenuController as AdminMenuController; 
use App\Http\Controllers\Admin\OrderController as AdminOrderController; 
use App\Http\Middleware\IsOwner; 

/*
|--------------------------------------------------------------------------
| Web Routes - Bakso Gala Project (Final Deployment)
|--------------------------------------------------------------------------
*/

// ====================================================
// 1. JALUR PRIORITAS & MAINTENANCE
// ====================================================

/**
 * JALUR PEMBERSIH CACHE (Buka /clear-all jika masih 404)
 */
Route::get('/clear-all', function() {
    \Artisan::call('route:clear');
    \Artisan::call('config:clear');
    \Artisan::call('cache:clear');
    \Artisan::call('view:clear');
    return "Semua Cache Hostinger Berhasil Dihapus! Silakan coba /tes-notif";
});

/**
 * JALUR TES NOTIFIKASI
 */
Route::get('/tes-notif', function () {
    try {
        // Menggunakan anonymous class karena Controller asli adalah abstract
        $controller = new class extends \App\Http\Controllers\Controller {};
        $hasil = $controller->sendNotifToAdmin("🔔 BAKSO GALA", "Tes Live dari Hostinger Berhasil!");
        return "Status Kirim: " . $hasil;
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

/**
 * JALUR DARURAT SIMPAN TOKEN (Anti Error 500)
 */
Route::post('/update-fcm-token', function (Request $request) {
    try {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Sesi habis'], 401);
        }
        
        if (!$request->token) {
            return response()->json(['success' => false, 'message' => 'Token kosong'], 400);
        }

        // Simpan langsung ke tabel users tanpa lewat Model (Bypass Fillable)
        DB::table('users')
            ->where('id', $userId)
            ->update([
                'fcm_token' => $request->token,
                'updated_at' => now()
            ]);
            
        return response()->json(['success' => true, 'message' => 'Token Saved!']);
    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
})->name('update.fcm-token');


// ====================================================
// 2. AREA PUBLIK (Tamu)
// ====================================================

Route::get('/', [HomepageController::class, 'index'])->name('home');
Route::get('/tentang-kami', [PageController::class, 'about'])->name('about');
Route::get('/scan/{area}/{table}', [HomepageController::class, 'scanQr'])->name('scan.qr');
Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');

// Keranjang Belanja
Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add'); 
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/update-note', [CartController::class, 'updateNote'])->name('cart.updateNote');
Route::post('/cart/save-info', [CartController::class, 'saveInfo'])->name('cart.saveInfo');

// Checkout & Transaksi
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/pesanan/{id}', [OrderController::class, 'show'])->name('orders.show');
Route::get('/pesanan/{id}/detail', [OrderController::class, 'detail'])->name('orders.detail');
Route::get('/pesanan/{id}/cetak', [OrderController::class, 'cetakStruk'])->name('orders.cetak');
Route::get('/pesanan/{id}/status', [OrderController::class, 'checkStatus'])->name('orders.status');

// Review
Route::post('/pesanan/{id}/review', [OrderController::class, 'storeReview'])->name('orders.review.store');
Route::post('/review/polish', [OrderController::class, 'polishReview'])->name('review.polish');

// Autentikasi
Route::get('/auth', [AuthController::class, 'index'])->name('login'); 
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/register', [AuthController::class, 'register'])->name('register.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// ====================================================
// 3. AREA ADMIN (Panel Kelola)
// ====================================================

// Login Admin
Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login.submit');

// Group Admin (Protected)
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // --- A. AKSES BERSAMA (OWNER & KASIR) ---
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::patch('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::get('/orders/check-new', [AdminOrderController::class, 'checkNewOrders'])->name('orders.checkNew');
    Route::get('/orders/{id}/detail', [AdminOrderController::class, 'getOrderDetail'])->name('orders.detail');
    
    Route::resource('menu', AdminMenuController::class)->except(['create', 'show', 'edit']);
    
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::patch('/reviews/{review}/toggle', [ReviewController::class, 'toggleFeatured'])->name('reviews.toggle');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // --- B. AKSES KHUSUS OWNER ---
    Route::middleware([IsOwner::class])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        Route::get('/generate-qr', function () {
            $areas = ['Lantai 2 Gym', 'Indoor More', 'Depan Utama', 'Area Photobooth'];
            $totalMejaPerArea = 20; 
            return view('admin.print_qr', compact('areas', 'totalMejaPerArea'));
        })->name('qr.generate');
        
        Route::resource('promotions', PromotionController::class);
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    });
});