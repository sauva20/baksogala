<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- WAJIB: Link ke manifest agar notif background diizinkan browser --}}
    <link rel="manifest" href="/manifest.json">

    <title>@yield('title', 'Admin Dashboard') - Bakso Gala</title>

    <link rel="stylesheet" href="{{ asset('assets/css/global.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.cdnfonts.com/css/bolton-sans" rel="stylesheet">

    {{-- SweetAlert2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    @yield('styles')

    <style>
        :root {
            --sidebar-width: 260px;
            --primary-bg: #2c3e50;
            --accent-gold: #B1935B;
        }

        body { margin: 0; font-family: 'Bolton', sans-serif; background-color: #f4f6f9; }

        /* --- LAYOUT STRUCTURE --- */
        .admin-wrapper { display: flex; min-height: 100vh; }

        /* SIDEBAR (Desktop) */
        .admin-sidebar {
            width: var(--sidebar-width);
            background-color: var(--primary-bg);
            color: white;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 1050;
        }

        .sidebar-logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .sidebar-logo img { height: 40px; }

        .sidebar-menu { list-style: none; padding: 0; margin: 0; overflow-y: auto; flex-grow: 1; }
        .menu-header { padding: 15px 20px 5px; font-size: 0.7rem; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; font-weight: bold; }
        .menu-item {
            display: flex; align-items: center; padding: 12px 20px; color: #bdc3c7;
            text-decoration: none; border-left: 4px solid transparent; transition: 0.2s;
        }
        .menu-item i { width: 25px; font-size: 1.1rem; }
        .menu-item:hover, .menu-item.active { background: #34495e; color: white; border-left-color: var(--accent-gold); }

        .logout-btn-sidebar {
            width: 100%; border: none; background: rgba(0,0,0,0.2); color: #ecf0f1;
            padding: 15px 20px; text-align: left; cursor: pointer; display: flex; align-items: center; gap: 10px; font-size: 1em; transition: 0.3s;
        }
        .logout-btn-sidebar:hover { background-color: rgba(255,255,255,0.1); color: white; }

        /* MAIN CONTENT */
        .admin-main-content { flex-grow: 1; display: flex; flex-direction: column; min-width: 0; }

        /* HEADER */
        .admin-header {
            height: 70px; background: white; display: flex; align-items: center;
            justify-content: space-between; padding: 0 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            position: sticky; top: 0; z-index: 1000;
        }

        .header-left { display: flex; align-items: center; gap: 15px; }
        #sidebarToggle { background: none; border: none; font-size: 1.3rem; cursor: pointer; color: #555; }

        .admin-content-inner { padding: 25px; flex-grow: 1; }

        /* NOTIF & USER */
        .header-right { display: flex; align-items: center; gap: 20px; }
        .notif-wrapper { position: relative; }
        .btn-notif { font-size: 1.2rem; cursor: pointer; position: relative; color: #555; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; transition: 0.3s; }
        .btn-notif:hover { background-color: #f0f2f5; }
        .badge-dot {
            position: absolute; top: 8px; right: 8px; width: 10px; height: 10px;
            background: #e74c3c; border-radius: 50%; border: 2px solid white;
            animation: pulse-dot 2s infinite;
        }

        .user-profile { display: flex; align-items: center; gap: 10px; }
        .user-info-text { display: flex; flex-direction: column; text-align: right; }
        .user-name-bold { font-weight: 700; color: #333; font-size: 0.9em; }
        .user-role-badge { font-size: 0.75em; color: #888; text-transform: uppercase; }
        .user-avatar {
            width: 40px; height: 40px; background: var(--primary-bg); color: white;
            border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;
        }

        @keyframes pulse-dot { 0% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0.7); } 70% { box-shadow: 0 0 0 5px rgba(231, 76, 60, 0); } 100% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0); } }

        /* --- MOBILE RESPONSIVE (BREAKPOINT 992PX) --- */
        @media (max-width: 992px) {
            .admin-sidebar {
                position: fixed;
                left: -260px; /* Sembunyi ke kiri */
                top: 0;
                bottom: 0;
                transition: 0.3s;
            }
            .admin-sidebar.show { left: 0; }

            .sidebar-overlay {
                display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.5); z-index: 1040;
            }
            .sidebar-overlay.show { display: block; }

            .admin-header { padding: 0 15px; height: 60px; }
            .greeting-text { display: none; } /* Sembunyikan teks sapaan di HP agar tidak sesak */
            .user-info-text { display: none; } /* Sembunyikan nama di HP */

            .notif-dropdown { width: 280px; right: -50px; }
        }

        /* NOTIF DROPDOWN */
        .notif-dropdown {
            position: absolute; top: 55px; right: -10px; width: 320px; background: white;
            border-radius: 12px; box-shadow: 0 5px 25px rgba(0,0,0,0.15); border: 1px solid #eee;
            display: none; overflow: hidden; z-index: 1100;
        }
        .notif-dropdown.show { display: block; }
        .notif-header { background: var(--primary-bg); color: white; padding: 15px; font-size: 0.9rem; }
        .notif-body { max-height: 300px; overflow-y: auto; padding: 10px; }
        .notif-empty { text-align: center; color: #999; padding: 20px; }
        .notif-empty i { font-size: 2rem; margin-bottom: 10px; display: block; }
        .notif-footer { padding: 10px; text-align: center; border-top: 1px solid #eee; }
        .notif-footer a { font-size: 0.8rem; color: var(--accent-gold); font-weight: bold; text-decoration: none; }
    </style>
</head>
<body>

<!-- Overlay untuk klik di luar sidebar mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="admin-wrapper">
    <!-- SIDEBAR -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-logo">
            <img src="{{ asset('assets/images/GALA.png') }}" alt="Gala">
            <h3 style="color: white; margin:0; font-size: 1.2em;">Bakso Gala</h3>
        </div>

        <ul class="sidebar-menu">
            @if(Auth::user()->role == 'owner')
                <li><a href="{{ route('admin.dashboard') }}" class="menu-item {{ Route::is('admin.dashboard') ? 'active' : '' }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            @endif

            <li class="menu-header">OPERASIONAL</li>
            <li><a href="{{ route('admin.orders.index') }}" class="menu-item {{ Route::is('admin.orders*') ? 'active' : '' }}"><i class="fas fa-receipt"></i> Pesanan</a></li>
            <li><a href="{{ route('admin.menu.index') }}" class="menu-item {{ Route::is('admin.menu*') ? 'active' : '' }}"><i class="fas fa-utensils"></i> Manajemen Menu</a></li>
            <li><a href="{{ route('admin.reviews.index') }}" class="menu-item {{ Route::is('admin.reviews*') ? 'active' : '' }}"><i class="fas fa-star"></i> Manajemen Review</a></li>

            @if(Auth::user()->role == 'owner')
                <li class="menu-header">ADMINISTRASI</li>
                <li><a href="{{ route('admin.promotions.index') }}" class="menu-item {{ Route::is('admin.promotions*') ? 'active' : '' }}"><i class="fas fa-tags"></i> Diskon & Voucher</a></li>
                <li><a href="{{ route('admin.reports.index') }}" class="menu-item {{ Route::is('admin.reports*') ? 'active' : '' }}"><i class="fas fa-chart-line"></i> Laporan</a></li>
                <li><a href="{{ route('admin.users.index') }}" class="menu-item {{ Route::is('admin.users*') ? 'active' : '' }}"><i class="fas fa-users"></i> Data Pelanggan</a></li>
            @endif
        </ul>

        <form action="{{ route('admin.logout') }}" method="POST" style="margin-top: auto;">
            @csrf
            <button type="submit" class="logout-btn-sidebar"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </form>
    </aside>

    <!-- MAIN -->
    <div class="admin-main-content">
        <header class="admin-header">
            <div class="header-left">
                <button id="sidebarToggle"><i class="fas fa-bars"></i></button>
                <span class="greeting-text">Halo, <b>{{ Auth::user()->name }}</b>! 👋</span>
            </div>

            <div class="header-right">
                <div class="notif-wrapper">
                    <div class="btn-notif" id="notifBtn">
                        <i class="fas fa-bell"></i>
                        <span class="badge-dot" id="navBadge" style="display:none;"></span>
                    </div>
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header"><h5>Pesanan Masuk</h5></div>
                        <div class="notif-body" id="notifBody">
                            <div class="notif-empty">
                                <i class="fas fa-bell-slash"></i>
                                <p>Tidak ada notifikasi baru.</p>
                            </div>
                        </div>
                        <div class="notif-footer"><a href="{{ route('admin.orders.index') }}">Lihat Semua Pesanan</a></div>
                    </div>
                </div>

                <div class="user-profile">
                    <div class="user-info-text">
                        <span class="user-name-bold">{{ Auth::user()->name }}</span>
                        <span class="user-role-badge">{{ ucfirst(Auth::user()->role) }}</span>
                    </div>
                    <div class="user-avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
                </div>
            </div>
        </header>

        <main class="admin-content-inner">
            @yield('content')
        </main>
    </div>
</div>

<audio id="alarmSound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto" loop></audio>

{{-- SweetAlert JS --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // --- 1. TOGGLE SIDEBAR & OVERLAY ---
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggle');

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
    });

    // --- 2. NOTIF DROPDOWN ---
    const notifBtn = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');

    notifBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        notifDropdown.classList.toggle('show');
    });

    document.addEventListener('click', () => {
        notifDropdown.classList.remove('show');
    });

    // --- 3. GLOBALS & SYNC ---
    let lastGlobalId = {{ \DB::table('orders')->max('id') ?? 0 }};
    let isAlertOpen = false;
    const alarmAudio = document.getElementById('alarmSound');

    // --- 4. AUDIO UNLOCK ---
    document.body.addEventListener('click', function() {
        if(alarmAudio.paused) {
            alarmAudio.muted = true;
            alarmAudio.play().then(() => { alarmAudio.pause(); alarmAudio.muted = false; });
        }
    }, { once: true });

    // --- 5. SHARED ALERT LOGIC ---
    function triggerOrderAlert(id, title, message, type) {
        if(isAlertOpen || id <= lastGlobalId) return;

        lastGlobalId = id;
        isAlertOpen = true;

        if(alarmAudio) {
            alarmAudio.currentTime = 0;
            alarmAudio.play().catch(e => console.log("Audio block:", e));
        }
        document.getElementById('navBadge').style.display = 'block';

        Swal.fire({
            title: title || '🔔 PESANAN BARU!',
            text: message || 'Segera cek dapur, ada pesanan masuk!',
            icon: type || 'info',
            showCancelButton: true,
            confirmButtonText: '🔊 MATIKAN ALARM & LIHAT',
            confirmButtonColor: '#B1935B',
            allowOutsideClick: false,
            backdrop: `rgba(0,0,0,0.8)`
        }).then((res) => {
            if(alarmAudio) { alarmAudio.pause(); alarmAudio.currentTime = 0; }
            isAlertOpen = false;
            if(res.isConfirmed) window.location.href = "{{ route('admin.orders.index') }}";
        });
    }

    // --- 6. POLLING SCRIPT ---
    function checkGlobalOrders() {
        if(isAlertOpen) return;
        fetch('{{ route("admin.orders.checkNew") }}?last_id=' + lastGlobalId)
            .then(res => res.json())
            .then(data => {
                if (data.has_new && data.latest_id > lastGlobalId) {
                    triggerOrderAlert(data.latest_id, data.title, data.message, data.type);
                }
            }).catch(e => {});
    }
    setInterval(checkGlobalOrders, 5000);

    // --- 7. REGISTRASI SERVICE WORKER ---
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/firebase-messaging-sw.js')
        .then(reg => console.log('SW Registered:', reg.scope))
        .catch(err => console.log('SW Error:', err));
    }
</script>

{{-- FIREBASE MODULE (SDK 12.9.0) --}}
<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/12.9.0/firebase-app.js";
    import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/12.9.0/firebase-messaging.js";

    const firebaseConfig = {
        apiKey: "AIzaSyDmAom7VDb0OkTijt0Hf5UE3YB1kuNvywA",
        authDomain: "pondasikita-465612.firebaseapp.com",
        projectId: "pondasikita-465612",
        storageBucket: "pondasikita-465612.firebasestorage.app",
        messagingSenderId: "92626258010",
        appId: "1:92626258010:web:35b5aedc63783dd6387063",
    };

    const app = initializeApp(firebaseConfig);
    const messaging = getMessaging(app);

    // Save Token
    Notification.requestPermission().then((permission) => {
        if (permission === 'granted') {
            getToken(messaging, { vapidKey: 'BKKkRu1AiCDLOEndKleGE3P0yQunprYaUppLGulYJJmbiy3NupZ6RrMxI4fX8HfLnb-Opy7hcH-ObnXi0YDCT9c' }).then((token) => {
                if (token) {
                    fetch("{{ route('update.fcm-token') }}", {
                        method: "POST",
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ token: token })
                    });
                }
            });
        }
    });

    // Listener Foreground
    onMessage(messaging, (payload) => {
        console.log('Firebase incoming:', payload);
        const orderId = payload.data ? parseInt(payload.data.order_id) : lastGlobalId + 1;
        triggerOrderAlert(orderId, payload.notification.title, payload.notification.body, 'success');
    });
</script>

@stack('scripts')
</body>
</html>
