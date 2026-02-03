{{-- File: resources/views/partials/navbar.blade.php --}}

<header class="main-header">
    <div class="container navbar-container">
        {{-- 1. LOGO --}}
        <div class="logo">
            <a href="{{ route('home') }}">
                <img src="{{ asset('assets/images/GALA.png') }}" alt="Logo Bakso Gala">
                <span class="logo-text">Bakso Cap Gala</span>
            </a>
        </div>

        {{-- 2. NAVIGASI --}}
        <nav class="main-nav" id="mainNav">
            <ul>
                <li><a href="{{ route('menu.index') }}" class="{{ request()->routeIs('menu.index') ? 'active' : '' }}">Menu</a></li>
                <li><a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">Tentang Kami</a></li>
                
                {{-- 3. ICON KERANJANG --}}
                <li>
                    <a href="{{ route('cart.index') }}" class="nav-cart-icon" style="position: relative; display: inline-block;">
                        <i class="fas fa-shopping-cart"></i>
                        <span id="cart-badge" class="cart-count" style="{{ (isset($cartCount) && $cartCount > 0) ? 'display: flex;' : 'display: none;' }}">
                            {{ $cartCount ?? 0 }}
                        </span>
                    </a>
                </li>

                {{-- 4. CEK STATUS LOGIN --}}
                @auth
                    {{-- Jika User SUDAH Login --}}
                    <li class="user-greeting">
                        <span>Halo, {{ explode(' ', trim(Auth::user()->name))[0] }}</span>
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="nav-btn-logout">Logout</button>
                        </form>
                    </li>
                @else
                    {{-- Jika User BELUM Login (Tamu) --}}
                    {{-- PERBAIKAN DISINI: Ganti route('auth.index') menjadi route('login') --}}
                    <li><a href="{{ route('login') }}" class="nav-btn-login">Login</a></li>
                @endauth
            </ul>
        </nav>

        {{-- HAMBURGER MENU (MOBILE) --}}
        <button class="hamburger-menu" id="hamburgerMenu">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</header>

<script>
    // Toggle menu mobile
    const hamburger = document.getElementById('hamburgerMenu');
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            document.getElementById('mainNav').classList.toggle('active');
        });
    }

    // Fungsi Global untuk Update Badge
    window.updateCartBadge = function(count) {
        const badge = document.getElementById('cart-badge');
        if (badge) {
            badge.innerText = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        }
    };
</script>

<style>
    .nav-cart-icon {
        position: relative;
        color: #333;
        font-size: 1.2rem;
        text-decoration: none;
    }
    .cart-count {
        position: absolute;
        top: -8px;
        right: -10px;
        background-color: #d32f2f;
        color: white;
        font-size: 0.75rem;
        font-weight: bold;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        z-index: 10;
    }
    .nav-btn-logout {
        background: none;
        border: 1px solid #d32f2f;
        color: #d32f2f;
        cursor: pointer;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.9em;
        transition: 0.3s;
    }
    .nav-btn-logout:hover {
        background: #d32f2f;
        color: white;
    }
</style>