@extends('layouts.admin')

@section('title', 'Executive Dashboard')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/admin_dashboard.css') }}">
@endsection

@section('content')
<div class="dashboard-container">
    
    {{-- HEADER SECTION --}}
    <div class="d-flex justify-content-between align-items-center mb-4 header-section">
        <div>
            <h1 class="dashboard-title">Overview Bisnis</h1>
            <p class="text-muted">Ringkasan performa Bakso Gala hari ini, {{ date('d M Y') }}</p>
        </div>
        {{-- Filter tanggal bisa dibuat fungsi JS nanti --}}
        <div class="date-filter">
            <button class="btn-filter active">Hari Ini</button>
            <button class="btn-filter">Minggu Ini</button>
            <button class="btn-filter">Bulan Ini</button>
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="stats-grid-premium">
        {{-- Total Pendapatan --}}
        <div class="stat-card-premium primary-gradient">
            <div class="stat-icon"><i class="fas fa-wallet"></i></div>
            <div class="stat-details">
                <p class="stat-label">Total Pendapatan</p>
                <h3 class="stat-value">Rp {{ number_format($totalSales, 0, ',', '.') }}</h3>
                <span class="stat-trend positive"><i class="fas fa-chart-line"></i> Data Realtime</span>
            </div>
        </div>

        {{-- Pesanan Baru --}}
        <div class="stat-card-premium">
            <div class="stat-icon bg-blue"><i class="fas fa-shopping-bag"></i></div>
            <div class="stat-details">
                <p class="stat-label">Pesanan Baru</p>
                <h3 class="stat-value">{{ $newOrders }}</h3>
                <span class="stat-trend text-muted">Perlu diproses</span>
            </div>
        </div>

        {{-- Menu Jawara --}}
        <div class="stat-card-premium">
            <div class="stat-icon bg-gold"><i class="fas fa-crown"></i></div>
            <div class="stat-details">
                <p class="stat-label">Menu Jawara</p>
                <h3 class="stat-value text-truncate" style="max-width: 150px;" title="{{ $bestSeller }}">{{ $bestSeller }}</h3>
                <span class="stat-trend positive">Favorit Pelanggan</span>
            </div>
        </div>

        {{-- Total User (Data Dinamis) --}}
        <div class="stat-card-premium">
            <div class="stat-icon bg-green"><i class="fas fa-users"></i></div>
            <div class="stat-details">
                <p class="stat-label">Total Pelanggan</p>
                <h3 class="stat-value">{{ number_format($totalCustomers) }}</h3>
                <span class="stat-trend positive">Terdaftar</span>
            </div>
        </div>
    </div>

    {{-- CHART & RECENT ORDERS --}}
    <div class="dashboard-split-grid">
        
        <div class="chart-container card-panel">
            <div class="panel-header">
                <h3>Grafik Penjualan (7 Hari Terakhir)</h3>
            </div>
            <div class="canvas-wrapper">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <div class="quick-actions-container card-panel">
            <div class="panel-header">
                <h3>Control Panel</h3>
            </div>
            
            {{-- TOMBOL AKSES CEPAT (LINK SUDAH DIPERBAIKI) --}}
            {{-- Pastikan route ini sudah dibuat di web.php, jika belum buat dummy route dulu --}}
            <div class="action-buttons-grid">
                <a href="{{ route('admin.menu.index') }}" class="action-btn">
                    <div class="icon-box"><i class="fas fa-utensils"></i></div>
                    <span>Menu</span>
                </a>
                <a href="#" class="action-btn">
                    <div class="icon-box"><i class="fas fa-receipt"></i></div>
                    <span>Pesanan</span>
                </a>
                <a href="#" class="action-btn">
                    <div class="icon-box"><i class="fas fa-tags"></i></div>
                    <span>Promo</span>
                </a>
                <a href="#" class="action-btn">
                    <div class="icon-box"><i class="fas fa-chart-line"></i></div>
                    <span>Laporan</span>
                </a>
            </div>

            {{-- DAFTAR TRANSAKSI REAL --}}
            <div class="mt-4">
                <h4 class="mini-title">Transaksi Terakhir</h4>
                <ul class="recent-transaction-list">
                    @forelse($recentOrders as $order)
                        <li>
                            <div class="trans-icon incoming"><i class="fas fa-arrow-down"></i></div>
                            <div class="trans-info">
                                <strong>{{ $order->user_name }}</strong>
                                {{-- Menampilkan status sebagai subteks --}}
                                <span class="badge-status {{ $order->status }}">{{ ucfirst($order->status) }}</span>
                            </div>
                            <div class="trans-amount positive">+Rp {{ number_format($order->total_price, 0, ',', '.') }}</div>
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
    const ctx = document.getElementById('salesChart').getContext('2d');
    let gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(177, 147, 91, 0.5)');
    gradient.addColorStop(1, 'rgba(177, 147, 91, 0)');

    // DATA DARI CONTROLLER (Blade to JS)
    const labels = {!! json_encode($chartLabels) !!};
    const dataValues = {!! json_encode($chartValues) !!};

    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels.length > 0 ? labels : ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'], // Fallback jika kosong
            datasets: [{
                label: 'Penjualan (Rp)',
                data: dataValues.length > 0 ? dataValues : [0,0,0,0,0,0,0], // Fallback
                backgroundColor: gradient,
                borderColor: '#B1935B',
                borderWidth: 2,
                pointBackgroundColor: '#2F3D65',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endpush