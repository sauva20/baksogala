@extends('layouts.admin')

@section('title', 'Executive Dashboard')

@section('styles')
    <style>
        /* --- DASHBOARD SPECIFIC STYLES --- */
        .dashboard-container { padding: 0 10px; }
        .dashboard-title { font-size: 1.8rem; font-weight: 800; color: #2c3e50; margin-bottom: 5px; }
        
        /* Stats Grid (Responsive) */
        .stats-grid-premium { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); 
            gap: 20px; 
            margin-bottom: 30px; 
        }
        
        .stat-card-premium { 
            background: #fff; 
            border-radius: 12px; 
            padding: 20px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.05); 
            display: flex; 
            align-items: center; 
            gap: 15px; 
            border-left: 4px solid transparent;
            transition: transform 0.3s;
        }
        .stat-card-premium:hover { transform: translateY(-5px); }
        .stat-card-premium.primary-gradient { border-left-color: #B1935B; }
        
        .stat-icon { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; background: #f8f9fa; color: #B1935B; }
        .stat-details h3 { margin: 5px 0; font-size: 1.5rem; font-weight: 800; color: #333; }
        .stat-details p { margin: 0; font-size: 0.9rem; color: #888; }
        .stat-trend { font-size: 0.8rem; font-weight: 600; display: block; margin-top: 5px; }
        .stat-trend.positive { color: #2ecc71; }
        .stat-trend.text-muted { color: #95a5a6; }

        /* Dashboard Split (Chart & Actions) */
        .dashboard-split-grid { 
            display: grid; 
            grid-template-columns: 2fr 1fr; 
            gap: 25px; 
        }
        
        .card-panel { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 25px; height: 100%; }
        .panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .panel-header h3 { margin: 0; font-size: 1.2rem; color: #2c3e50; }

        /* Quick Actions Grid */
        .action-buttons-grid { 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); 
            gap: 15px; 
        }
        
        .action-btn { 
            background: #f8f9fa; 
            border: 1px solid #eee; 
            border-radius: 10px; 
            padding: 15px; 
            text-align: center; 
            text-decoration: none; 
            color: #555; 
            transition: 0.3s; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
            gap: 10px;
        }
        .action-btn:hover { background: #B1935B; color: #fff; border-color: #B1935B; }
        .action-btn .icon-box { font-size: 1.5rem; margin-bottom: 5px; }

        /* Recent Transactions (Card Style) */
        .recent-transaction-list { list-style: none; padding: 0; margin: 0; }
        .recent-transaction-list li { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding: 12px 0; 
            border-bottom: 1px solid #f0f0f0; 
        }
        .recent-transaction-list li:last-child { border-bottom: none; }
        
        .trans-icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #e3f2fd; color: #3498db; margin-right: 15px; }
        .trans-info strong { display: block; font-size: 0.95rem; color: #333; }
        .badge-status { font-size: 0.75rem; padding: 2px 8px; border-radius: 4px; font-weight: bold; }
        .badge-status.new { background: #3498db; color: #fff; }
        .badge-status.completed { background: #2ecc71; color: #fff; }
        .trans-amount { font-weight: bold; color: #2ecc71; }

        /* --- MOBILE RESPONSIVE (OVERRIDE) --- */
        @media (max-width: 992px) {
            .dashboard-split-grid { grid-template-columns: 1fr; } /* Stack vertikal di mobile */
            
            /* Header Stack */
            .header-section { flex-direction: column; align-items: flex-start; gap: 15px; }
            .date-filter { width: 100%; overflow-x: auto; display: flex; padding-bottom: 5px; gap: 10px; }
            .date-filter button { flex: 0 0 auto; } /* Tombol filter tidak menyusut */

            /* Stats jadi 1 kolom di HP kecil, 2 di tablet */
            .stats-grid-premium { grid-template-columns: repeat(auto-fit, minmax(100%, 1fr)); gap: 15px; }
            
            /* Chart Container Tinggi Fix */
            .chart-container { min-height: 300px; padding: 15px; }
            .canvas-wrapper { height: 250px !important; }

            /* Transaksi List Mobile Optimization */
            .recent-transaction-list li { padding: 15px 0; }
            .trans-icon { width: 35px; height: 35px; font-size: 0.9rem; }
            .trans-info strong { font-size: 0.9rem; }
            .trans-amount { font-size: 0.9rem; }
        }
    </style>
@endsection

@section('content')
<div class="dashboard-container">
    
    {{-- HEADER SECTION --}}
    <div class="d-flex justify-content-between align-items-center mb-4 header-section">
        <div>
            <h1 class="dashboard-title">Overview Bisnis</h1>
            <p class="text-muted">Ringkasan performa Bakso Gala hari ini, {{ date('d M Y') }}</p>
        </div>
        
        <div class="date-filter">
            <button class="btn btn-sm btn-outline-secondary active">Hari Ini</button>
            <button class="btn btn-sm btn-outline-secondary">Minggu Ini</button>
            <button class="btn btn-sm btn-outline-secondary">Bulan Ini</button>
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="stats-grid-premium">
        {{-- Total Pendapatan --}}
        <div class="stat-card-premium primary-gradient">
            <div class="stat-icon"><i class="fas fa-wallet"></i></div>
            <div class="stat-details">
                <p class="stat-label">Total Pendapatan</p>
                {{-- Gunakan Null Coalescing (?? 0) agar tidak error jika data kosong --}}
                <h3 class="stat-value">Rp {{ number_format($totalSales ?? 0, 0, ',', '.') }}</h3>
                <span class="stat-trend positive"><i class="fas fa-chart-line"></i> Data Realtime</span>
            </div>
        </div>

        {{-- Pesanan Baru --}}
        <div class="stat-card-premium">
            <div class="stat-icon" style="background:#e3f2fd; color:#3498db;"><i class="fas fa-shopping-bag"></i></div>
            <div class="stat-details">
                <p class="stat-label">Pesanan Baru</p>
                <h3 class="stat-value">{{ $newOrders ?? 0 }}</h3>
                <span class="stat-trend text-muted">Perlu diproses</span>
            </div>
        </div>

        {{-- Menu Jawara --}}
        <div class="stat-card-premium">
            <div class="stat-icon" style="background:#fff8e1; color:#f1c40f;"><i class="fas fa-crown"></i></div>
            <div class="stat-details">
                <p class="stat-label">Menu Jawara</p>
                <h3 class="stat-value text-truncate" style="max-width: 150px;" title="{{ $bestSeller ?? '-' }}">{{ $bestSeller ?? '-' }}</h3>
                <span class="stat-trend positive">Favorit Pelanggan</span>
            </div>
        </div>

        {{-- Total User --}}
        <div class="stat-card-premium">
            <div class="stat-icon" style="background:#e8f5e9; color:#2ecc71;"><i class="fas fa-users"></i></div>
            <div class="stat-details">
                <p class="stat-label">Total Pelanggan</p>
                <h3 class="stat-value">{{ number_format($totalCustomers ?? 0) }}</h3>
                <span class="stat-trend positive">Terdaftar</span>
            </div>
        </div>
    </div>

    {{-- CHART & RECENT ORDERS --}}
    <div class="dashboard-split-grid">
        
        {{-- GRAFIK PENJUALAN --}}
        <div class="chart-container card-panel">
            <div class="panel-header">
                <h3>Grafik Penjualan (7 Hari Terakhir)</h3>
            </div>
            <div class="canvas-wrapper" style="position: relative; height: 300px; width: 100%;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        {{-- CONTROL PANEL & TRANSAKSI --}}
        <div class="quick-actions-container card-panel">
            <div class="panel-header">
                <h3>Control Panel</h3>
            </div>
            
            {{-- TOMBOL AKSES CEPAT --}}
            <div class="action-buttons-grid">
                <a href="{{ route('admin.menu.index') }}" class="action-btn">
                    <div class="icon-box"><i class="fas fa-utensils"></i></div>
                    <span>Menu</span>
                </a>
                <a href="{{ route('admin.orders.index') }}" class="action-btn">
                    <div class="icon-box"><i class="fas fa-receipt"></i></div>
                    <span>Pesanan</span>
                </a>
                <a href="{{ route('admin.promotions.index') }}" class="action-btn">
                    <div class="icon-box"><i class="fas fa-tags"></i></div>
                    <span>Promo</span>
                </a>
                <a href="{{ route('admin.reports.index') }}" class="action-btn">
                    <div class="icon-box"><i class="fas fa-chart-line"></i></div>
                    <span>Laporan</span>
                </a>
            </div>

            {{-- DAFTAR TRANSAKSI REAL --}}
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mini-title mb-0" style="font-size:1rem; font-weight:700; color:#555;">Transaksi Terakhir</h4>
                    <a href="{{ route('admin.orders.index') }}" style="font-size:0.8rem; text-decoration:none;">Lihat Semua</a>
                </div>
                
                <ul class="recent-transaction-list">
                    @forelse($recentOrders ?? [] as $order)
                        <li>
                            <div class="d-flex align-items-center w-100">
                                <div class="trans-icon"><i class="fas fa-arrow-down"></i></div>
                                <div class="trans-info flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>{{ $order->user_name ?? 'Pelanggan' }}</strong>
                                        <div class="trans-amount">+Rp {{ number_format($order->total_price, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <span class="badge-status {{ $order->status }}">{{ ucfirst($order->status) }}</span>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }}</small>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="text-muted text-center py-3">Belum ada transaksi hari ini.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('salesChart').getContext('2d');
        
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(177, 147, 91, 0.5)');
        gradient.addColorStop(1, 'rgba(177, 147, 91, 0)');

        // Data Aman (Gunakan Default jika variabel backend kosong)
        const labels = {!! json_encode($chartLabels ?? ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']) !!};
        const dataValues = {!! json_encode($chartValues ?? [0,0,0,0,0,0,0]) !!};

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Penjualan (Rp)',
                    data: dataValues,
                    backgroundColor: gradient,
                    borderColor: '#B1935B',
                    borderWidth: 2,
                    pointBackgroundColor: '#2F3D65',
                    pointRadius: 4,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // PENTING AGAR GRAFIK ELASTIS
                plugins: { 
                    legend: { display: false },
                    tooltip: { mode: 'index', intersect: false }
                },
                interaction: { mode: 'nearest', axis: 'x', intersect: false },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { borderDash: [5, 5], color: '#f0f0f0' },
                        ticks: {
                            font: { size: 10 }, // Font kecil di HP
                            callback: function(value) { return (value/1000) + 'k'; } // Singkat angka (10000 -> 10k)
                        }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    });
</script>
@endpush