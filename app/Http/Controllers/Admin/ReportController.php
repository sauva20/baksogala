<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. FILTER TANGGAL (Default: Bulan Ini)
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // 2. KPI CARDS (Statistik Utama)
        $totalRevenue = DB::table('orders')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum('total_price');

        $totalOrders = DB::table('orders')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->count();

        // Rata-rata nilai transaksi (AOV)
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Estimasi Profit (Misal profit margin 40% dari omset - Logika kasar)
        $netProfit = $totalRevenue * 0.40; 

        // 3. GRAFIK TREN PENJUALAN (Line Chart)
        $salesData = DB::table('orders')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_price) as total'))
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // 4. TOP MENU (Bar Chart / Table)
        $topProducts = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('menu_items', 'order_details.menu_item_id', '=', 'menu_items.id')
            ->select('menu_items.name', DB::raw('SUM(order_details.quantity) as total_qty'), DB::raw('SUM(order_details.subtotal) as total_income'))
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('menu_items.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // 5. PAYMENT METHOD STATS (Doughnut Chart)
        $paymentStats = DB::table('orders')
            ->select('payment_method', DB::raw('count(*) as total'))
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('payment_method')
            ->get();

        return view('admin.reports.index', compact(
            'startDate', 'endDate', 
            'totalRevenue', 'totalOrders', 'averageOrderValue', 'netProfit',
            'salesData', 'topProducts', 'paymentStats'
        ));
    }
}