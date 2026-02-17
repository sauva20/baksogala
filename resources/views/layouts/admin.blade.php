<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- 1. WAJIB: MANIFEST UNTUK NOTIFIKASI BACKGROUND --}}
    <link rel="manifest" href="/manifest.json">
    
    <title>@yield('title', 'Admin Dashboard') - Bakso Gala</title>
    
    {{-- CSS Admin --}}
    <link rel="stylesheet" href="{{ asset('assets/css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.cdnfonts.com/css/bolton-sans" rel="stylesheet">
    
    {{-- SweetAlert2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    @yield('styles')

    <style>
        /* --- CSS STABILIZATION --- */
        .admin-wrapper { display: flex; min-height: 100vh; width: 100%; overflow-x: hidden; }
        .admin-sidebar { width: 260px; min-width: 260px; flex-shrink: 0; background-color: #2c3e50; color: white; min-height: 100vh; display: flex; flex-direction: column; transition: 0.3s; z-index: 1050; }
        .sidebar-logo { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); position: relative; }
        .sidebar-logo img { max-width: 60px; height: auto; display: block; margin: 0 auto 10px auto; }
        .admin-main-content { flex-grow: 1; width: calc(100% - 260px); background-color: #f4f6f9; display: flex; flex-direction: column; transition: 0.3s; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; height: 70px; padding: 0 30px; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 1000; }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .admin-content-inner { flex-grow: 1; padding: 30px; }
        .admin-footer { background-color: #2c3e50; color: #fff; text-align: center; padding: 15px 0; margin-top: auto; }
        
        .sidebar-menu { list-style: none; padding: 0; margin: 0; }
        .sidebar-menu .menu-item { display: block; padding: 15px 20px; color: #bdc3c7; text-decoration: none; transition: 0.3s; border-left: 3px solid transparent; }
        .sidebar-menu .menu-item:hover, .sidebar-menu .menu-item.active { background-color: #34495e; color: #fff; border-left-color: #B1935B; }
        .sidebar-menu .menu-item i { margin-right: 10px; width: 20px; text-align: center; }
        .menu-header { padding: 15px 20px 5px 20px; font-size: 0.75em; color: #7f8c8d; font-weight: bold; letter-spacing: 1px; }
        .logout-btn-sidebar { width: 100%; text-align: left; background: none; border: none; color: #cbd5e0; padding: 12px 20px; cursor: pointer; display: flex; align-items: center; gap: 10px; font-size: 1em; transition: 0.3s; }
        
        .notif-wrapper { position: relative; }
        .btn-notif { color: #555; font-size: 1.3rem; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: 0.3s; cursor: pointer; }
        .badge-dot { position: absolute; top: 8px; right: 8px; width: 10px; height: 10px; background-color: #e74c3c; border-radius: 50%; border: 2px solid white; animation: pulse-dot 2s infinite; }
        .notif-dropdown { position: absolute; top: 55px; right: -10px; width: 320px; background: white; border-radius: 12px; box-shadow: 0 5px 25px rgba(0,0,0,0.15); overflow: hidden; display: none; border: 1px solid #eee; z-index: 1100; }
        .notif-dropdown.show { display: block; }

        .user-avatar-circle { width: 40px; height: 40px; background-color: #2c3e50; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }

        @keyframes pulse-dot { 0% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0.7); } 70% { box-shadow: 0 0 0 5px rgba(231, 76, 60, 0); } 100% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0); } }

        @media (max-width: 992px) {
            .admin-sidebar { width: 100% !important; min-height: auto; position: relative; }
            .sidebar-menu { display: none; }
            .sidebar-menu.active { display: block; position: absolute; top: 100%; left: 0; width: 100%; background: #2c3e50; box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
            .admin-main-content { width: 100% !important; }
            #mobileSidebarToggle { display: block !important; position: absolute; right: 20px; top: 20px; background: none; border: none; color: white; font-size: 1.5rem; }
        }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-logo">
            <div style="display:flex; align-items:center; gap:10px; justify-content: center;">
                <img src="{{ asset('assets/images/GALA.png') }}" alt="Gala">
                <h3 style="color: white; margin:0; font-size: 1.2em;">Bakso Gala</h3>
            </div>
            <button id="mobileSidebarToggle" style="display:none;"><i class="fas fa-bars"></i></button>
        </div>
        
        <ul class="sidebar-menu" id="sidebarMenu">
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

            <li class="menu-header">AKUN</li>
            <li>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn-sidebar"><i class="fas fa-sign-out-alt"></i> Logout</button>
                </form>
            </li>
        </ul>
    </aside>

    <div class="admin-main-content">
        <header class="admin-header">
            <div class="header-left">
                <button id="sidebarToggle" class="btn-icon" style="background:none; border:none; font-size:1.2em; cursor:pointer;"><i class="fas fa-bars"></i></button>
                <span class="greeting-text">Halo, {{ Auth::user()->name }}! 👋</span>
            </div>

            <div class="header-right" style="display:flex; align-items:center; gap:20px;">
                <div class="notif-wrapper" id="notifWrapper">
                    <div class="btn-notif" onclick="toggleNotifDropdown()">
                        <i class="fas fa-bell"></i>
                        <span class="badge-dot" style="display: none;" id="navBadge"></span>
                    </div>
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header" style="background: #2c3e50; color: white; padding: 10px 15px;"><h5>Pesanan Masuk</h5></div>
                        <div class="notif-body"><div class="notif-empty" style="padding: 20px; text-align: center;"><p>Tidak ada notifikasi baru.</p></div></div>
                        <div class="notif-footer" style="padding: 10px; text-align: center; border-top: 1px solid #eee;"><a href="{{ route('admin.orders.index') }}">Lihat Semua</a></div>
                    </div>
                </div>

                <div class="user-dropdown" style="display:flex; align-items:center; gap:10px;">
                    <div class="user-info-text" style="text-align: right;">
                        <span class="user-name-bold" style="display: block;">{{ Auth::user()->name }}</span>
                        <small class="text-muted">{{ ucfirst(Auth::user()->role) }}</small>
                    </div>
                    <div class="user-avatar-circle">{{ substr(Auth::user()->name, 0, 1) }}</div>
                </div>
            </div>
        </header>

        <div class="admin-content-inner">
            @yield('content')
        </div>
    </div>
</div>

{{-- AUDIO ALARM --}}
<audio id="alarmSound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto" loop></audio>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // --- 1. GLOBALS & ID SYNC ---
    let lastGlobalId = {{ \DB::table('orders')->max('id') ?? 0 }};
    let isAlertOpen = false; 
    const alarmAudio = document.getElementById('alarmSound');

    // --- 2. AUDIO UNLOCK (WAJIB KLIK LAYAR SEKALI) ---
    document.body.addEventListener('click', function() {
        if(alarmAudio.paused) {
            alarmAudio.muted = true;
            alarmAudio.play().then(() => { alarmAudio.pause(); alarmAudio.muted = false; });
        }
    }, { once: true });

    // --- 3. SIDEBAR LOGIC ---
    const mobileToggle = document.getElementById('mobileSidebarToggle');
    const sidebarToggle = document.getElementById('sidebarToggle');
    
    function toggleSide() {
        if(window.innerWidth <= 992) {
            document.getElementById('sidebarMenu').classList.toggle('active');
        } else {
            const sidebar = document.getElementById('adminSidebar');
            const main = document.querySelector('.admin-main-content');
            sidebar.style.display = sidebar.style.display === 'none' ? 'flex' : 'none';
            main.style.width = sidebar.style.display === 'none' ? '100%' : 'calc(100% - 260px)';
        }
    }
    if(mobileToggle) mobileToggle.addEventListener('click', toggleSide);
    if(sidebarToggle) sidebarToggle.addEventListener('click', toggleSide);

    function toggleNotifDropdown() { document.getElementById('notifDropdown').classList.toggle('show'); }

    // --- 4. POLLING (BACKUP JIKA FIREBASE DELAY) ---
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

    // --- 5. SHARED ALERT FUNCTION (FOR BOTH FIREBASE & POLLING) ---
    function triggerOrderAlert(id, title, message, type) {
        if(isAlertOpen || id <= lastGlobalId) return; 
        
        lastGlobalId = id; // Update agar polling tidak mengulang
        isAlertOpen = true;
        
        if(alarmAudio) {
            alarmAudio.currentTime = 0;
            alarmAudio.play().catch(e => console.log("Audio block:", e));
        }
        
        document.getElementById('navBadge').style.display = 'block';

        Swal.fire({
            title: title || '🔔 PESANAN BARU!',
            text: message || 'Ada pesanan masuk, cek sekarang!',
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

    setInterval(checkGlobalOrders, 5000);

    // --- 6. SERVICE WORKER REGISTRATION (SANGAT PENTING) ---
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/firebase-messaging-sw.js')
        .then((reg) => console.log('SW Registered:', reg.scope))
        .catch((err) => console.log('SW Error:', err));
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

    // Save Token to DB
    Notification.requestPermission().then((permission) => {
        if (permission === 'granted') {
            getToken(messaging, { vapidKey: 'BKKkRu1AiCDLOEndKleGE3P0yQunprYaUppLGulYJJmbiy3NupZ6RrMxI4fX8HfLnb-Opy7hcH-ObnXi0YDCT9c' }).then((token) => {
                if (token) {
                    fetch("{{ route('update.fcm-token') }}", {
                        method: "POST",
                        headers: { 
                            'Content-Type': 'application/json', 
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                        },
                        body: JSON.stringify({ token: token })
                    });
                }
            });
        }
    });

    // Foreground Listener
    onMessage(messaging, (payload) => {
        console.log('Firebase Foreground:', payload);
        const orderId = payload.data ? parseInt(payload.data.order_id) : (lastGlobalId + 1);
        triggerOrderAlert(orderId, payload.notification.title, payload.notification.body, 'success');
    });
</script>

@stack('scripts')
</body>
</html>