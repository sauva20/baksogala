@extends('layouts.app')

@section('title', 'Detail Pesanan & Review')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
{{-- Gunakan CSS yang sama seperti sebelumnya --}}
<style>
    /* ... (Paste CSS Anda yang tadi di sini) ... */
    :root { --primary: #B1935B; --navy: #2F3D65; --bg: #F4F6F9; --text: #333; }
    body { background-color: var(--bg); color: var(--text); padding-bottom: 50px; }
    footer { display: none !important; }
    .detail-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn-back { text-decoration: none; color: #666; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: 0.3s; }
    .btn-back:hover { color: var(--primary); }
    .btn-print { background: white; border: 1px solid #ddd; color: var(--navy); padding: 8px 15px; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .receipt-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #eee; position: relative; }
    .receipt-card::after { content: ""; position: absolute; bottom: 0; left: 0; right: 0; height: 10px; background: linear-gradient(135deg, transparent 50%, var(--bg) 50%), linear-gradient(45deg, var(--bg) 50%, transparent 50%); background-size: 20px 20px; }
    .receipt-header { background: var(--navy); color: white; padding: 25px 20px; text-align: center; }
    .receipt-title { margin: 0; font-size: 1.2rem; font-weight: 700; letter-spacing: 1px; }
    .receipt-subtitle { font-size: 0.85rem; opacity: 0.8; margin-top: 5px; }
    .order-status-badge { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; margin-top: 10px; background: rgba(255,255,255,0.2); color: white; }
    .receipt-body { padding: 25px 20px 40px 20px; }
    .item-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.95rem; }
    .item-name { font-weight: 600; color: #444; }
    .item-qty { color: #888; font-size: 0.85rem; margin-right: 5px; }
    .item-note { font-size: 0.8rem; color: #999; font-style: italic; margin-top: 2px; }
    .item-price { font-weight: 600; color: #333; }
    .summary-section { margin-top: 20px; padding-top: 15px; border-top: 2px dashed #eee; }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem; color: #666; }
    .summary-total { display: flex; justify-content: space-between; margin-top: 15px; padding-top: 15px; border-top: 2px solid #333; font-size: 1.2rem; font-weight: 800; color: var(--primary); }
    .review-card { background: white; border-radius: 15px; padding: 25px; margin-top: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); border: 1px solid #eee; text-align: center; }
    .rate { display: inline-block; height: 46px; padding: 0 10px; }
    .rate:not(:checked) > input { position:absolute; top:-9999px; }
    .rate:not(:checked) > label { float:right; width:1em; overflow:hidden; white-space:nowrap; cursor:pointer; font-size:35px; color:#ddd; }
    .rate:not(:checked) > label:before { content: '★ '; }
    .rate > input:checked ~ label { color: #ffc700; }
    .rate:not(:checked) > label:hover, .rate:not(:checked) > label:hover ~ label { color: #deb217; }
    .rate > input:checked + label:hover, .rate > input:checked + label:hover ~ label, .rate > input:checked ~ label:hover, .rate > input:checked ~ label:hover ~ label, .rate > label:hover ~ input:checked ~ label { color: #c59b08; }
    .form-control { border-radius: 10px; border: 1px solid #ddd; padding: 12px; width: 100%; font-family: inherit; }
    .btn-submit-review { width: 100%; background: var(--primary); color: white; border: none; padding: 12px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.3s; }
    .btn-submit-review:hover { background: #967d4d; }
    .ai-wrapper { position: relative; margin-bottom: 15px; }
    .btn-ai-polish { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 8px 15px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 5px; position: absolute; bottom: 10px; right: 10px; z-index: 10; box-shadow: 0 4px 10px rgba(118, 75, 162, 0.3); transition: transform 0.2s; }
    .btn-ai-polish:hover { transform: scale(1.05); }
    .ai-loading { display: none; margin-left: 5px; }
    .fa-spin { animation: fa-spin 1s infinite linear; }
    @keyframes fa-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    @media print { body { background: white; padding: 0; } .detail-nav, .review-card, footer { display: none !important; } .container { max-width: 100%; padding: 0; margin: 0; } .receipt-card { box-shadow: none; border: none; border-radius: 0; } .receipt-card::after { display: none; } .receipt-header { color: black; background: white; border-bottom: 2px solid black; padding: 10px 0; } .order-status-badge { color: black; border: 1px solid black; } .summary-total { color: black; } }
</style>
@endsection

@section('content')
<div class="container" style="max-width: 500px; padding: 30px 20px;">
    
    <div class="detail-nav">
        <a href="{{ route('orders.show', $order->id) }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <button onclick="window.print()" class="btn-print">
            <i class="fas fa-print"></i> Cetak Struk
        </button>
    </div>

    {{-- STRUK PEMBAYARAN --}}
    <div class="receipt-card">
        <div class="receipt-header">
            <h1 class="receipt-title">BAKSO GALA</h1>
            <div class="receipt-subtitle">Jl. Otto Iskandardinata No.115, Subang</div>
            <div style="margin-top: 15px;">
                <div style="font-size: 0.9rem; font-weight: 600;">NO. PESANAN</div>
                <div style="font-size: 1.2rem; font-weight: 800; letter-spacing: 2px;">#{{ $order->id }}</div>
            </div>
            
            @php
                $isPaidOrProcessed = $order->payment_status == 'paid' || in_array($order->status, ['process', 'preparing', 'ready', 'completed']);
            @endphp

            <div class="order-status-badge">
                {{ $isPaidOrProcessed ? 'LUNAS / DIPROSES' : 'BELUM BAYAR' }}
            </div>
            <div style="margin-top: 5px; font-size: 0.8rem;">
                {{ $order->created_at->format('d M Y, H:i') }} WIB
            </div>
        </div>

        <div class="receipt-body">
            @php $subtotal = 0; @endphp
            @foreach($order->orderDetails as $detail)
                @php $subtotal += $detail->subtotal; @endphp
                <div class="item-row">
                    <div style="flex: 1;">
                        <span class="item-qty">{{ $detail->quantity }}x</span>
                        <span class="item-name">{{ $detail->menuItem->name ?? 'Item Dihapus' }}</span>
                        @if($detail->item_notes)
                            <div class="item-note">{{ $detail->item_notes }}</div>
                        @endif
                    </div>
                    <div class="item-price">{{ number_format($detail->subtotal, 0, ',', '.') }}</div>
                </div>
            @endforeach

            <div class="summary-section">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                @php $serviceFee = $order->total_price - $subtotal; @endphp
                <div class="summary-row">
                    <span>Biaya Layanan (0.7%)</span>
                    <span>Rp {{ number_format($serviceFee, 0, ',', '.') }}</span>
                </div>
                <div class="summary-total">
                    <span>TOTAL</span>
                    <span>Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                </div>
                @if($order->payment_method)
                <div style="text-align: center; margin-top: 15px; font-size: 0.8rem; color: #888;">
                    Metode: {{ strtoupper($order->payment_method) }}
                </div>
                @endif
            </div>
            <div style="text-align: center; margin-top: 30px; font-size: 0.8rem; color: #999;">
                -- Terima Kasih --<br>Simpan struk ini sebagai bukti pembayaran.
            </div>
        </div>
    </div>

    {{-- BAGIAN REVIEW --}}
    @if($isPaidOrProcessed)
        <div class="review-card">
            <h4 style="margin-bottom: 20px; color: var(--navy); font-weight: 700;">Ulasan Pelanggan</h4>

            @if($order->review)
                {{-- TAMPILAN SUDAH REVIEW --}}
                <div>
                    <div style="color: #ffc700; font-size: 1.5rem; margin-bottom: 10px;">
                        @for($i=0; $i<$order->review->rating; $i++) ★ @endfor
                        @for($i=0; $i<(5-$order->review->rating); $i++) <span style="color: #eee;">★</span> @endfor
                    </div>
                    <p style="font-style: italic; color: #555;">"{{ $order->review->comment }}"</p>
                    @if($order->review->photo)
                        <img src="{{ asset('storage/' . $order->review->photo) }}" alt="Review" style="width: 100%; border-radius: 10px; margin-top: 10px; object-fit: cover;">
                    @endif
                    <div style="margin-top: 15px; color: #28a745; font-weight: bold; font-size: 0.9rem;">
                        <i class="fas fa-check-circle"></i> Ulasan Terkirim
                    </div>
                    
                    @if($order->review->is_featured)
                    <div style="margin-top: 10px; font-size: 0.8rem; color: #B1935B; font-weight: bold; background: #fff8e1; padding: 5px; border-radius: 5px; display: inline-block;">
                        <i class="fas fa-star"></i> Tampil di Beranda (Pilihan AI)
                    </div>
                    @endif
                </div>
            @else
                {{-- FORM REVIEW (TANPA AJAX SUBMIT, NORMAL POST) --}}
                <form id="reviewForm" action="{{ route('orders.review.store', $order->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    {{-- TAMPILKAN ERROR VALIDASI JIKA ADA --}}
                    @if($errors->any())
                        <div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 8px; margin-bottom: 15px; text-align: left; font-size: 0.85rem;">
                            <ul style="margin: 0; padding-left: 20px;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div style="margin-bottom: 15px;">
                        <div class="rate">
                            <input type="radio" id="star5" name="rating" value="5" /><label for="star5">5</label>
                            <input type="radio" id="star4" name="rating" value="4" /><label for="star4">4</label>
                            <input type="radio" id="star3" name="rating" value="3" /><label for="star3">3</label>
                            <input type="radio" id="star2" name="rating" value="2" /><label for="star2">2</label>
                            <input type="radio" id="star1" name="rating" value="1" /><label for="star1">1</label>
                        </div>
                    </div>

                    <div class="ai-wrapper">
                        <textarea id="reviewComment" name="comment" class="form-control" rows="4" placeholder="Tulis ulasan kasar Anda, biar AI yang perindah... (Contoh: 'Enak banget baksonya kuahnya mantap')" required></textarea>
                        
                        <button type="button" class="btn-ai-polish" onclick="polishWithAI()">
                            <i class="fas fa-magic"></i> 
                            <span>Perindah Kata</span>
                            <i class="fas fa-spinner fa-spin ai-loading"></i>
                        </button>
                    </div>

                    <div style="text-align: left; margin-bottom: 15px;">
                        <label style="font-size: 0.85rem; font-weight: 600; color: #666; display: block; margin-bottom: 5px;">Foto Makanan (Opsional)</label>
                        <input type="file" name="photo" class="form-control" style="padding: 8px;">
                    </div>

                    {{-- TOMBOL SUBMIT --}}
                    <button type="submit" id="btnSubmitReview" class="btn-submit-review" onclick="this.innerHTML='Mengirim...';">Kirim Ulasan</button>
                </form>
            @endif
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // --- 1. POPUP STATUS SETELAH REDIRECT DARI CONTROLLER ---
    @if(session('success'))
        Swal.fire({
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonColor: '#B1935B'
        });
    @endif

    @if(session('error'))
        Swal.fire({
            title: 'Gagal',
            text: "{{ session('error') }}",
            icon: 'error',
            confirmButtonColor: '#B1935B'
        });
    @endif

    // --- 2. LOGIKA AI PERINDAH KATA (POLISH) ---
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