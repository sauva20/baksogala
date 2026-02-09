@extends('layouts.app')

@section('title', 'Detail Pembayaran')

@section('styles')
<link rel="icon" href="{{ asset('assets/images/gala.png') }}" type="image/png">
    <style>
        /* FIX: Sembunyikan Footer di Halaman Ini */
        footer { display: none !important; }
        
        body { background-color: #F4F6F9; padding-bottom: 50px; }
    </style>
@endsection

@section('content')
<div class="container" style="max-width: 600px; padding: 40px 20px;">
    
    {{-- LOGIKA TAMPILAN BERDASARKAN STATUS --}}
    @if($order->status == 'cancelled')
        <div style="text-align: center; margin-bottom: 30px;">
            <i class="fas fa-times-circle" style="font-size: 4rem; color: #e74c3c;"></i>
            <h2 style="margin-top: 15px; color: #333;">Pesanan Dibatalkan</h2>
            <p style="color: #777;">Waktu pembayaran telah habis atau dibatalkan admin.</p>
            <a href="{{ route('menu.index') }}" class="btn btn-secondary">Pesan Lagi</a>
        </div>

    @elseif($order->payment_status == 'paid' || in_array($order->status, ['process', 'preparing', 'ready', 'completed']))
        <div style="text-align: center; margin-bottom: 30px;">
            <i class="fas fa-check-circle" style="font-size: 4rem; color: #4caf50;"></i>
            <h2 style="margin-top: 15px; color: #333;">Pesanan Diterima!</h2>
            
            {{-- Pesan Dinamis Berdasarkan Status --}}
            @if($order->status == 'preparing')
                <p class="badge bg-warning text-dark" style="font-size: 1rem;">Sedang Dimasak <i class="fas fa-fire"></i></p>
            @elseif($order->status == 'ready')
                <p class="badge bg-success" style="font-size: 1rem;">Siap Disajikan <i class="fas fa-bell"></i></p>
            @elseif($order->status == 'completed')
                <p class="badge bg-primary" style="font-size: 1rem;">Selesai <i class="fas fa-check"></i></p>
            @else
                <p>Terima kasih telah memesan.</p>
            @endif
        </div>

    @else
        {{-- TAMPILAN MENUNGGU PEMBAYARAN --}}
        <div style="text-align: center; margin-bottom: 30px;">
            <i class="fas fa-clock" style="font-size: 4rem; color: #f39c12;"></i>
            <h2 style="margin-top: 15px; color: #333;">Selesaikan Pembayaran</h2>
            
            {{-- COUNTDOWN TIMER UI --}}
            <div id="timer-box" style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 8px; display: inline-block; margin-top: 10px; font-weight: bold; border: 1px solid #ffcdd2;">
                Sisa Waktu: <span id="countdown">Memuat...</span>
            </div>
        </div>
    @endif

    <div class="card" style="border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-radius: 12px; overflow: hidden;">
        <div class="card-header" style="background: #fff; border-bottom: 1px dashed #eee; padding: 20px;">
            <h5 style="margin: 0; font-weight: bold; color: #2F3D65;">Order #{{ $order->id }}</h5>
            <small style="color: #777;">Dibuat: {{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</small>
        </div>
        <div class="card-body" style="padding: 20px;">
            
            {{-- List Item --}}
            <div style="margin-bottom: 20px;">
                @foreach($order->orderDetails as $detail)
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.95rem;">
                    <div>
                        <strong>{{ $detail->quantity }}x</strong> {{ $detail->menuItem->name ?? 'Menu' }}
                        @if($detail->item_notes)
                            <div style="font-size: 0.8rem; color: #888;">{{ $detail->item_notes }}</div>
                        @endif
                    </div>
                    <div>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</div>
                </div>
                @endforeach
            </div>

            <div style="border-top: 2px dashed #eee; padding-top: 15px; margin-top: 15px;">
                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.1rem; color: #B1935B;">
                    <span>Total Bayar</span>
                    <span>Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- LOGIKA TOMBOL --}}
            {{-- Jika Status Masih New/Pending DAN Belum Lunas -> Muncul Tombol Bayar --}}
            @if(in_array($order->status, ['new', 'pending']) && $order->payment_status != 'paid')
                <button id="pay-button" class="btn btn-primary" style="width: 100%; margin-top: 25px; padding: 12px; font-weight: bold; background: #B1935B; border: none; border-radius: 8px;">
                    <i class="fas fa-wallet"></i> Buka Pembayaran
                </button>
            @endif

            {{-- Jika Sudah Lunas ATAU Status diproses Admin -> Tombol Detail --}}
            @if($order->payment_status == 'paid' || in_array($order->status, ['process', 'preparing', 'ready', 'completed']))
                <div style="margin-top: 25px;">
                    <a href="{{ route('orders.detail', $order->id) }}" class="btn btn-outline-secondary" style="width: 100%; padding: 12px; font-weight: bold; border-radius: 8px; border: 1px solid #ccc; color: #555;">
                        <i class="fas fa-receipt"></i> Lihat Detail Pesanan & Review
                    </a>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

<script type="text/javascript">
    // --- LOGIKA COUNTDOWN TIMER ---
    // Hanya jalankan timer jika status masih pending/new
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
                timerElement.innerHTML = minutes + "m " + seconds + "s ";
            }

            if (distance < 0) {
                clearInterval(x);
                if(timerElement) timerElement.innerHTML = "WAKTU HABIS";
                var btn = document.getElementById('pay-button');
                if(btn) btn.style.display = 'none';
                
                // Reload halaman untuk cek status terbaru
                window.location.reload();
            }
        }, 1000);
    @endif

    // --- LOGIKA AUTO POPUP MIDTRANS ---
    function triggerSnap() {
        window.snap.pay('{{ $order->snap_token }}', {
            onSuccess: function(result){ alert("Pembayaran Berhasil!"); window.location.reload(); },
            onPending: function(result){ alert("Menunggu Pembayaran!"); window.location.reload(); },
            onError: function(result){ alert("Pembayaran Gagal!"); window.location.reload(); },
            onClose: function(){ 
                // Tidak melakukan apa-apa agar user bisa klik tombol bayar lagi
            }
        });
    }

    // 1. Event Listener Tombol (Manual)
    var payButton = document.getElementById('pay-button');
    if (payButton) {
        payButton.addEventListener('click', function () {
            triggerSnap();
        });
    }
</script>
@endpush