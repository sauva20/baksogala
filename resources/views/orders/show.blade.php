@extends('layouts.app')

@section('title', 'Detail Pesanan')

@section('styles')
    <link rel="icon" href="{{ asset('assets/images/GALA.png') }}" type="image/png">
    <style>
        /* --- GLOBAL SETUP --- */
        :root {
            --primary: #B1935B;
            --secondary: #2F3D65;
            --bg: #F4F6F9;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1f2937;
            --muted: #6b7280;
        }

        /* Hapus Footer */
        footer { display: none !important; }
        
        body { 
            background-color: var(--bg); 
            padding-bottom: 80px; /* Ruang untuk tombol bawah */
            font-family: 'Bolton Sans', sans-serif;
        }

        .container-center {
            max-width: 500px; /* Lebar ideal untuk tampilan mobile/struk */
            margin: 0 auto;
            padding: 30px 20px;
        }

        /* --- STATUS HEADER --- */
        .status-header {
            text-align: center;
            margin-bottom: 30px;
            animation: fadeIn 0.5s ease-in-out;
        }
        .status-icon-wrapper {
            width: 80px; height: 80px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 15px;
            font-size: 2.5rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
        .status-title { font-weight: 800; color: var(--secondary); font-size: 1.5rem; margin-bottom: 5px; }
        .status-desc { color: var(--muted); font-size: 0.95rem; }

        /* Varian Warna Status */
        .st-pending .status-icon-wrapper { background: #fffbeb; color: var(--warning); }
        .st-success .status-icon-wrapper { background: #d1fae5; color: var(--success); }
        .st-cancel .status-icon-wrapper { background: #fee2e2; color: var(--danger); }

        /* --- COUNTDOWN --- */
        .countdown-box {
            background: #FEF2F2; border: 1px solid #xFCA5A5; color: var(--danger);
            padding: 10px 20px; border-radius: 50px; font-weight: 700;
            display: inline-flex; align-items: center; gap: 8px; margin-top: 15px;
            font-size: 0.9rem;
        }

        /* --- ORDER CARD (RECEIPT STYLE) --- */
        .order-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.04);
            overflow: hidden;
            position: relative;
            border: 1px solid #f3f4f6;
        }
        
        /* Hiasan Bergerigi di atas (Optional, estetik struk) */
        .order-card::before {
            content: "";
            position: absolute; top: 0; left: 0; right: 0; height: 5px;
            background: radial-gradient(circle, transparent 0.25em, white 0.25em);
            background-size: 0.5em 0.5em;
            background-position: 0 -0.25em;
        }

        .card-header-custom {
            padding: 25px 25px 15px;
            border-bottom: 2px dashed #eee;
            display: flex; justify-content: space-between; align-items: center;
        }
        .order-id { font-weight: 800; font-size: 1.1rem; color: var(--secondary); }
        .order-date { font-size: 0.75rem; color: var(--muted); text-align: right; }

        .item-list { padding: 20px 25px; }
        
        .item-row {
            display: flex; justify-content: space-between;
            margin-bottom: 15px; align-items: flex-start;
        }
        .item-qty { 
            background: #eff6ff; color: var(--secondary); 
            font-weight: 700; font-size: 0.8rem;
            width: 24px; height: 24px; border-radius: 6px; 
            display: flex; align-items: center; justify-content: center;
            margin-right: 12px; flex-shrink: 0;
        }
        .item-info { flex-grow: 1; padding-right: 10px; }
        .item-name { font-weight: 700; color: var(--dark); font-size: 0.95rem; line-height: 1.3; }
        .item-notes { 
            font-size: 0.8rem; color: var(--muted); margin-top: 4px; 
            background: #f9fafb; padding: 4px 8px; border-radius: 6px; display: inline-block;
        }
        .item-price { font-weight: 700; color: var(--dark); font-size: 0.95rem; white-space: nowrap; }

        /* --- TOTAL SECTION --- */
        .total-section {
            background: #fff8f1; /* Light Orange/Gold tint */
            padding: 20px 25px;
            border-top: 2px dashed #eee;
        }
        .total-row {
            display: flex; justify-content: space-between; align-items: center;
        }
        .total-label { font-size: 1rem; color: var(--secondary); font-weight: 600; }
        .total-value { font-size: 1.3rem; color: var(--primary); font-weight: 800; }

        /* --- ACTION BUTTONS --- */
        .btn-action {
            display: block; width: 100%;
            padding: 16px; border-radius: 12px;
            font-weight: 700; font-size: 1rem;
            text-align: center; border: none; cursor: pointer;
            transition: transform 0.2s; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-decoration: none;
        }
        .btn-action:active { transform: scale(0.98); }
        
        .btn-pay { background: var(--primary); color: white; }
        .btn-pay:hover { background: #9a7d46; }
        
        .btn-track { background: white; border: 2px solid #e5e7eb; color: var(--secondary); }
        .btn-track:hover { border-color: var(--secondary); }

        .btn-reorder { background: var(--secondary); color: white; }

        /* Animation */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
@endsection

@section('content')
<div class="container-center">
    
    {{-- === 1. STATUS SECTION === --}}
    @if($order->status == 'cancelled')
        {{-- CASE: BATAL --}}
        <div class="status-header st-cancel">
            <div class="status-icon-wrapper"><i class="fas fa-times"></i></div>
            <h2 class="status-title">Pesanan Dibatalkan</h2>
            <p class="status-desc">Waktu pembayaran habis atau dibatalkan.</p>
            <div style="margin-top: 20px;">
                <a href="{{ route('menu.index') }}" class="btn-action btn-reorder">Pesan Lagi</a>
            </div>
        </div>

    @elseif($order->payment_status == 'paid' || in_array($order->status, ['process', 'preparing', 'ready', 'completed']))
        {{-- CASE: SUKSES / DIPROSES --}}
        <div class="status-header st-success">
            <div class="status-icon-wrapper"><i class="fas fa-check"></i></div>
            <h2 class="status-title">Pesanan Diterima!</h2>
            
            @if($order->status == 'preparing')
                <div class="badge bg-warning text-dark mt-2 px-3 py-2 rounded-pill">Sedang Dimasak <i class="fas fa-fire"></i></div>
            @elseif($order->status == 'ready')
                <div class="badge bg-success mt-2 px-3 py-2 rounded-pill">Siap Disajikan <i class="fas fa-bell"></i></div>
            @elseif($order->status == 'completed')
                <div class="badge bg-primary mt-2 px-3 py-2 rounded-pill">Selesai <i class="fas fa-check"></i></div>
            @else
                <p class="status-desc">Terima kasih, mohon ditunggu ya.</p>
            @endif
        </div>

    @else
        {{-- CASE: MENUNGGU PEMBAYARAN --}}
        <div class="status-header st-pending">
            <div class="status-icon-wrapper"><i class="fas fa-wallet"></i></div>
            <h2 class="status-title">Menunggu Pembayaran</h2>
            <p class="status-desc">Selesaikan pembayaran sebelum waktu habis.</p>
            
            <div id="timer-box" class="countdown-box">
                <i class="fas fa-stopwatch"></i> <span id="countdown">Memuat...</span>
            </div>
        </div>
    @endif

    {{-- === 2. ORDER DETAILS CARD (STRUK) === --}}
    <div class="order-card">
        {{-- Header Struk --}}
        <div class="card-header-custom">
            <div>
                <div class="order-id">Order #{{ $order->id }}</div>
                <div style="font-size: 0.8rem; color: var(--primary);">{{ $order->payment_method == 'midtrans' ? 'QRIS / E-Wallet' : 'Tunai' }}</div>
            </div>
            <div class="order-date">
                <div>{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y') }}</div>
                <div>{{ $order->created_at->timezone('Asia/Jakarta')->format('H:i') }} WIB</div>
            </div>
        </div>

        {{-- List Menu --}}
        <div class="item-list">
            @foreach($order->orderDetails as $detail)
            <div class="item-row">
                <div style="display: flex; width: 100%;">
                    <div class="item-qty">{{ $detail->quantity }}x</div>
                    <div class="item-info">
                        <div class="item-name">{{ $detail->menuItem->name ?? 'Menu' }}</div>
                        
                        {{-- Tampilkan Notes/Topping dengan rapi --}}
                        @if($detail->item_notes)
                            <div class="item-notes">{{ $detail->item_notes }}</div>
                        @endif
                    </div>
                </div>
                <div class="item-price">
                    Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                </div>
            </div>
            @endforeach
        </div>

        {{-- Total Section --}}
        <div class="total-section">
            <div class="total-row">
                <span class="total-label">Total Bayar</span>
                <span class="total-value">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- === 3. ACTION BUTTONS === --}}
    
    {{-- Tombol Bayar (Muncul jika belum lunas) --}}
    @if(in_array($order->status, ['new', 'pending']) && $order->payment_status != 'paid')
        <div style="margin-top: 25px;">
            <button id="pay-button" class="btn-action btn-pay">
                Bayar Sekarang <i class="fas fa-chevron-right ml-2"></i>
            </button>
        </div>
    @endif

    {{-- Tombol Detail / Refresh (Muncul jika sudah lunas) --}}
    @if($order->payment_status == 'paid' || in_array($order->status, ['process', 'preparing', 'ready', 'completed']))
        <div style="margin-top: 25px;">
            <a href="{{ route('orders.detail', $order->id) }}" class="btn-action btn-track">
                <i class="fas fa-sync-alt"></i> Refresh Status
            </a>
        </div>
    @endif

</div>
@endsection

@push('scripts')
{{-- Cek apakah mode Production atau Sandbox --}}
<script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('midtrans.client_key') }}"></script>

<script type="text/javascript">
    // --- LOGIKA TIMER ---
    @if(in_array($order->status, ['new', 'pending']) && $order->payment_status != 'paid')
        var createdTime = new Date("{{ $order->created_at->toIso8601String() }}").getTime();
        var deadline = createdTime + (10 * 60 * 1000); // 10 menit

        var x = setInterval(function() {
            var now = new Date().getTime();
            var distance = deadline - now;
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            var timerElement = document.getElementById("countdown");
            if(timerElement) {
                timerElement.innerHTML = minutes + "m " + seconds + "s";
            }

            if (distance < 0) {
                clearInterval(x);
                if(timerElement) timerElement.innerHTML = "Expired";
                var btn = document.getElementById('pay-button');
                if(btn) {
                    btn.style.background = '#ccc';
                    btn.innerText = 'Waktu Habis';
                    btn.disabled = true;
                }
                window.location.reload();
            }
        }, 1000);
    @endif

    // --- LOGIKA MIDTRANS ---
    function triggerSnap() {
        window.snap.pay('{{ $order->snap_token }}', {
            onSuccess: function(result){ 
                Swal.fire('Berhasil!', 'Pembayaran diterima.', 'success').then(() => window.location.reload());
            },
            onPending: function(result){ 
                Swal.fire('Menunggu', 'Silakan selesaikan pembayaran.', 'info').then(() => window.location.reload());
            },
            onError: function(result){ 
                Swal.fire('Gagal', 'Pembayaran gagal.', 'error').then(() => window.location.reload());
            },
            onClose: function(){ 
                // User tutup popup tanpa bayar
            }
        });
    }

    var payButton = document.getElementById('pay-button');
    if (payButton) {
        payButton.addEventListener('click', function () {
            triggerSnap();
        });
    }
</script>
{{-- Tambahkan SweetAlert agar alert lebih cantik --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush