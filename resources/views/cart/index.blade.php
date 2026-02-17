@extends('layouts.app')

@section('title', 'Keranjang - Bakso Gala')

@section('styles')
<link rel="icon" href="{{ asset('assets/images/GALA.png') }}" type="image/png">
    <style>
        /* --- GLOBAL COLORS --- */
        :root {
            --primary-color: #B1935B;  
            --secondary-color: #2F3D65; 
            --bg-color: #F4F6F9;
            --white: #ffffff;
            --text-dark: #333;
            --text-gray: #777;
        }

        body { background-color: var(--bg-color); padding-bottom: 140px; padding-top: 60px; }
        footer { display: none !important; }

        /* HEADER */
        .cart-header {
            position: fixed; top: 0; left: 0; width: 100%;
            background: var(--white); padding: 15px 20px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 1px 5px rgba(0,0,0,0.05); z-index: 1000;
        }
        .btn-back-header {
            position: absolute; left: 20px; font-size: 1.2rem; 
            color: var(--text-dark); cursor: pointer; text-decoration: none;
        }
        .header-title { font-size: 1.1rem; font-weight: 700; color: var(--text-dark); margin: 0; }

        /* INFO LOKASI / TEMPAT */
        .order-type-box {
            background: #fff; border: 1px solid #eee;
            padding: 15px; border-radius: 12px; margin-bottom: 20px;
            display: flex; justify-content: space-between; align-items: center;
            color: var(--text-dark); cursor: pointer; transition: 0.2s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        
        .order-type-box.branding {
            background: #fff8e1; border: 1px solid #ffe0b2;
        }
        .order-type-box.branding .main-text { color: #f57f17; }

        .order-type-box.warning {
            background: #fff3cd; border: 1px solid #ffeeba;
            animation: pulse 2s infinite;
        }
        .order-type-box.warning .main-text { color: #856404; }

        .info-label { font-size: 0.75rem; color: #888; display: block; margin-bottom: 4px; }
        .info-value { font-size: 1rem; font-weight: 700; display: block; }
        
        @keyframes pulse { 
            0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4); } 
            70% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); } 
            100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); } 
        }

        /* MENU TERKAIT */
        .related-menu-section { margin-bottom: 25px; }
        .section-title { font-size: 0.95rem; font-weight: 700; margin-bottom: 10px; color: var(--text-dark); }
        .related-scroll {
            display: flex; overflow-x: auto; gap: 12px; padding-bottom: 10px;
            -ms-overflow-style: none; scrollbar-width: none;
        }
        .related-scroll::-webkit-scrollbar { display: none; }
        .related-card {
            min-width: 140px; background: var(--white); border-radius: 10px; padding: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; flex-direction: column;
            border: 1px solid #eee; position: relative;
        }
        .related-img { width: 100%; height: 80px; object-fit: cover; border-radius: 8px; margin-bottom: 8px; }
        .related-name { font-size: 0.85rem; font-weight: 600; line-height: 1.2; margin-bottom: 4px; }
        .related-price { font-size: 0.85rem; font-weight: 700; color: var(--secondary-color); }
        .btn-add-mini {
            position: absolute; bottom: 10px; right: 10px;
            width: 24px; height: 24px; border-radius: 50%;
            background: var(--white); border: 1px solid var(--primary-color);
            color: var(--primary-color); display: flex; align-items: center; justify-content: center;
            font-size: 1rem; cursor: pointer;
        }

        /* CARD ITEM PESANAN */
        .cart-section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .btn-add-more { border: 1px solid var(--primary-color); color: var(--primary-color); background: white; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-decoration: none; }

        .cart-item-card {
            background: var(--white); border-radius: 12px; padding: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03); margin-bottom: 15px;
            border: 1px solid #f0f0f0;
        }
        .item-header { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .item-name { font-weight: 700; font-size: 1rem; color: var(--text-dark); }
        
        /* Style khusus Topping */
        .item-addons { 
            font-size: 0.85rem; 
            color: #B1935B; 
            margin-bottom: 8px; 
            font-weight: 600; 
            background: #fff8e1;
            display: inline-block;
            padding: 3px 8px;
            border-radius: 5px;
        }
        
        .item-price-row { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; }
        .item-price { font-weight: 700; color: var(--secondary-color); font-size: 1rem; }

        .qty-wrapper { display: flex; align-items: center; gap: 12px; }
        .qty-btn { width: 28px; height: 28px; border-radius: 50%; border: 1px solid #ddd; display: flex; align-items: center; justify-content: center; font-size: 1rem; cursor: pointer; color: #555; }
        .qty-val { font-weight: 600; font-size: 1rem; min-width: 20px; text-align: center; }

        .item-note-box {
            display: flex; align-items: center; gap: 8px; 
            background: #fafafa; padding: 8px 10px; border-radius: 6px; 
            margin-top: 5px; font-size: 0.85rem; color: #666; cursor: pointer;
        }

        /* CATATAN UMUM */
        .general-note { display: flex; align-items: center; gap: 10px; padding: 15px 0; border-bottom: 1px solid #eee; margin-bottom: 20px; cursor: pointer; }
        .general-note span { font-size: 0.9rem; font-weight: 600; color: #555; font-style: italic; }

        /* RINGKASAN PEMBAYARAN */
        .payment-summary {
            background: var(--white); border-radius: 12px; padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03); margin-bottom: 20px; border: 1px solid #f0f0f0;
        }
        .summary-title { font-weight: 700; font-size: 1rem; margin-bottom: 15px; text-align: center; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9rem; color: #555; }
        .summary-row.total { border-top: 1px dashed #ddd; padding-top: 15px; margin-top: 10px; margin-bottom: 0; font-weight: 800; font-size: 1.1rem; color: var(--primary-color); }

        /* BOTTOM BAR */
        .sticky-bottom-pay {
            position: fixed; bottom: 0; left: 0; width: 100%;
            background: var(--white); padding: 15px 20px;
            border-radius: 20px 20px 0 0;
            box-shadow: 0 -5px 20px rgba(0,0,0,0.05); z-index: 1000;
        }
        .total-pay-label { font-size: 0.85rem; color: #777; margin-bottom: 2px; }
        .total-pay-value { font-size: 1.2rem; font-weight: 800; color: #222; }
        
        .btn-pay-now {
            background: var(--primary-color); color: white; border: none;
            width: 100%; padding: 14px; border-radius: 10px; font-weight: 700; font-size: 1rem;
            margin-top: 12px; cursor: pointer; text-align: center; display: block; text-decoration: none;
        }
        .empty-cart { text-align: center; padding: 50px 20px; }
        .empty-cart img { width: 120px; opacity: 0.5; margin-bottom: 20px; }
    </style>
@endsection

@section('content')

{{-- Header --}}
<div class="cart-header">
    <a href="{{ route('menu.index') }}" class="btn-back-header"><i class="fas fa-arrow-left"></i></a>
    <h1 class="header-title">Pesanan</h1>
</div>

<div class="container" style="max-width: 600px;">
    
    @if(isset($finalCartItems) && count($finalCartItems) > 0)

        @php
            // Hitung Total (Subtotal + Biaya Bungkus) untuk dasar AppFee
            $subtotalWithPackaging = $subtotal + $totalPackagingFee;
            $appFee = $subtotalWithPackaging * 0.007;
            $finalTotal = ceil($subtotalWithPackaging + $appFee);

            // --- LOGIC TAMPILAN TEMPAT & MEJA ---
            $diningOption = session('dining_option', 'dine_in'); 
            $tableNumber  = session('table_number', null);      
            $tableArea    = session('table_area', null); // Ambil data Area
            
            // Text Logic
            if ($diningOption == 'dine_in') {
                $diningText = "Makan di Tempat";
                if($tableNumber && $tableArea) {
                    // Tampilkan Area dan Nomor Meja
                    $locationText = "{$tableArea} - Meja {$tableNumber}";
                } else {
                    $locationText = "Pilih Area & Meja";
                }
                $icon = "fa-utensils";
            } else {
                $diningText = "Bungkus (Take Away)";
                $locationText = "Ambil di Kasir"; 
                $icon = "fa-shopping-bag";
            }
            
            // Validasi: Dine In wajib punya meja & area
            $isValidOrder = true; 
            if($diningOption == 'dine_in' && (empty($tableNumber) || empty($tableArea))){
                $isValidOrder = false; 
            }

            $boxClass = $isValidOrder ? 'branding' : 'warning';
        @endphp

        {{-- 1. INFO LOKASI / TEMPAT --}}
        <div class="order-type-box {{ $boxClass }}" onclick="updateOrderInfo()">
            {{-- Kiri: Tipe Makan --}}
            <div style="flex: 1;">
                <span class="info-label">Tipe Pesanan <i class="fas fa-pencil-alt" style="font-size:0.7em;"></i></span>
                <span class="info-value main-text">
                    <i class="fas {{ $icon }}"></i> {{ $diningText }}
                </span>
            </div>
            
            {{-- Kanan: Detail Lokasi (Meja atau Kasir) --}}
            <div style="text-align: right; border-left: 1px solid #ddd; padding-left: 15px; margin-left: 10px;">
                <span class="info-label">Lokasi Anda</span>
                <span class="info-value" style="{{ !$isValidOrder ? 'color:#d32f2f; text-decoration:underline;' : '' }}">
                    {{ $locationText }}
                </span>
            </div>
        </div>

        @if(!$isValidOrder)
            <div style="color: #dc3545; font-size: 0.8rem; margin-top: -15px; margin-bottom: 15px; text-align: center; font-weight: 600;">
                <i class="fas fa-exclamation-circle"></i> Mohon lengkapi Area dan Nomor Meja.
            </div>
        @endif

        {{-- Menu Terkait --}}
        @if(isset($relatedMenus) && count($relatedMenus) > 0)
        <div class="related-menu-section">
            <h3 class="section-title">Jangan Lupa Tambah Ini</h3>
            <div class="related-scroll">
                @foreach($relatedMenus as $rekomen)
                <div class="related-card" onclick="window.location.href='{{ route('menu.index') }}'">
                    <img src="{{ asset($rekomen->image_url) }}" class="related-img">
                    <div class="related-name">{{ Str::limit($rekomen->name, 15) }}</div>
                    <div class="related-price">Rp {{ number_format($rekomen->price, 0, ',', '.') }}</div>
                    <div class="btn-add-mini"><i class="fas fa-plus"></i></div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- List Item Pesanan --}}
        <div class="cart-section-header">
            <span class="section-title">Daftar Pesanan ({{ count($finalCartItems) }})</span>
            <a href="{{ route('menu.index') }}" class="btn-add-more">+ Tambah</a>
        </div>

        @foreach ($finalCartItems as $item)
            <div class="cart-item-card">
                <div class="item-header">
                    <span class="item-name">{{ $item->menu_name }}</span>
                </div>

                {{-- Tampilkan Topping --}}
                @if(!empty($item->addons_list))
                    <div class="item-addons">+ {{ $item->addons_list }}</div>
                @endif

                {{-- Catatan --}}
                <div class="item-note-box" onclick="editNote({{ $item->id }}, '{{ $item->notes }}')">
                    <i class="far fa-edit"></i> 
                    @if(!empty($item->notes))
                        <span style="color: #333;">{{ Str::limit($item->notes, 35) }}</span>
                    @else
                        <span>Tulis catatan...</span>
                    @endif
                </div>

                <div class="item-price-row">
                    <div class="item-price">Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                    
                    <div class="qty-wrapper">
                        <div class="qty-btn" onclick="changeQty({{ $item->id }}, {{ $item->quantity - 1 }})"><i class="fas fa-minus"></i></div>
                        <div class="qty-val">{{ $item->quantity }}</div>
                        <div class="qty-btn" onclick="changeQty({{ $item->id }}, {{ $item->quantity + 1 }})"><i class="fas fa-plus"></i></div>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Ringkasan Bayar --}}
        <div class="payment-summary">
            <div class="summary-title">Rincian Pembayaran</div>
            
            <div class="summary-row">
                <span>Subtotal</span>
                <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>

            {{-- TAMBAHAN: TAMPILKAN BIAYA BUNGKUS JIKA ADA --}}
            @if($totalPackagingFee > 0)
            <div class="summary-row" style="color: #d32f2f;">
                <span>Biaya Kemasan (Take Away)</span>
                <span>+ Rp {{ number_format($totalPackagingFee, 0, ',', '.') }}</span>
            </div>
            @endif

            <div class="summary-row">
                <span>Biaya Layanan (0.7%)</span>
                <span>Rp {{ number_format($appFee, 0, ',', '.') }}</span>
            </div>
            
            <div class="summary-row total">
                <span>Total</span>
                <span>Rp {{ number_format($finalTotal, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Bottom Bar --}}
        <div class="sticky-bottom-pay">
            <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                <div>
                    <div class="total-pay-label">Total Tagihan</div>
                    <div class="total-pay-value">Rp {{ number_format($finalTotal, 0, ',', '.') }}</div>
                </div>
            </div>
            
            @if($isValidOrder)
                <a href="{{ route('checkout.index') }}" class="btn-pay-now">
                    Lanjut Pembayaran
                </a>
            @else
                <button onclick="updateOrderInfo()" class="btn-pay-now" style="background: #999;">
                    Lengkapi Lokasi
                </button>
            @endif
        </div>

    @else
        <div class="empty-cart">
            <img src="https://cdn-icons-png.flaticon.com/512/11329/11329060.png" alt="Empty">
            <h3>Keranjang Kosong</h3>
            <p style="color: #888;">Belum ada menu yang dipilih.</p>
            <a href="{{ route('menu.index') }}" class="btn-pay-now" style="display: inline-block; width: auto; padding: 10px 30px;">Pesan Sekarang</a>
        </div>
    @endif

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // --- POPUP UPDATE LOKASI & MEJA & AREA ---
    function updateOrderInfo() {
        Swal.fire({
            title: 'Atur Lokasi Makan',
            html: `
                <div style="text-align:left;">
                    <label style="font-weight:bold; display:block; margin-bottom:5px;">Pilih Tipe Pesanan:</label>
                    <select id="swal-type" class="swal2-input" style="width:100%; margin:0 0 15px;" onchange="toggleTable(this.value)">
                        <option value="dine_in" {{ session('dining_option') == 'dine_in' ? 'selected' : '' }}>Makan di Tempat</option>
                        <option value="take_away" {{ session('dining_option') == 'take_away' ? 'selected' : '' }}>Bungkus (Take Away)</option>
                    </select>
                    
                    <div id="swal-table-box" style="display:{{ session('dining_option', 'dine_in') == 'dine_in' ? 'block' : 'none' }}">
                        {{-- PILIHAN AREA --}}
                        <label style="font-weight:bold; display:block; margin-bottom:5px;">Pilih Area:</label>
                        <select id="swal-area" class="swal2-input" style="width:100%; margin:0 0 15px;">
                            <option value="">-- Pilih Area --</option>
                            <option value="Lantai 2 Gym" {{ session('table_area') == 'Lantai 2 Gym' ? 'selected' : '' }}>Lantai 2 Gym</option>
                            <option value="Indoor More" {{ session('table_area') == 'Indoor More' ? 'selected' : '' }}>Indoor More</option>
                            <option value="Depan Utama" {{ session('table_area') == 'Depan Utama' ? 'selected' : '' }}>Depan Utama</option>
                            {{-- PENAMBAHAN AREA BARU --}}
                            <option value="Photobooth" {{ session('table_area') == 'Photobooth' ? 'selected' : '' }}>Area Photobooth</option>
                        </select>

                        <label style="font-weight:bold; display:block; margin-bottom:5px;">Nomor Meja:</label>
                        <input type="number" id="swal-table" class="swal2-input" style="width:100%; margin:0;" placeholder="Contoh: 5" value="{{ session('table_number') }}">
                    </div>
                </div>
            `,
            confirmButtonText: 'Simpan',
            confirmButtonColor: '#B1935B',
            showCancelButton: true,
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const type = document.getElementById('swal-type').value;
                const table = document.getElementById('swal-table').value;
                const area = document.getElementById('swal-area').value;

                if(type === 'dine_in') {
                    if(!area) Swal.showValidationMessage('Wajib pilih Area!');
                    else if(!table) Swal.showValidationMessage('Wajib isi Nomor Meja!');
                }
                return { type, table, area };
            }
        }).then((res) => {
            if(res.isConfirmed) saveInfo(res.value.type, res.value.table, res.value.area);
        });
    }

    function toggleTable(val) {
        document.getElementById('swal-table-box').style.display = val === 'dine_in' ? 'block' : 'none';
    }

    function saveInfo(type, table, area) {
        fetch('{{ route("cart.saveInfo") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({ dining_option: type, table_number: table, table_area: area })
        }).then(() => location.reload());
    }

    // UPDATE QTY & REMOVE
    function changeQty(id, qty) {
        if(qty < 1) { 
            Swal.fire({
                title: 'Hapus menu?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya', cancelButtonText: 'Batal', confirmButtonColor: '#d33'
            }).then((r) => { if(r.isConfirmed) removeItem(id); });
            return; 
        }
        fetch('{{ route("cart.update") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({ cart_id: id, quantity: qty })
        }).then(() => location.reload());
    }

    function removeItem(id) {
        fetch('{{ route("cart.remove") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({ cart_id: id })
        }).then(() => location.reload());
    }

    function editNote(id, note) {
        Swal.fire({
            title: 'Catatan', input: 'text', inputValue: note, inputPlaceholder: 'Contoh: Jangan pedas...', confirmButtonText: 'Simpan', confirmButtonColor: '#B1935B'
        }).then((r) => {
            if(r.isConfirmed) {
                fetch('{{ route("cart.updateNote") }}', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    body: JSON.stringify({ cart_id: id, note: r.value })
                }).then(() => location.reload());
            }
        });
    }
</script>
@endpush