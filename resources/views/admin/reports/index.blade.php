@extends('layouts.admin')

@section('title', 'Laporan Keuangan')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/admin_report.css') }}">
    {{-- Flatpickr untuk Date Picker Keren --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
<div class="container-fluid">

    {{-- 1. HEADER & FILTER --}}
    <div class="report-header">
        <div>
            <h1 class="page-title">Laporan Keuangan & Penjualan</h1>
            <p class="text-muted">Analisis performa bisnis Bakso Gala secara real-time.</p>
        </div>
        
        <form action="{{ route('admin.reports.index') }}" method="GET" class="date-filter-form">
            <div class="input-group">
                <span class="input-icon"><i class="far fa-calendar-alt"></i></span>
                <input type="text" name="start_date" class="datepicker" value="{{ $startDate }}" placeholder="Dari Tanggal">
            </div>
            <span class="separator">-</span>
            <div class="input-group">
                <span class="input-icon"><i class="far fa-calendar-alt"></i></span>
                <input type="text" name="end_date" class="datepicker" value="{{ $endDate }}" placeholder="Sampai Tanggal">
            </div>
            <button type="submit" class="btn-filter">Terapkan</button>
            <button type="button" class="btn-export" onclick="window.print()"><i class="fas fa-print"></i> PDF</button>
        </form>
    </div>

    {{-- 2. KPI CARDS (KEY PERFORMANCE INDICATORS) --}}
    <div class="kpi-grid">
        <div class="kpi-card revenue">
            <div class="kpi-icon"><i class="fas fa-coins"></i></div>
            <div class="kpi-info">
                <span class="kpi-label">Total Omset (Gross)</span>
                <h3 class="kpi-value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
            </div>
        </div>
        
        <div class="kpi-card profit">
            <div class="kpi-icon"><i class="fas fa-chart-line"></i></div>
            <div class="kpi-info">
                <span class="kpi-label">Estimasi Laba Bersih</span>
                <h3 class="kpi-value">Rp {{ number_format($netProfit, 0, ',', '.') }}</h3>
                <span class="kpi-sub">+40% Margin</span>
            </div>
        </div>

        <div class="kpi-card orders">
            <div class="kpi-icon"><i class="fas fa-shopping-bag"></i></div>
            <div class="kpi-info">
                <span class="kpi-label">Total Transaksi</span>
                <h3 class="kpi-value">{{ number_format($totalOrders) }}</h3>
                <span class="kpi-sub">Pesanan Selesai</span>
            </div>
        </div>

        <div class="kpi-card aov">
            <div class="kpi-icon"><i class="fas fa-receipt"></i></div>
            <div class="kpi-info">
                <span class="kpi-label">Rata-rata Transaksi</span>
                <h3 class="kpi-value">Rp {{ number_format($averageOrderValue, 0, ',', '.') }}</h3>
                <span class="kpi-sub">Per Pelanggan</span>
            </div>
        </div>
    </div>

    {{-- 3. MAIN CHART (SALES TREND) --}}
    <div class="chart-section">
        <div class="card-panel">
            <div class="card-header-clean">
                <h3>Tren Penjualan</h3>
                <div class="legend">
                    <span class="dot revenue"></span> Omset Harian
                </div>
            </div>
            <div class="chart-wrapper">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>

    {{-- 4. SPLIT SECTION (TOP PRODUCTS & PAYMENT METHOD) --}}
    <div class="split-grid">
        
        {{-- Top Selling Products --}}
        <div class="card-panel">
            <div class="card-header-clean">
                <h3>5 Menu Terlaris</h3>
            </div>
            <div class="top-products-list">
                @foreach($topProducts as $index => $product)
                <div class="product-item">
                    <div class="rank">#{{ $index + 1 }}</div>
                    <div class="prod-info">
                        <span class="prod-name">{{ $product->name }}</span>
                        <div class="progress-bar-bg">
                            {{-- Hitung persentase bar visual --}}
                            @php $percent = ($product->total_qty / ($topProducts->max('total_qty') ?? 1)) * 100; @endphp
                            <div class="progress-bar-fill" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                    <div class="prod-stats">
                        <span class="qty">{{ $product->total_qty }} Terjual</span>
                        <span class="income">Rp {{ number_format($product->total_income / 1000, 0) }}k</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Payment Method & Recent Log --}}
        <div class="card-panel">
            <div class="card-header-clean">
                <h3>Metode Pembayaran</h3>
            </div>
            <div class="chart-wrapper-small">
                <canvas id="paymentChart"></canvas>
            </div>
            <div class="payment-legend" id="paymentLegend">
                {{-- Legend akan diisi JS --}}
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    // 1. Inisialisasi Datepicker
    flatpickr(".datepicker", {
        dateFormat: "Y-m-d",
        allowInput: true
    });

    // 2. Grafik Tren Penjualan (Line Chart)
    const ctxSales = document.getElementById('salesChart').getContext('2d');
    const salesData = @json($salesData);
    
    // Siapkan data array
    const labels = salesData.map(item => item.date);
    const dataValues = salesData.map(item => item.total);

    // Gradient Effect
    let gradient = ctxSales.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(47, 61, 101, 0.2)'); // Navy pudar
    gradient.addColorStop(1, 'rgba(47, 61, 101, 0)');

    new Chart(ctxSales, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Omset',
                data: dataValues,
                borderColor: '#2F3D65',
                backgroundColor: gradient,
                borderWidth: 2,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#B1935B',
                pointRadius: 4,
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

    // 3. Grafik Metode Pembayaran (Doughnut)
    const ctxPay = document.getElementById('paymentChart').getContext('2d');
    const paymentStats = @json($paymentStats);
    
    const payLabels = paymentStats.map(item => item.payment_method.toUpperCase());
    const payValues = paymentStats.map(item => item.total);

    new Chart(ctxPay, {
        type: 'doughnut',
        data: {
            labels: payLabels,
            datasets: [{
                data: payValues,
                backgroundColor: ['#2F3D65', '#B1935B', '#95a5a6', '#e74c3c'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
            }
        }
    });
</script>
@endpush