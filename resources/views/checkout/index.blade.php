@extends('layouts.app')

@section('title', 'Checkout - Bakso Gala')

@section('styles')
<link rel="icon" href="{{ asset('assets/images/GALA.png') }}" type="image/png">
<style>
    :root {
        --primary: #B1935B;
        --navy: #2F3D65;
        --bg: #F4F6F9;
    }
    body { background-color: var(--bg); padding-bottom: 40px; }

    /* FIX: MENYEMBUNYIKAN FOOTER DI HALAMAN CHECKOUT */
    footer { display: none !important; }

    .checkout-container { max-width: 600px; margin: 0 auto; padding: 20px; }

    .checkout-header { text-align: center; margin-bottom: 25px; }
    .checkout-header h1 { font-size: 1.5rem; font-weight: 800; color: var(--navy); margin: 0; }
    .checkout-header p { color: #666; font-size: 0.9rem; }

    /* Alert Box */
    .alert-box { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
    .alert-error { background-color: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }

    /* Card Style */
    .co-card {
        background: white; border-radius: 12px; padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 20px; border: 1px solid #eee;
    }
    .co-title { font-size: 1rem; font-weight: 700; color: var(--navy); margin-bottom: 15px; display: block; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; }

    /* Dining Options Tabs */
    .dining-tabs { display: flex; gap: 10px; margin-bottom: 20px; }
    .dining-tab {
        flex: 1; padding: 12px; text-align: center; border: 1px solid #ddd;
        border-radius: 8px; cursor: pointer; transition: 0.2s; background: white; color: #555;
    }
    .dining-tab input { display: none; }
    
    /* Logic CSS untuk Tab Aktif */
    .dining-tab:has(input:checked) { 
        border-color: var(--primary); background: #fff8e1; color: var(--primary); font-weight: bold; 
    }
    .dining-tab i { font-size: 1.2rem; display: block; margin-bottom: 5px; }

    /* Payment Methods */
    .payment-option {
        display: flex; align-items: center; padding: 15px; border: 1px solid #ddd;
        border-radius: 10px; margin-bottom: 10px; cursor: pointer; transition: 0.2s;
    }
    .payment-option.active { border-color: var(--primary); background-color: #fff8e1; }
    .payment-option input { margin-right: 15px; accent-color: var(--primary); transform: scale(1.2); }
    .pay-icon { font-size: 1.5rem; margin-right: 15px; color: var(--navy); width: 30px; text-align: center;}
    .pay-info h4 { margin: 0; font-size: 0.95rem; font-weight: 700; color: #333; }
    .pay-info p { margin: 0; font-size: 0.8rem; color: #777; }

    /* Forms */
    .form-group { margin-bottom: 15px; }
    .form-label { font-weight: 600; font-size: 0.85rem; color: #444; margin-bottom: 5px; display: block; }
    .form-input {
        width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;
        font-size: 0.95rem; outline: none; transition: 0.2s;
    }
    .form-input:focus { border-color: var(--primary); }

    #dineInFields { display: none; animation: fadeIn 0.3s; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }

    /* Summary & Total */
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem; color: #555; }
    .co-total { border-top: 1px dashed #ddd; padding-top: 12px; margin-top: 12px; font-weight: 800; font-size: 1.1rem; color: var(--primary); }

    .btn-confirm {
        width: 100%; background: var(--primary); color: white; border: none;
        padding: 15px; border-radius: 10px; font-weight: 700; font-size: 1rem;
        cursor: pointer; transition: 0.2s;
    }
    .btn-confirm:hover { background: #967d4d; }
    
    .login-promo {
        background: #e3f2fd; padding: 10px 15px; border-radius: 8px; margin-bottom: 20px;
        font-size: 0.85rem; color: #1565c0; display: flex; justify-content: space-between; align-items: center;
    }
    .login-promo a { font-weight: bold; color: #0d47a1; text-decoration: underline; }
</style>
@endsection

@section('content')
<div class="checkout-container">

    <div class="checkout-header">
        <h1>Checkout Pesanan</h1>
        <p>Lengkapi data untuk memproses pesanan</p>
    </div>

    @if(session('error'))
        <div class="alert-box alert-error">
            <strong>Gagal:</strong> {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert-box alert-error">
            <strong>Mohon periksa kembali:</strong>
            <ul style="margin: 5px 0 0 15px; padding:0;">
                @foreach ($errors->all() as $error)
                    <li>- {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @guest
    <div class="login-promo">
        <span><i class="fas fa-gift"></i> Punya voucher promo?</span>
        <a href="{{ route('login') }}">Login</a>
    </div>
    @endguest

    <form action="{{ route('checkout.store') }}" method="POST">
        @csrf
        
        @php
            $subtotal = $grandTotal;
            $appFee = $subtotal * 0.007;
            $finalTotal = ceil($subtotal + $appFee); 
            
            // --- AMBIL DATA DARI SESSION (YANG DISIMPAN DI KERANJANG) ---
            $sessDining = session('dining_option', 'dine_in'); // Default Dine In
            $sessTable  = session('table_number');
            $sessArea   = session('table_area');
        @endphp

        <input type="hidden" name="grand_total" value="{{ $finalTotal }}">
        <input type="hidden" name="payment_method" value="midtrans">

        {{-- 1. DATA PEMESAN --}}
        <div class="co-card">
            <span class="co-title">Informasi Pemesan</span>
            <div class="form-group">
                <label class="form-label">Nama Lengkap <span style="color:red">*</span></label>
                <input type="text" name="customer_name" class="form-input" value="{{ Auth::check() ? Auth::user()->name : '' }}" required placeholder="Contoh: Budi Santoso">
            </div>
            <div class="form-group">
                <label class="form-label">Nomor WhatsApp <span style="color:red">*</span></label>
                <input type="number" name="customer_phone" class="form-input" value="{{ Auth::check() ? Auth::user()->phone_number : '' }}" required placeholder="08xxxxxxxxxx">
                <small style="color: #888; font-size: 0.75rem;">Wajib diisi untuk konfirmasi pesanan.</small>
            </div>
        </div>

        {{-- 2. TIPE PESANAN (OTOMATIS TERISI DARI KERANJANG) --}}
        <div class="co-card">
            <span class="co-title">Tipe Pesanan</span>
            <div class="dining-tabs">
                <label class="dining-tab" onclick="setDining('dine_in')">
                    {{-- Cek session untuk menentukan mana yang checked --}}
                    <input type="radio" name="dining_option" value="dine_in" {{ $sessDining == 'dine_in' ? 'checked' : '' }}>
                    <i class="fas fa-chair"></i> Dine In
                </label>
                <label class="dining-tab" onclick="setDining('take_away')">
                    <input type="radio" name="dining_option" value="take_away" {{ $sessDining == 'take_away' ? 'checked' : '' }}>
                    <i class="fas fa-shopping-bag"></i> Take Away
                </label>
            </div>

            <div id="dineInFields">
                <div class="form-group">
                    <label class="form-label">Lokasi Duduk <span style="color:red">*</span></label>
                    
                    {{-- DROPDOWN AREA (SESUAI PERMINTAAN) --}}
                    <select name="dining_area" id="areaInput" class="form-input">
                        <option value="" disabled {{ !$sessArea ? 'selected' : '' }}>-- Pilih Lokasi --</option>
                        
                        <option value="Lantai 2 Gym" {{ $sessArea == 'Lantai 2 Gym' ? 'selected' : '' }}>
                            Lantai 2 Gym
                        </option>
                        
                        <option value="Indoor More" {{ $sessArea == 'Indoor More' ? 'selected' : '' }}>
                            Indoor More
                        </option>
                        
                        <option value="Depan Utama" {{ $sessArea == 'Depan Utama' ? 'selected' : '' }}>
                            Depan Utama
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Nomor Meja <span style="color:red">*</span></label>
                    {{-- Value otomatis dari session --}}
                    <input type="number" name="table_number" id="tableInput" class="form-input" value="{{ $sessTable }}" placeholder="Contoh: 12">
                </div>
            </div>
            
<div class="form-group">
    <label class="form-label">Catatan Pesanan (Opsional)</label>
    {{-- Tambahkan {{ $compiledNotes ?? '' }} di antara tag textarea --}}
    <textarea name="order_notes" class="form-input" rows="2" placeholder="Cth: Jangan terlalu pedas...">{{ $compiledNotes ?? '' }}</textarea>
</div>
        </div>

        {{-- 3. METODE PEMBAYARAN --}}
        <div class="co-card">
            <span class="co-title">Metode Pembayaran</span>
            <label class="payment-option active" style="cursor: default;">
                <div class="pay-icon"><i class="fas fa-qrcode"></i></div>
                <div class="pay-info">
                    <h4>QRIS / E-Wallet</h4>
                    <p>Gopay, ShopeePay, Dana, OVO, dll.</p>
                </div>
                <div style="margin-left: auto; font-size: 1.2rem; color: var(--primary);">
                    <i class="fas fa-check-circle"></i>
                </div>
            </label>
        </div>

        {{-- 4. TOTAL & TOMBOL --}}
        <div class="co-card" style="border: 1px solid var(--primary); background: #fffdf5;">
            <div class="summary-row">
                <span>Subtotal</span>
                <strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
            </div>
            <div class="summary-row">
                <span>Biaya Aplikasi (0.7%)</span>
                <strong>Rp {{ number_format($appFee, 0, ',', '.') }}</strong>
            </div>
            <div class="summary-row co-total">
                <span>Total Bayar</span>
                <span>Rp {{ number_format($finalTotal, 0, ',', '.') }}</span>
            </div>
            
            <button type="submit" class="btn-confirm">
                Bayar Sekarang <i class="fas fa-arrow-right"></i>
            </button>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
    function setDining(type) {
        // Hapus styling active manual (karena kita pakai CSS :has checked)
        // Tapi untuk fallback JS:
        const dineInBox = document.getElementById('dineInFields');
        const areaInput = document.getElementById('areaInput');
        const tableInput = document.getElementById('tableInput');

        if (type === 'dine_in') {
            dineInBox.style.display = 'block';
            // Set required via JS agar validasi form jalan
            areaInput.setAttribute('required', 'required');
            tableInput.setAttribute('required', 'required');
        } else {
            dineInBox.style.display = 'none';
            areaInput.removeAttribute('required');
            tableInput.removeAttribute('required');
        }
    }

    // Jalankan saat halaman load untuk set tampilan awal sesuai Session
    document.addEventListener("DOMContentLoaded", function() {
        // Cek mana radio yang checked dari PHP
        const selectedOption = document.querySelector('input[name="dining_option"]:checked').value;
        setDining(selectedOption);
    });
</script>
@endpush