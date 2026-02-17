@extends('layouts.app')

@section('title', 'Detail Pesanan #' . $order->id)

@section('styles')
<link rel="icon" href="{{ asset('assets/images/GALA.png') }}" type="image/png">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    :root { --primary: #B1935B; --navy: #2F3D65; --bg: #F4F6F9; --text: #333; }
    body { background-color: var(--bg); color: var(--text); padding-bottom: 50px; }
    footer { display: none !important; }
    
    .detail-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn-back { text-decoration: none; color: #666; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: 0.3s; }
    
    /* --- STATUS TRACKER (STEPPER) --- */
    .status-tracker {
        background: white; padding: 20px; border-radius: 15px; margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: center; position: relative;
    }
    .status-tracker::before {
        content: ''; position: absolute; top: 35%; left: 40px; right: 40px; height: 3px; background: #eee; z-index: 0;
    }
    .step { position: relative; z-index: 1; text-align: center; width: 25%; }
    .step-icon {
        width: 35px; height: 35px; background: #eee; border-radius: 50%; margin: 0 auto 8px;
        display: flex; align-items: center; justify-content: center; color: #999; font-size: 0.9rem;
        transition: 0.3s; border: 3px solid white;
    }
    .step-label { font-size: 0.75rem; color: #999; font-weight: 600; }
    
    /* Active Step */
    .step.active .step-icon { background: var(--primary); color: white; box-shadow: 0 0 0 3px #fff8e1; }
    .step.active .step-label { color: var(--primary); }
    /* Completed Step */
    .step.completed .step-icon { background: var(--navy); color: white; }
    .step.completed .step-label { color: var(--navy); }

    /* Receipt Styles */
    .receipt-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #eee; }
    .receipt-header { background: var(--navy); color: white; padding: 20px; text-align: center; }
    .receipt-title { margin: 0; font-size: 1.1rem; font-weight: 700; }
    
    .item-row { display: flex; justify-content: space-between; padding: 12px 20px; border-bottom: 1px dashed #eee; font-size: 0.9rem; }
    .item-name { font-weight: 600; color: #444; display:block;}
    .item-notes { font-size: 0.75rem; color: #888; font-style: italic; }
    
    .total-section { padding: 20px; background: #fcfcfc; }
    .total-row { display: flex; justify-content: space-between; font-weight: 700; font-size: 1.1rem; color: var(--navy); }

    /* Review Styles */
    .review-card { background: white; border-radius: 15px; padding: 25px; margin-top: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); border: 1px solid #eee; text-align: center; }
    .rate { display: inline-block; height: 46px; padding: 0 10px; }
    .rate:not(:checked) > input { position:absolute; top:-9999px; }
    .rate:not(:checked) > label { float:right; width:1em; overflow:hidden; white-space:nowrap; cursor:pointer; font-size:35px; color:#ddd; }
    .rate:not(:checked) > label:before { content: '★ '; }
    .rate > input:checked ~ label { color: #ffc700; }
    .rate:not(:checked) > label:hover, .rate:not(:checked) > label:hover ~ label { color: #deb217; }
    .rate > input:checked + label:hover, .rate > input:checked + label:hover ~ label, .rate > input:checked ~ label:hover, .rate > input:checked ~ label:hover ~ label, .rate > label:hover ~ input:checked ~ label { color: #c59b08; }

    /* AI Button Styles */
    .ai-wrapper { position: relative; margin-bottom: 15px; }
    .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; margin-bottom: 5px;}
    
    .btn-ai-polish { 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
        color: white; border: none; padding: 6px 12px; border-radius: 20px; 
        font-size: 0.75rem; font-weight: 600; cursor: pointer; 
        display: flex; align-items: center; gap: 5px; 
        position: absolute; bottom: 15px; right: 10px; z-index: 10; 
        box-shadow: 0 4px 10px rgba(118, 75, 162, 0.3); transition: transform 0.2s; 
    }
    .btn-ai-polish:hover { transform: scale(1.05); }
    .ai-loading { display: none; margin-left: 5px; }
    
    .btn-submit-review { width: 100%; background: var(--primary); color: white; border: none; padding: 12px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.3s; margin-top: 10px;}
    
    /* Waiting Message */
    .waiting-message { text-align: center; padding: 30px; background: white; border-radius: 15px; margin-top: 20px; border: 1px solid #eee; }
    .waiting-icon { font-size: 3rem; color: #ccc; margin-bottom: 15px; animation: pulse 2s infinite; }
    
    @keyframes pulse { 0% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.1); opacity: 0.7; } 100% { transform: scale(1); opacity: 1; } }
</style>
@endsection

@section('content')
<div class="container" style="max-width: 500px; padding: 30px 20px;">
    
    <div class="detail-nav">
        <a href="{{ route('menu.index') }}" class="btn-back"><i class="fas fa-arrow-left"></i> Menu Utama</a>
        <span style="font-size: 0.8rem; color: #888;">Order #{{ $order->id }}</span>
    </div>

    {{-- 1. STATUS TRACKER (STEPPER) --}}
    @php
        $s = $order->status;
        $step1 = ($s == 'new' || $s == 'preparing' || $s == 'ready' || $s == 'completed') ? 'completed' : 'active';
        $step2 = ($s == 'preparing' || $s == 'ready' || $s == 'completed') ? 'completed' : ($s == 'process' ? 'active' : '');
        $step3 = ($s == 'ready' || $s == 'completed') ? 'completed' : ($s == 'preparing' ? 'active' : '');
        $step4 = ($s == 'completed') ? 'completed' : ($s == 'ready' ? 'active' : '');
    @endphp

    <div class="status-tracker">
        <div class="step {{ $step1 }}">
            <div class="step-icon"><i class="fas fa-wallet"></i></div>
            <div class="step-label">Diterima</div>
        </div>
        <div class="step {{ $step2 }}">
            <div class="step-icon"><i class="fas fa-fire"></i></div>
            <div class="step-label">Dimasak</div>
        </div>
        <div class="step {{ $step3 }}">
            <div class="step-icon"><i class="fas fa-bell"></i></div>
            <div class="step-label">Siap</div>
        </div>
        <div class="step {{ $step4 }}">
            <div class="step-icon"><i class="fas fa-check"></i></div>
            <div class="step-label">Selesai</div>
        </div>
    </div>

    {{-- 2. RINCIAN PESANAN --}}
    <div class="receipt-card">
        <div class="receipt-header">
            <h1 class="receipt-title">Rincian Pesanan</h1>
            <div style="font-size: 0.8rem; opacity: 0.8;">{{ $order->order_type }} - {{ Str::after($order->shipping_address, '-') }}</div>
        </div>
        
        @foreach($order->orderDetails as $detail)
            <div class="item-row">
                <div style="flex: 1;">
                    <span style="font-weight: bold; color: var(--primary); margin-right: 5px;">{{ $detail->quantity }}x</span>
                    <span class="item-name">{{ $detail->menuItem->name ?? 'Item Dihapus' }}</span>
                    @if($detail->item_notes)
                        <div class="item-notes">"{{ $detail->item_notes }}"</div>
                    @endif
                </div>
                <div style="font-weight: 600;">{{ number_format($detail->subtotal, 0, ',', '.') }}</div>
            </div>
        @endforeach

        <div class="total-section">
            <div class="total-row">
                <span>TOTAL</span>
                <span>Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
            </div>
            <div style="text-align: center; margin-top: 10px; font-size: 0.8rem;">
                Status Bayar: 
                <span style="color: {{ $order->payment_status == 'paid' ? 'green' : 'red' }}; font-weight: bold;">
                    {{ strtoupper($order->payment_status) }}
                </span>
            </div>
        </div>
    </div>

    {{-- 3. LOGIC REVIEW & WAITING MESSAGE --}}
    @if($order->status == 'completed')
        
        {{-- SUDAH SELESAI -> BOLEH REVIEW --}}
        <div class="review-card">
            @if($order->review)
                {{-- SUDAH PERNAH REVIEW --}}
                <h4 style="color: var(--navy); margin-bottom: 10px;">Terima Kasih!</h4>
                <div style="color: #ffc700; font-size: 1.5rem; margin-bottom: 10px;">
                    @for($i=0; $i<$order->review->rating; $i++) ★ @endfor
                </div>
                <p style="font-style: italic; color: #666;">"{{ $order->review->comment }}"</p>
                
                {{-- PERBAIKAN: MENAMPILKAN GAMBAR (MENCEGAH PATH GANDA) --}}
                @if($order->review->photo)
                    <div style="margin-top: 15px;">
                        {{-- KUNCI PERBAIKAN: Gunakan asset() langsung ke variabel, jangan tambah 'uploads/' lagi --}}
                        <img src="{{ asset($order->review->photo) }}" 
                             alt="Review Photo" 
                             style="max-width: 100%; height: auto; max-height: 200px; border-radius: 10px; border: 1px solid #eee; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    </div>
                @endif

                <div style="margin-top: 10px; color: green; font-size: 0.8rem;"><i class="fas fa-check-circle"></i> Ulasan terkirim</div>
            @else
                {{-- FORM REVIEW --}}
                <h4 style="color: var(--navy);">Beri Ulasan Yuk!</h4>
                <p style="font-size: 0.85rem; color: #666; margin-bottom: 20px;">Gimana rasa makanannya? Bantu kami jadi lebih baik.</p>
                
                <form action="{{ route('orders.review.store', $order->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if($errors->any())
                        <div style="background: #ffebee; color: red; padding: 10px; font-size: 0.8rem; margin-bottom: 10px; border-radius: 5px;">{{ $errors->first() }}</div>
                    @endif

                    <div style="margin-bottom: 10px;">
                        <div class="rate">
                            <input type="radio" id="star5" name="rating" value="5" /><label for="star5">5</label>
                            <input type="radio" id="star4" name="rating" value="4" /><label for="star4">4</label>
                            <input type="radio" id="star3" name="rating" value="3" /><label for="star3">3</label>
                            <input type="radio" id="star2" name="rating" value="2" /><label for="star2">2</label>
                            <input type="radio" id="star1" name="rating" value="1" /><label for="star1">1</label>
                        </div>
                    </div>

                    {{-- FITUR AI PERCANTIK KATA DI SINI --}}
                    <div class="ai-wrapper">
                        <textarea id="reviewComment" name="comment" class="form-control" rows="4" placeholder="Tulis ulasan kasar, nanti AI yang perbaiki... (Cth: 'Enak banget baksonya')" required></textarea>
                        
                        <button type="button" class="btn-ai-polish" onclick="polishWithAI()">
                            <i class="fas fa-magic"></i> 
                            <span>Perindah Kata</span>
                            <i class="fas fa-spinner fa-spin ai-loading"></i>
                        </button>
                    </div>
                    
                    <div style="text-align: left; margin-bottom: 10px;">
                        <label style="font-size: 0.8rem; font-weight: bold;">Foto (Opsional)</label>
                        <input type="file" name="photo" class="form-control">
                    </div>

                    <button type="submit" class="btn-submit-review">Kirim Ulasan</button>
                </form>
            @endif
        </div>

    @else
        
        {{-- BELUM SELESAI -> PESAN TUNGGU --}}
        <div class="waiting-message">
            <div class="waiting-icon"><i class="fas fa-hourglass-half"></i></div>
            <h3 style="margin: 0 0 10px 0; color: var(--navy);">Mohon Ditunggu...</h3>
            <p style="color: #666; font-size: 0.9rem; line-height: 1.5;">
                Pesananmu sedang kami siapkan.<br>
                Halaman ini akan <strong>refresh otomatis</strong> saat status berubah.<br>
                Kamu bisa memberi ulasan setelah pesanan selesai.
            </p>
        </div>

    @endif

</div>

{{-- INPUT HIDDEN UNTUK NYIMPAN STATUS TERAKHIR (BUAT JS) --}}
<input type="hidden" id="currentStatus" value="{{ $order->status }}">

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // --- 1. POPUP SWAL STATUS ---
    @if(session('success')) Swal.fire('Berhasil', "{{ session('success') }}", 'success'); @endif
    @if(session('error')) Swal.fire('Gagal', "{{ session('error') }}", 'error'); @endif

    // --- 2. SCRIPT AUTO RELOAD (POLLING) ---
    const statusSekarang = document.getElementById('currentStatus').value;
    
    if (statusSekarang !== 'completed' && statusSekarang !== 'cancelled') {
        console.log("Monitoring status pesanan...");
        setInterval(() => {
            fetch("{{ route('orders.status', $order->id) }}")
                .then(response => response.json())
                .then(data => {
                    if (data.status !== statusSekarang) {
                        console.log("Status berubah! Reloading...");
                        location.reload(); 
                    }
                })
                .catch(err => console.error("Gagal cek status:", err));
        }, 5000); 
    }

    // --- 3. LOGIKA AI PERCANTIK KATA ---
    function polishWithAI() {
        const commentBox = document.getElementById('reviewComment');
        const text = commentBox.value.trim();
        const btn = document.querySelector('.btn-ai-polish');
        const loadingIcon = document.querySelector('.ai-loading');
        const btnText = btn.querySelector('span');

        if (text.length < 5) {
            Swal.fire('Ups!', 'Tulis ulasan kasar dulu minimal 5 huruf, nanti AI yang perbaiki.', 'warning');
            return;
        }

        btn.disabled = true;
        btnText.innerText = "Sedang berpikir...";
        loadingIcon.style.display = "inline-block";

        fetch('{{ route("review.polish") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ text: text })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                commentBox.value = data.text;
                commentBox.style.borderColor = "#667eea";
                setTimeout(() => commentBox.style.borderColor = "#ddd", 1000);
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Terjadi kesalahan koneksi.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btnText.innerText = "Perindah Kata";
            loadingIcon.style.display = "none";
        });
    }
</script>
@endpush