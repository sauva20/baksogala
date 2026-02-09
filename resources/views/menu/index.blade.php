@extends('layouts.app')

@section('title', 'Menu - Bakso Gala')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/menu.css') }}">
    <link rel="icon" href="{{ asset('assets/images/gala.png') }}" type="image/png">
    <style>
        /* --- GLOBAL VARIABLES --- */
        :root {
            --primary-color: #B1935B;  
            --secondary-color: #2F3D65; 
            --app-bg: #f8f9fa; 
            --nav-bg: #ffffff;
        }

        /* --- HAPUS FOOTER KHUSUS HALAMAN INI --- */
        footer, .footer-section, .site-footer { 
            display: none !important; 
        }

        /* Hide elements on Desktop */
        .btn-mobile-add, .sticky-cart-bar, .modal-bottom-bar-mobile, .mobile-category-nav, .mobile-table-banner, .mobile-header { display: none; }

        /* --- ESTETIK MODAL ADDONS (CHIPS STYLE) --- */
        .modal-addons, .modal-variants { 
            margin: 20px 0; 
            padding: 0; 
            background: transparent; 
            border: none; 
        }
        
        .modal-addons h4, .modal-variants h4 {
            font-size: 0.95rem; 
            font-weight: 700; 
            color: #555; 
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .addons-grid, .variants-grid { 
            display: flex; 
            flex-wrap: wrap; 
            gap: 10px; 
        }

        /* --- STYLE ITEM PILIHAN (Addon & Variant) --- */
        .addon-item, .variant-item { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding: 10px 15px; 
            background: #fff; 
            border: 1px solid #e0e0e0; 
            border-radius: 50px; 
            cursor: pointer; 
            transition: all 0.2s ease;
            user-select: none;
            flex-grow: 1; /* Biar rata lebar */
            min-width: 45%; 
            position: relative;
        }

        /* Hover Effect */
        .addon-item:hover, .variant-item:hover {
            border-color: var(--primary-color);
            background: #fffbf0;
        }

        /* Selected State (Checked) */
        .addon-item:has(input:checked), .variant-item:has(input:checked) {
            background-color: #fff8e1; 
            border-color: var(--primary-color);
            color: var(--primary-color);
            box-shadow: 0 2px 5px rgba(177, 147, 91, 0.2);
            font-weight: 700;
        }

        .addon-item span.name, .variant-item span.name {
            font-size: 0.9rem;
            margin-left: 5px;
        }

        .addon-item span.price {
            font-size: 0.8rem;
            font-weight: 700;
            color: #888;
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: auto;
        }
        
        .addon-item:has(input:checked) span.price {
            background: rgba(177, 147, 91, 0.2);
            color: var(--primary-color);
        }

        /* Sembunyikan Input Asli */
        .addon-checkbox, .variant-radio {
            appearance: none;
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            border: 2px solid #ccc;
            border-radius: 50%;
            margin-right: 8px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
        }

        /* Radio butuh border radius beda? Tidak, bulat juga bagus */
        
        .addon-checkbox:checked, .variant-radio:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Tanda Centang / Dot */
        .addon-checkbox:checked::after {
            content: '✔';
            font-size: 10px;
            color: white;
            position: absolute;
        }
        .variant-radio:checked::after {
            content: '';
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            position: absolute;
        }

        /* --- WAJIB PILIH ALERT --- */
        .variant-error {
            color: #d32f2f;
            font-size: 0.85rem;
            margin-top: 5px;
            display: none;
            font-weight: 600;
        }

        @media (min-width: 769px) {
            .addon-item, .variant-item {
                min-width: auto; 
                width: auto;
                padding-right: 15px;
            }
        }

        /* --- ESTETIK TEXTAREA CATATAN --- */
        .modal-notes-section {
            margin-top: 25px;
        }

        .modal-notes-section label {
            font-size: 0.9rem;
            font-weight: 700;
            color: #555;
            display: block;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .modal-notes-section textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid #e0e0e0;
            background-color: #f8f9fa; 
            border-radius: 12px; 
            font-family: inherit;
            font-size: 0.95rem;
            color: #333;
            transition: all 0.3s ease;
            resize: none; 
            min-height: 80px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }

        .modal-notes-section textarea:focus {
            background-color: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(177, 147, 91, 0.1); 
            outline: none;
        }

        /* --- BACK TO TOP BUTTON --- */
        .btn-back-to-top {
            position: fixed; bottom: 30px; right: 30px; width: 50px; height: 50px;
            background-color: var(--secondary-color); color: white; border: none; border-radius: 50%;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3); cursor: pointer; z-index: 890;
            opacity: 0; visibility: hidden; transform: translateY(20px); transition: all 0.3s ease;
            display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
        }
        .btn-back-to-top:hover { background-color: var(--primary-color); transform: translateY(-3px); }
        .btn-back-to-top.show { opacity: 1; visibility: visible; transform: translateY(0); }

        /* --- TAMPILAN MOBILE --- */
        @media (max-width: 768px) {
            body { background-color: var(--app-bg); padding-bottom: 100px; padding-top: 0; }
            .menu-hero { display: none; } 
            
            /* Header & Nav */
            .mobile-header { display: block; background: white; padding: 15px 20px; position: sticky; top: 0; z-index: 1000; box-shadow: 0 1px 5px rgba(0,0,0,0.05); }
            .brand-title { font-size: 1.4rem; font-weight: 800; color: var(--secondary-color); margin: 0; }
            .brand-subtitle { font-size: 0.8rem; color: #666; margin: 0;}
            
            .mobile-table-banner { display: flex; justify-content: space-between; align-items: center; background: #fff8e1; color: #f57f17; padding: 10px 20px; border-bottom: 1px solid #ffe0b2; font-size: 0.9rem; font-weight: 700; }
            
            .mobile-category-nav { display: flex; overflow-x: auto; white-space: nowrap; background: white; padding: 10px 15px; position: sticky; top: 73px; z-index: 990; border-bottom: 1px solid #eee; gap: 10px; -ms-overflow-style: none; scrollbar-width: none; }
            .mobile-category-nav::-webkit-scrollbar { display: none; }
            
            .cat-pill { display: inline-block; padding: 8px 16px; border-radius: 20px; background: #f1f3f5; color: #555; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: 0.2s; border: 1px solid transparent; }
            .cat-pill.active { background: var(--primary-color); color: white; box-shadow: 0 2px 8px rgba(177, 147, 91, 0.4); }

            /* Cards */
            .menu-container { padding-top: 15px; }
            .menu-section { scroll-margin-top: 140px; }
            .menu-section h2 { font-size: 1.1rem; font-weight: 800; color: #333; margin: 0 0 15px 10px; border-left: 4px solid var(--primary-color); padding-left: 8px; }
            .menu-cards-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; padding: 0 10px; }
            .menu-card { background: white; border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.05); display: flex; flex-direction: column; height: 100%; }
            .card-image-placeholder { height: 130px; width: 100%; background-size: cover; background-position: center; }
            .card-content { padding: 10px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
            .card-content h3 { font-size: 0.9rem; margin: 0 0 5px 0; font-weight: 700; line-height: 1.3; min-height: 2.4em; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; color: var(--secondary-color); }
            .card-content p { display: none; } 
            .card-content .price { font-size: 0.95rem; font-weight: 800; color: #444; margin-bottom: 8px; display: block; }
            .btn-mobile-add { width: 100%; padding: 6px 0; background: white; color: var(--primary-color); border: 1px solid var(--primary-color); border-radius: 50px; font-size: 0.8rem; font-weight: 700; text-align: center; cursor: pointer; }
            .btn-mobile-add:active { background: var(--primary-color); color: white; }

            /* Sticky Cart */
            .sticky-cart-bar { display: none; position: fixed; bottom: 15px; left: 15px; right: 15px; background: var(--secondary-color); color: white; padding: 12px 15px; border-radius: 12px; box-shadow: 0 8px 20px rgba(47, 61, 101, 0.4); z-index: 900; align-items: center; justify-content: space-between; animation: slideUp 0.3s; }
            .sticky-cart-bar.show { display: flex; }
            .cart-badge { background: var(--primary-color); color: white; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: bold; position: absolute; top: -8px; right: -8px; border: 2px solid var(--secondary-color);}

            /* Modal */
            .modal { align-items: flex-end; z-index: 9999 !important; }
            .modal-content { width: 100%; height: 100%; border-radius: 0; position: fixed; top: 0; left: 0; background: white; display: flex; flex-direction: column; z-index: 10000; }
            .modal-body { overflow-y: auto; padding-bottom: 120px; flex-grow: 1; display: block; }
            .modal-image-container img { width: 100%; height: 250px; object-fit: cover; }
            .modal-details { padding: 20px; }
            .modal-bottom-bar-mobile { display: flex; position: fixed; bottom: 0; left: 0; width: 100%; background: white; padding: 15px 20px; box-shadow: 0 -4px 20px rgba(0,0,0,0.05); z-index: 10001; align-items: center; gap: 15px; }
            .qty-wrapper { display: flex; align-items: center; gap: 12px; }
            .qty-btn { width: 32px; height: 32px; border-radius: 50%; border: 1px solid #ddd; display: flex; align-items: center; justify-content: center; font-size: 1rem; color: var(--secondary-color); }
            .qty-val { font-weight: 800; font-size: 1.1rem; min-width: 20px; text-align: center; }
            .btn-add-final { flex-grow: 1; background: var(--primary-color); color: white; border: none; border-radius: 12px; padding: 12px; font-weight: 700; font-size: 1rem; display: flex; justify-content: space-between; transition: all 0.3s; }
            .btn-add-final:disabled { background: #ccc; cursor: not-allowed; opacity: 0.7; }
            .close-modal-btn { top: 15px; right: 15px; background: rgba(0,0,0,0.3); color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px); z-index: 10002; font-size: 1.2rem; position: absolute; border:none; }
            
            .quantity-control-desktop, .add-btn-desktop { display: none; }
            .btn-back-to-top { bottom: 85px; right: 20px; width: 45px; height: 45px; }
        }
        @keyframes slideUp { from { transform: translateY(100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
@endsection

@section('content')

{{-- 1. Mobile Header & Table Info --}}
<div class="mobile-header d-md-none">
    <h1 class="brand-title">Bakso Gala</h1>
    <p class="brand-subtitle">Nikmati bakso & mie terbaik.</p>
</div>

@if($nomorMeja)
<div class="mobile-table-banner d-md-none">
    <span><i class="fas fa-utensils"></i> Dine In</span>
    <span>Meja No. {{ $nomorMeja }}</span>
</div>
@else
<div class="mobile-table-banner d-md-none" style="background: #e3f2fd; color: #1565c0; border-bottom-color: #bbdefb;">
    <span><i class="fas fa-shopping-bag"></i> Take Away</span>
    <span>(Tanpa Meja)</span>
</div>
@endif

{{-- 2. Category Navigation --}}
<div class="mobile-category-nav d-md-none" id="categoryNav">
    @foreach ($menuGrouped as $category => $items)
        <a href="#{{ Str::slug($category) }}" class="cat-pill">{{ $category }}</a>
    @endforeach
</div>

{{-- Desktop Hero --}}
<header class="menu-hero d-none d-md-block" style="background: var(--primary-color); padding: 60px 0; color: white; text-align: center;">
    <div class="container">
        <h1>Menu <strong>Bakso Gala</strong></h1>
    </div>
</header>

{{-- 3. Menu List --}}
<div class="container menu-container">
    @foreach ($menuGrouped as $category => $items)
        <section class="menu-section" id="{{ Str::slug($category) }}">
            <h2>{{ $category }}</h2>
            <div class="menu-cards-grid">
                @foreach ($items as $item)
                    {{-- Tambahkan data-category --}}
                    <div class="menu-card"
                         onclick="openModal(this)"
                         data-id="{{ $item->id }}"
                         data-name="{{ $item->name }}"
                         data-price="{{ $item->price }}"
                         data-description="{{ $item->description }}"
                         data-image="{{ $item->image_url }}"
                         data-category="{{ $category }}"> 
                        
                        <div class="card-image-placeholder" style="background-image: url('{{ asset($item->image_url) }}');"></div>
                        
                        <div class="card-content">
                            <h3>{{ $item->name }} @if($item->is_favorite) <i class="fas fa-thumbs-up" style="color: #ffc107; font-size: 0.8em;"></i> @endif</h3>
                            <p>{{ Str::limit($item->description, 60) }}</p>
                            <span class="price">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                            <div class="btn-mobile-add">Tambah</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach
</div>

{{-- 4. Sticky Cart Bar --}}
<div id="stickyCart" class="sticky-cart-bar" onclick="window.location.href='{{ route('cart.index') }}'">
    <div style="display: flex; align-items: center; gap: 10px;">
        <div style="position: relative;">
            <i class="fas fa-shopping-basket" style="font-size: 1.4rem;"></i>
            <div class="cart-badge" id="stickyQty">0</div>
        </div>
        <div style="display: flex; flex-direction: column; line-height: 1.2;">
            <span style="font-size: 0.7rem; opacity: 0.8;">Total Estimasi</span>
            <span style="font-weight: 800; font-size: 1rem;" id="stickyTotal">Rp 0</span>
        </div>
    </div>
    <div style="font-weight: 700; font-size: 0.9rem;">
        CHECKOUT <i class="fas fa-arrow-right"></i>
    </div>
</div>

<button id="backToTop" class="btn-back-to-top" title="Kembali ke atas"><i class="fas fa-arrow-up"></i></button>

{{-- 6. Modal Detail (ESTETIK BARU) --}}
<div class="modal" id="menuDetailModal">
    <div class="modal-content">
        <button class="close-modal-btn" onclick="closeModal()">&times;</button>
        
        <div class="modal-body">
            <div class="modal-image-container">
                <img src="" alt="Gambar Menu" id="modal-image"> 
            </div>

            <div class="modal-details">
                <h3 id="modal-title" style="font-size: 1.5rem; font-weight:800; color: var(--secondary-color); margin-bottom:5px;">Nama Menu</h3>
                <p id="modal-description" style="color:#666; line-height:1.5;">Deskripsi...</p>
                <div class="modal-price" style="font-size:1.3rem; font-weight:800; color: var(--primary-color); margin-bottom:20px;">
                    <span id="modal-price-value">Rp 0</span>
                </div>
                
                {{-- AREA PILIHAN VARIAN (MANIS/ASIN) - KHUSUS BAKMIE --}}
                <div id="variant-wrapper" style="display: none;">
                    <div class="modal-variants">
                        <h4>Pilih Rasa (Wajib) <span style="color:red">*</span></h4>
                        <div class="variants-grid">
                            <label class="variant-item">
                                <input type="radio" name="noodle_flavor" class="variant-radio" value="Asin" onchange="checkVariant()">
                                <span class="name">Asin</span>
                            </label>
                            <label class="variant-item">
                                <input type="radio" name="noodle_flavor" class="variant-radio" value="Manis" onchange="checkVariant()">
                                <span class="name">Manis</span>
                            </label>
                        </div>
                        <div id="variant-error" class="variant-error">Silakan pilih rasa terlebih dahulu!</div>
                    </div>
                </div>

                {{-- AREA TAMBAH TOPPING (CHIPS STYLE) --}}
                <div id="addons-wrapper">
                    @if(isset($sideDishes) && count($sideDishes) > 0)
                    <div class="modal-addons">
                        <h4>Pilih Tambahan (Opsional)</h4>
                        <div class="addons-grid">
                            @foreach($sideDishes as $addon)
                            <label class="addon-item">
                                <input type="checkbox" class="addon-checkbox" value="{{ $addon->id }}" data-price="{{ $addon->price }}" onchange="updateTotalPrice()">
                                <span class="name">{{ $addon->name }}</span>
                                <span class="price">+{{ number_format($addon->price / 1000, 0) }}k</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Catatan --}}
                <div class="modal-notes-section">
                    <label for="item-notes">Catatan Khusus (Opsional)</label>
                    <textarea id="item-notes" rows="3" placeholder="Contoh: Jangan pakai daun bawang, sambal dipisah..."></textarea>
                </div>

                {{-- Desktop Qty --}}
                <div class="quantity-control quantity-control-desktop" style="margin-top: 20px;">
                    <button type="button" onclick="updateQty(-1)">-</button>
                    <input type="number" id="qty-input-desktop" value="1" min="1" readonly style="width: 50px; text-align: center;">
                    <button type="button" onclick="updateQty(1)">+</button>
                </div>
            </div>
        </div>

        {{-- Desktop Button --}}
        <button id="add-btn-desktop" class="add-to-cart-btn-modal add-btn-desktop" onclick="addToCart()" style="margin: 20px;">Tambah ke Keranjang</button>

        {{-- Mobile Sticky Bottom Bar --}}
        <div class="modal-bottom-bar-mobile">
            <div class="qty-wrapper">
                <div class="qty-btn" onclick="updateQty(-1)"><i class="fas fa-minus"></i></div>
                <div class="qty-val" id="qty-val-display">1</div>
                <div class="qty-btn" onclick="updateQty(1)"><i class="fas fa-plus"></i></div>
            </div>
            <button id="add-btn-mobile" class="btn-add-final" onclick="addToCart()">
                <span>Tambah Pesanan</span>
                <span id="btn-total-price">Rp 0</span>
            </button>
        </div>
    </div>
</div>

<input type="hidden" id="real-qty-input" value="1">

@endsection
@push('scripts')
<script>
    let currentMenuItem = {};
    let globalCartQty = {{ $currentQty ?? 0 }}; 
    let globalCartTotal = {{ $currentTotal ?? 0 }}; 
    let isVariantRequired = false;

    document.addEventListener("DOMContentLoaded", function() {
        updateStickyCartUI(globalCartQty, globalCartTotal);
    });

    // --- SCROLL & BACK TO TOP ---
    const sections = document.querySelectorAll('.menu-section');
    const navLinks = document.querySelectorAll('.cat-pill');
    const navContainer = document.getElementById('categoryNav');
    const backToTopBtn = document.getElementById('backToTop');

    window.addEventListener('scroll', () => {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            if (pageYOffset >= sectionTop - 180) { current = section.getAttribute('id'); }
        });
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href').includes(current)) {
                link.classList.add('active');
                const linkRect = link.getBoundingClientRect();
                const containerRect = navContainer.getBoundingClientRect();
                if (linkRect.left < 0 || linkRect.right > containerRect.width) {
                    link.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }
            }
        });
        if (window.scrollY > 300) { backToTopBtn.classList.add('show'); } else { backToTopBtn.classList.remove('show'); }
    });

    backToTopBtn.addEventListener('click', () => { window.scrollTo({ top: 0, behavior: 'smooth' }); });

    // --- OPEN MODAL ---
    function openModal(card) {
        document.getElementById('stickyCart').classList.remove('show'); 
        
        const modal = document.getElementById('menuDetailModal');
        currentMenuItem = {
            id: card.dataset.id,
            name: card.dataset.name,
            price: parseInt(card.dataset.price),
            description: card.dataset.description,
            image: card.dataset.image,
            category: card.dataset.category 
        };

        document.getElementById('modal-image').src = `{{ asset('') }}${currentMenuItem.image}`;
        document.getElementById('modal-title').innerText = currentMenuItem.name;
        document.getElementById('modal-description').innerText = currentMenuItem.description;
        
        const cat = currentMenuItem.category.toLowerCase().trim();
        const addonsWrapper = document.getElementById('addons-wrapper');
        const variantWrapper = document.getElementById('variant-wrapper');
        
        // 1. Logic Hide Addons (Sama seperti sebelumnya)
        const excludedCategories = ['minuman', 'drink', 'beverage', 'tambahan', 'side dish', 'topping'];
        const isExcluded = excludedCategories.some(keyword => cat.includes(keyword));
        
        if (isExcluded) {
            addonsWrapper.style.display = 'none';
        } else {
            addonsWrapper.style.display = 'block';
        }

        // 2. Logic Variant Wajib (Bakmie/Mie)
        // Reset Radio Button
        const radios = document.querySelectorAll('input[name="noodle_flavor"]');
        radios.forEach(r => r.checked = false);
        document.getElementById('variant-error').style.display = 'none';

        // Cek apakah ini Bakmie?
        if (cat.includes('mie') || cat.includes('bakmie')) {
            variantWrapper.style.display = 'block';
            isVariantRequired = true;
            toggleAddButton(false); // Matikan tombol dulu
        } else {
            variantWrapper.style.display = 'none';
            isVariantRequired = false;
            toggleAddButton(true); // Hidupkan tombol
        }

        setQty(1);
        document.getElementById('item-notes').value = '';
        document.querySelectorAll('.addon-checkbox').forEach(cb => cb.checked = false);

        updateTotalPrice();
        modal.style.display = 'flex';
    }

    function checkVariant() {
        if (isVariantRequired) {
            const selected = document.querySelector('input[name="noodle_flavor"]:checked');
            if (selected) {
                document.getElementById('variant-error').style.display = 'none';
                toggleAddButton(true);
            } else {
                toggleAddButton(false);
            }
        }
    }

    function toggleAddButton(enable) {
        const btnDesktop = document.getElementById('add-btn-desktop');
        const btnMobile = document.getElementById('add-btn-mobile');
        
        if (enable) {
            btnDesktop.disabled = false;
            btnDesktop.style.opacity = '1';
            btnDesktop.style.cursor = 'pointer';
            
            btnMobile.disabled = false;
            btnMobile.style.opacity = '1';
            btnMobile.style.cursor = 'pointer';
        } else {
            btnDesktop.disabled = true;
            btnDesktop.style.opacity = '0.5';
            btnDesktop.style.cursor = 'not-allowed';
            
            btnMobile.disabled = true;
            btnMobile.style.opacity = '0.5';
            btnMobile.style.cursor = 'not-allowed';
        }
    }

    function closeModal() {
        document.getElementById('menuDetailModal').style.display = 'none';
        updateStickyCartUI(globalCartQty, globalCartTotal);
    }

    window.onclick = function(e) { if (e.target == document.getElementById('menuDetailModal')) closeModal(); }

    function setQty(val) {
        let newVal = val < 1 ? 1 : val;
        document.getElementById('real-qty-input').value = newVal;
        document.getElementById('qty-val-display').innerText = newVal;
        if(document.getElementById('qty-input-desktop')) document.getElementById('qty-input-desktop').value = newVal;
    }

    function updateQty(change) {
        let current = parseInt(document.getElementById('real-qty-input').value);
        let next = current + change;
        if(next >= 1) { setQty(next); updateTotalPrice(); }
    }

    function updateTotalPrice() {
        let base = currentMenuItem.price;
        let addon = 0;
        let qty = parseInt(document.getElementById('real-qty-input').value);
        
        const addonsWrapper = document.getElementById('addons-wrapper');
        if (addonsWrapper.style.display !== 'none') {
            document.querySelectorAll('.addon-checkbox:checked').forEach(cb => addon += parseInt(cb.dataset.price));
        }
        
        let unit = base + addon;
        let total = unit * qty;

        document.getElementById('modal-price-value').innerText = 'Rp ' + unit.toLocaleString('id-ID');
        document.getElementById('btn-total-price').innerText = 'Rp ' + total.toLocaleString('id-ID');
    }

    function updateStickyCartUI(qty, total) {
        const bar = document.getElementById('stickyCart');
        if (qty > 0) {
            bar.classList.add('show');
            document.getElementById('stickyQty').innerText = qty;
            document.getElementById('stickyTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
        } else {
            bar.classList.remove('show');
        }
    }

    function addToCart() {
        // Cek validasi akhir
        if (isVariantRequired) {
            const selectedVariant = document.querySelector('input[name="noodle_flavor"]:checked');
            if (!selectedVariant) {
                document.getElementById('variant-error').style.display = 'block';
                return; // Stop process
            }
        }

        const quantity = parseInt(document.getElementById('real-qty-input').value);
        let notes = document.getElementById('item-notes').value;
        let selectedAddons = [];
        let addonsPrice = 0;
        
        // Ambil Addons
        const addonsWrapper = document.getElementById('addons-wrapper');
        if (addonsWrapper.style.display !== 'none') {
            document.querySelectorAll('.addon-checkbox:checked').forEach((cb) => {
                selectedAddons.push(cb.value);
                addonsPrice += parseInt(cb.dataset.price);
            });
        }

        // Ambil Varian Rasa (Gabungkan ke Notes agar simple)
        if (isVariantRequired) {
            const flavor = document.querySelector('input[name="noodle_flavor"]:checked').value;
            notes = `[Rasa: ${flavor}] ` + notes;
        }
        
        let itemTotal = (currentMenuItem.price + addonsPrice) * quantity;
        
        const btn = document.getElementById('add-btn-mobile');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span>Menyimpan...</span>'; 
        btn.disabled = true;

        fetch('{{ route("cart.add") }}', { 
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ 
                menu_id: currentMenuItem.id, 
                quantity: quantity, 
                addons: selectedAddons, 
                notes: notes 
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                closeModal();
                globalCartQty += quantity;
                globalCartTotal += itemTotal;
                updateStickyCartUI(globalCartQty, globalCartTotal);
            } else { 
                alert('Gagal: ' + data.message); 
            }
        })
        .catch(err => alert('Error: ' + err))
        .finally(() => { 
            btn.innerHTML = originalHtml; 
            btn.disabled = false; 
        });
    }
</script>
@endpush