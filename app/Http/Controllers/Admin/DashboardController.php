<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Order; 
use App\Models\User;
use App\Models\MenuItem;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. STATISTIK UTAMA
        // Hitung total uang dari order yang 'completed'
        $totalSales = Order::where('status', 'completed')->sum('total_price');
        
        // Hitung order baru (status 'pending')
        // Catatan: Pastikan status default di database Anda 'pending' atau 'new'. 
        // Di migrasi sebelumnya kita set 'pending'.
        $newOrders = Order::where('status', 'pending')->count(); 
        
        // Hitung Total Customer (Hanya user yang role-nya 'customer')
        $totalCustomers = User::where('role', 'customer')->count();

        // 2. MENU TERLARIS (Best Seller)
        // Kita cari menu_item_id yang paling banyak muncul di tabel order_details
        $bestSellerItem = DB::table('order_details')
            ->select('menu_item_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('menu_item_id')
            ->orderByDesc('total_quantity')
            ->first();
            
        $bestSeller = 'Belum ada data';
        if ($bestSellerItem) {
            $menu = MenuItem::find($bestSellerItem->menu_item_id);
            if ($menu) {
                $bestSeller = $menu->name;
            }
        }

        // 3. DATA TRANSAKSI TERAKHIR (REAL TIME)
        // PENTING: Gunakan 'with' agar bisa ambil nama user di View ($order->user->name)
        $recentOrders = Order::with('user')
                             ->latest() // Urutkan dari yang terbaru
                             ->limit(5)
                             ->get();

        // 4. DATA UNTUK GRAFIK (7 HARI TERAKHIR)
        $chartData = Order::select(
                        DB::raw('DATE(created_at) as date'), 
                        DB::raw('SUM(total_price) as total')
                     )
                     ->where('status', 'completed')
                     ->where('created_at', '>=', now()->subDays(7))
                     ->groupBy('date')
                     ->orderBy('date', 'asc')
                     ->get();
        
        // Format data untuk Chart.js
        $chartLabels = $chartData->pluck('date')->map(function($date) {
            return date('D', strtotime($date)); // Sen, Sel, Rab...
        });
        $chartValues = $chartData->pluck('total');

        return view('admin.dashboard', [
            'totalSales'     => $totalSales,
            'newOrders'      => $newOrders,
            'bestSeller'     => $bestSeller,
            'totalCustomers' => $totalCustomers,
            'recentOrders'   => $recentOrders,
            'chartLabels'    => $chartLabels,
            'chartValues'    => $chartValues
        ]);
    }
}