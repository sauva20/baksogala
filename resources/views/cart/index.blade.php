@extends('layouts.app')

@section('title', 'Keranjang - Bakso Gala')

@section('styles')
    <style>
        /* --- GLOBAL COLORS --- */
        :root {
            --primary-color: #B1935B;  /* Emas */
            --secondary-color: #2F3D65; /* Navy */
            --bg-color: #F4F6F9;
            --white: #ffffff;
            --text-dark: #333;
            --text-gray: #777;
        }

        body { background-color: var(--bg-color); padding-bottom: 140px; padding-top: 60px; }

        /* FIX: MENYEMBUNYIKAN FOOTER BAWAAN */
        footer { display: none !important; }

        /* --- 1. HEADER (Fixed Top) --- */
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

        /* --- 2. TIPE PEMESANAN (Advanced) --- */
        .order-type-box {
            background: #fff; border: 1px solid #eee;
            padding: 12px 15px; border-radius: 8px; margin-bottom: 20px;
            display: flex; justify-content: space-between; align-items: center;
            color: var(--text-dark); font-weight: 600; font-size: 0.9rem;
            cursor: pointer; transition: 0.2s;
        }
        
        /* Style untuk status: SUDAH LENGKAP (Branding Color) */
        .order-type-box.branding {
            background: #fff8e1; border: 1px solid #ffe0b2; color: #f57f17;
        }

        /* Style untuk status: BELUM LENGKAP (Warning Color + Pulse Animation) */
        .order-type-box.warning {
            background: #fff3cd; border: 1px solid #ffeeba; color: #856404;
            animation: pulse 2s infinite;
        }
        @keyframes pulse { 
            0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4); } 
            70% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); } 
            100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); } 
        }

        /* --- 3. MENU TERKAIT (Horizontal Scroll) --- */
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

        /* --- 4. ITEM PESANAN (CARD) --- */
        .cart-section-header {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;
        }
        .btn-add-more {
            border: 1px solid var(--primary-color); color: var(--primary-color);
            background: white; padding: 5px 12px; border-radius: 20px;
            font-size: 0.8rem; font-weight: 600; text-decoration: none;
        }

        .cart-item-card {
            background: var(--white); border-radius: 12px; padding: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03); margin-bottom: 15px;
            border: 1px solid #f0f0f0;
        }
        .item-header { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .item-name { font-weight: 700; font-size: 1rem; color: var(--text-dark); }
        .item-details { font-size: 0.85rem; color: var(--text-gray); margin-bottom: 10px; line-height: 1.4; }
        .item-price-row { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; }
        .item-price { font-weight: 700; color: var(--secondary-color); font-size: 1rem; }

        /* Qty Control */
        .qty-wrapper { display: flex; align-items: center; gap: 12px; }
        .qty-btn { 
            width: 28px; height: 28px; border-radius: 50%; border: 1px solid #ddd; 
            display: flex; align-items: center; justify-content: center; font-size: 1rem; cursor: pointer; color: #555;
        }
        .qty-val { font-weight: 600; font-size: 1rem; min-width: 20px; text-align: center; }

        /* Catatan Per Item */
        .item-note-box {
            display: flex; align-items: center; gap: 8px; 
            background: #fafafa; padding: 8px 10px; border-radius: 6px; 
            margin-top: 10px; font-size: 0.85rem; color: #666; cursor: pointer;
        }
        .item-note-box i { font-size: 0.9rem; }

        /* --- 5. CATATAN UMUM --- */
        .general-note { 
            display: flex; align-items: center; gap: 10px; padding: 15px 0; 
            border-bottom: 1px solid #eee; margin-bottom: 20px; cursor: pointer;
        }
        .general-note span { font-size: 0.9rem; font-weight: 600; color: #555; font-style: italic; }

        /* --- 6. RINCIAN PEMBAYARAN --- */
        .payment-summary {
            background: var(--white); border-radius: 12px; padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03); margin-bottom: 20px;
            border: 1px solid #f0f0f0;
        }
        .summary-title { font-weight: 700; font-size: 1rem; margin-bottom: 15px; text-align: center; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9rem; color: #555; }
        .summary-row.total { 
            border-top: 1px dashed #ddd; padding-top: 15px; margin-top: 10px; margin-bottom: 0; 
            font-weight: 800; font-size: 1.1rem; color: var(--primary-color);
        }

        /* --- 7. STICKY BOTTOM BAR --- */
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
        .btn-pay-now.disabled {
            background: #ccc; pointer-events: none;
        }
        .btn-pay-now:active { opacity: 0.9; }

        /* Empty State */
        .empty-cart { text-align: center; padding: 50px 20px; }
        .empty-cart img { width: 120px; opacity: 0.5; margin-bottom: 20px; }
    </style>
@endsection

@section('content')

{{-- 1. Header --}}
<div class="cart-header">
    <a href="{{ route('menu.index') }}" class="btn-back-header"><i class="fas fa-arrow-left"></i></a>
    <h1 class="header-title">Pesanan</h1>
</div>

<div class="container" style="max-width: 600px;">
    
    @if(isset($finalCartItems) && count($finalCartItems) > 0)

        {{-- ========================================================= --}}
        {{-- LOGIKA HITUNG TOTAL & BIAYA LAYANAN --}}
        {{-- ========================================================= --}}
        @php
            $appFee = $subtotal * 0.007; // 0.7% Biaya Layanan
            $finalTotal = ceil($subtotal + $appFee); // Total Akhir (Bulatkan ke atas)

            // --- LOGIC TIPE PESANAN ---
            $diningOption = session('dining_option', 'dine_in'); // Default: Dine In
            $tableNumber  = session('table_number', null);       
            
            // Teks Label & Icon
            $diningLabel = ($diningOption == 'dine_in') ? 'Makan di Tempat' : 'Bungkus (Take Away)';
            $icon        = ($diningOption == 'dine_in') ? 'fa-utensils' : 'fa-shopping-bag';
            
            // Logic Validasi Dine In
            $isValidOrder = true; 
            if($diningOption == 'dine_in' && empty($tableNumber)){
                $isValidOrder = false; // Dine In tapi gapunya meja = INVALID
            }

            // Logic Tampilan Box
            if(!$isValidOrder) {
                // Warning State
                $boxClass = 'warning';
                $tableLabel = 'Pilih Nomor Meja';
            } else {
                // Success State
                $boxClass = 'branding';
                $tableLabel = $tableNumber ? "Meja No. $tableNumber" : '-';
            }
        @endphp

        {{-- 2. Tipe Pemesanan (Interaktif) --}}
        <div class="order-type-box {{ $boxClass }}" onclick="updateOrderInfo()">
            <div style="display: flex; flex-direction: column;">
                <span style="font-size: 0.75rem; opacity: 0.8;">Tipe Pemesanan <i class="fas fa-edit"></i></span>
                <span style="font-size: 1rem; font-weight: 700;">
                    <i class="fas {{ $icon }}" style="margin-right: 5px;"></i> {{ $diningLabel }}
                </span>
            </div>
            
            {{-- Tampilkan Info Meja hanya jika Dine In --}}
            @if($diningOption == 'dine_in')
                <div style="text-align: right;">
                    <span style="font-size: 0.75rem; opacity: 0.8;">Lokasi</span>
                    <div style="font-weight: 700; font-size: 1rem; text-decoration: underline;">
                        {{ $tableLabel }}
                    </div>
                </div>
            @endif
        </div>

        {{-- Alert Merah jika belum valid --}}
        @if(!$isValidOrder)
            <div style="color: #dc3545; font-size: 0.8rem; margin-top: -15px; margin-bottom: 15px; text-align: center; font-weight: 600;">
                <i class="fas fa-exclamation-circle"></i> Harap pilih nomor meja sebelum lanjut bayar
            </div>
        @endif

        {{-- 3. Menu Terkait (Rekomendasi) --}}
        @if(isset($relatedMenus) && count($relatedMenus) > 0)
        <div class="related-menu-section">
            <h3 class="section-title">Menu Terkait</h3>
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

        {{-- 4. Item Pesanan --}}
        <div class="cart-section-header">
            <span class="section-title">Item yang dipesan ({{ count($finalCartItems) }})</span>
            <a href="{{ route('menu.index') }}" class="btn-add-more">+ Tambah Item</a>
        </div>

        @foreach ($finalCartItems as $item)
            <div class="cart-item-card">
                <div class="item-header">
                    <span class="item-name">{{ $item->menu_name }}</span>
                </div>

                <div class="item-details">
                    @if(!empty($item->addons_list))
                        <div>{{ $item->addons_list }}</div>
                    @else
                        <div style="font-style: italic; color: #999;">Tidak ada topping tambahan</div>
                    @endif
                </div>

                {{-- Catatan Per Item (DENGAN FUNGSI EDIT AKTIF) --}}
                <div class="item-note-box" onclick="editNote({{ $item->id }}, '{{ $item->notes }}')">
                    <i class="far fa-edit"></i> 
                    @if(!empty($item->notes))
                        <span style="color: #333;">{{ Str::limit($item->notes, 30) }}</span>
                    @else
                        <span>Tambahkan catatan...</span>
                    @endif
                </div>

                <div class="item-price-row">
                    <div class="item-price">Rp {{ number_format($item->price_per_unit, 0, ',', '.') }}</div>
                    
                    <div class="qty-wrapper">
                        <div class="qty-btn" onclick="changeQty({{ $item->id }}, {{ $item->quantity - 1 }})"><i class="fas fa-minus"></i></div>
                        <div class="qty-val">{{ $item->quantity }}</div>
                        <div class="qty-btn" onclick="changeQty({{ $item->id }}, {{ $item->quantity + 1 }})"><i class="fas fa-plus"></i></div>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- 5. Catatan Umum --}}
        <div class="general-note">
            <i class="fas fa-edit" style="color: var(--primary-color); font-size: 1.2rem;"></i>
            <span>Tambah catatan lainnya (Opsional)</span>
        </div>

        {{-- 6. Rincian Pembayaran --}}
        <div class="payment-summary">
            <div class="summary-title">Rincian Pembayaran</div>
            <div class="summary-row">
                <span>Subtotal ({{ count($finalCartItems) }} menu)</span>
                <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row">
                <span>Biaya Layanan (0.7%)</span>
                <span>Rp {{ number_format($appFee, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row total">
                <span>Total</span>
                <span>Rp {{ number_format($finalTotal, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- 7. STICKY BOTTOM BAR --}}
        <div class="sticky-bottom-pay">
            <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                <div>
                    <div class="total-pay-label">Total Pembayaran</div>
                    <div class="total-pay-value">Rp {{ number_format($finalTotal, 0, ',', '.') }}</div>
                </div>
            </div>
            
            {{-- Tombol Bayar (Non-aktif jika belum valid) --}}
            @if($isValidOrder)
                <a href="{{ route('checkout.index') }}" class="btn-pay-now">
                    Lanjut Pembayaran
                </a>
            @else
                <button onclick="updateOrderInfo()" class="btn-pay-now" style="background: #999;">
                    Lengkapi Data Meja
                </button>
            @endif
        </div>

    @else
        {{-- EMPTY STATE --}}
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
    // --- 1. LOGIC UPDATE INFO PESANAN (POPUP) ---
    function updateOrderInfo() {
        Swal.fire({
            title: 'Pengaturan Pesanan',
            html: `
                <div style="text-align:left; margin-bottom:15px;">
                    <label style="font-weight:600; display:block; margin-bottom:5px;">Mau makan dimana?</label>
                    <select id="swal-dining-option" class="swal2-input" style="margin:0; width:100%;" onchange="toggleTableInput(this.value)">
                        <option value="dine_in" {{ session('dining_option') == 'dine_in' ? 'selected' : '' }}>Makan di Tempat (Dine In)</option>
                        <option value="take_away" {{ session('dining_option') == 'take_away' ? 'selected' : '' }}>Bungkus (Take Away)</option>
                    </select>
                </div>
                
                <div id="swal-table-wrapper" style="text-align:left; display: {{ session('dining_option', 'dine_in') == 'dine_in' ? 'block' : 'none' }};">
                    <label style="font-weight:600; display:block; margin-bottom:5px;">Nomor Meja</label>
                    <input type="number" id="swal-table-number" class="swal2-input" style="margin:0; width:100%;" placeholder="Contoh: 12" value="{{ session('table_number') }}">
                </div>
            `,
            confirmButtonText: 'Simpan',
            confirmButtonColor: '#B1935B',
            showCancelButton: true,
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const diningOption = document.getElementById('swal-dining-option').value;
                const tableNumber = document.getElementById('swal-table-number').value;
                
                // Validasi Client Side
                if (diningOption === 'dine_in' && !tableNumber) {
                    Swal.showValidationMessage('Mohon isi nomor meja Anda');
                }
                
                return { diningOption, tableNumber };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                saveOrderInfo(result.value.diningOption, result.value.tableNumber);
            }
        });
    }

    // Helper: Show/Hide input meja
    window.toggleTableInput = function(value) {
        document.getElementById('swal-table-wrapper').style.display = (value === 'dine_in') ? 'block' : 'none';
    }

    // Fungsi AJAX Simpan ke Session
    function saveOrderInfo(type, table) {
        fetch('{{ route("cart.saveInfo") }}', { 
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ dining_option: type, table_number: table })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success', 
                    title: 'Berhasil', 
                    text: 'Info pesanan diperbarui', 
                    timer: 1000, 
                    showConfirmButton: false
                }).then(() => location.reload());
            }
        });
    }

    // --- 2. UPDATE QUANTITY ---
    function changeQty(cartId, newQty) {
        if (newQty < 1) {
            Swal.fire({
                title: 'Hapus menu?',
                text: "Menu akan dihapus dari pesanan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) removeItem(cartId);
            });
            return; 
        }

        fetch('{{ route("cart.update") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ cart_id: cartId, quantity: newQty })
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') location.reload();
        });
    }

    // --- 3. REMOVE ITEM ---
    function removeItem(cartId) {
        fetch('{{ route("cart.remove") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ cart_id: cartId })
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') location.reload();
        });
    }

    // --- 4. EDIT NOTE (REAL FUNCTION) ---
    function editNote(cartId, currentNote) {
        Swal.fire({
            title: 'Catatan Pesanan',
            input: 'textarea',
            inputValue: currentNote,
            inputPlaceholder: 'Contoh: Jangan terlalu pedas, kuah pisah...',
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            confirmButtonColor: '#B1935B',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true, // Menampilkan loading saat request ajax
            preConfirm: (newNote) => {
                // Kirim AJAX ke server
                return fetch('{{ route("cart.updateNote") }}', { 
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ cart_id: cartId, note: newNote })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(response.statusText)
                    }
                    return response.json()
                })
                .catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error}`)
                })
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Tersimpan',
                    text: 'Catatan berhasil diperbarui',
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload(); // Refresh halaman agar catatan terupdate
                });
            }
        });
    }
</script>
@endpush