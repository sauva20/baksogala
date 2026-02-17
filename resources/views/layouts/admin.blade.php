<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        /* --- CSS DEFAULT (DESKTOP) --- */
        .admin-wrapper { display: flex; min-height: 100vh; width: 100%; overflow-x: hidden; }
        .admin-sidebar { width: 260px; min-width: 260px; flex-shrink: 0; background-color: #2c3e50; color: white; min-height: 100vh; display: flex; flex-direction: column; transition: 0.3s; }
        .sidebar-logo { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-logo img { max-width: 60px; height: auto; display: block; margin: 0 auto 10px auto; }
        .admin-main-content { flex-grow: 1; width: calc(100% - 260px); background-color: #f4f6f9; display: flex; flex-direction: column; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; height: 70px; padding: 0 30px; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 25px; flex-shrink: 0; position: relative; z-index: 100; }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .admin-content-inner { flex-grow: 1; padding: 30px; }
        .admin-footer { background-color: #2c3e50; color: #fff; text-align: center; padding: 15px 0; margin-top: auto; }
        
        /* Sidebar Menu Style */
        .sidebar-menu { list-style: none; padding: 0; margin: 0; }
        .sidebar-menu .menu-item { display: block; padding: 15px 20px; color: #bdc3c7; text-decoration: none; transition: 0.3s; border-left: 3px solid transparent; }
        .sidebar-menu .menu-item:hover, .sidebar-menu .menu-item.active { background-color: #34495e; color: #fff; border-left-color: #B1935B; }
        .sidebar-menu .menu-item i { margin-right: 10px; width: 20px; text-align: center; }
        .menu-header { padding: 15px 20px 5px 20px; font-size: 0.75em; color: #7f8c8d; font-weight: bold; letter-spacing: 1px; }
        .logout-btn-sidebar { width: 100%; text-align: left; background: none; border: none; color: #cbd5e0; padding: 12px 20px; cursor: pointer; display: flex; align-items: center; gap: 10px; font-size: 1em; transition: 0.3s; }
        .logout-btn-sidebar:hover { background-color: rgba(255,255,255,0.1); color: white; }

        /* Notifikasi & User Dropdown */
        .notif-wrapper { position: relative; cursor: pointer; }
        .btn-notif { color: #555; font-size: 1.3rem; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: 0.3s; }
        .btn-notif:hover { background-color: #f0f2f5; color: #2c3e50; }
        .badge-dot { position: absolute; top: 8px; right: 8px; width: 10px; height: 10px; background-color: #e74c3c; border-radius: 50%; border: 2px solid white; animation: pulse-dot 2s infinite; }
        .notif-dropdown { position: absolute; top: 55px; right: -10px; width: 320px; background: white; border-radius: 12px; box-shadow: 0 5px 25px rgba(0,0,0,0.15); overflow: hidden; display: none; border: 1px solid #eee; z-index: 1000; }
        .notif-dropdown.show { display: block; }
        .notif-header { background-color: #2c3e50; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .notif-body { max-height: 300px; overflow-y: auto; }
        .notif-item { display: flex; gap: 12px; padding: 12px 15px; border-bottom: 1px solid #f5f5f5; text-decoration: none; color: #333; transition: 0.2s; align-items: flex-start; }
        .notif-item:hover { background-color: #f9f9f9; }
        .notif-item.unread { background-color: #fff8e1; }
        .notif-icon { width: 35px; height: 35px; background: #e3f2fd; color: #1976d2; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; flex-shrink: 0; }
        .notif-content div { font-weight: 600; font-size: 0.9rem; margin-bottom: 2px; }
        .notif-content p { margin: 0; font-size: 0.8rem; color: #666; }
        .notif-time { font-size: 0.7rem; color: #999; margin-top: 4px; display: block; }
        .notif-footer { padding: 10px; text-align: center; border-top: 1px solid #eee; background: #fcfcfc; }
        .notif-footer a { text-decoration: none; color: #2c3e50; font-size: 0.85rem; font-weight: 600; }
        .notif-empty { padding: 30px; text-align: center; color: #999; }
        .notif-empty i { font-size: 2rem; margin-bottom: 10px; display: block; opacity: 0.3; }

        .user-dropdown { display: flex; align-items: center; gap: 12px; }
        .user-info-text { display: flex; flex-direction: column; text-align: right; }
        .user-name-bold { font-weight: 700; color: #333; font-size: 0.9em; }
        .user-role-badge { font-size: 0.75em; color: #888; text-transform: uppercase; }
        .user-avatar-circle { width: 40px; height: 40px; min-width: 40px; background-color: #2c3e50; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2em; }

        @keyframes pulse-dot { 0% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0.7); } 70% { box-shadow: 0 0 0 5px rgba(231, 76, 60, 0); } 100% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0); } }

        /* --- MOBILE RESPONSIVE FIX --- */
        @media (max-width: 992px) {
            .admin-wrapper { flex-direction: column; }
            .admin-sidebar { width: 100% !important; min-width: 100% !important; min-height: auto; padding: 10px 20px; flex-direction: row; justify-content: space-between; align-items: center; position: relative; z-index: 1000; }
            .sidebar-logo { border: none; padding: 0; display: flex; align-items: center; gap: 10px; width: 100%; justify-content: space-between; }
            #mobileSidebarToggle { display: block; color: white; font-size: 1.5rem; background: none; border: none; cursor: pointer; padding: 5px; }
            .sidebar-menu { display: none; position: absolute; top: 100%; left: 0; width: 100%; background-color: #2c3e50; z-index: 999; box-shadow: 0 5px 10px rgba(0,0,0,0.2); }
            .sidebar-menu.active { display: block; }
            .admin-main-content { width: 100%; }
            .admin-header { padding: 10px 20px; flex-direction: column-reverse; height: auto; gap: 15px; align-items: flex-start; }
            .header-left { width: 100%; justify-content: space-between; }
            .admin-header #sidebarToggle { display: none; }
            .notif-dropdown { position: fixed; top: 60px; left: 10px; right: 10px; width: auto; max-width: none; }
        }

        @media (min-width: 993px) {
            #mobileSidebarToggle { display: none; }
        }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <aside class="admin-sidebar">
        <div class="sidebar-logo">
            <div style="display:flex; align-items:center; gap:10px;">
                <img src="{{ asset('assets/images/GALA.png') }}" alt="Gala" onerror="this.style.display='none'">
                <h3 style="color: white; margin:0; font-size: 1.2em;">Bakso Gala</h3>
            </div>
            <button id="mobileSidebarToggle"><i class="fas fa-bars"></i></button>
        </div>
        
        <ul class="sidebar-menu">
            {{-- 1. DASHBOARD: HANYA OWNER --}}
            @if(Auth::user()->role == 'owner')
            <li><a href="{{ route('admin.dashboard') }}" class="menu-item {{ Route::is('admin.dashboard') ? 'active' : '' }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            @endif

            <li class="menu-header">OPERASIONAL</li>
            
            {{-- 2. PESANAN: SEMUA (OWNER & KASIR) --}}
            <li><a href="{{ route('admin.orders.index') }}" class="menu-item {{ Route::is('admin.orders*') ? 'active' : '' }}"><i class="fas fa-receipt"></i> Pesanan</a></li>
            
            {{-- 3. MENU: SEMUA (OWNER & KASIR) --}}
            <li><a href="{{ route('admin.menu.index') }}" class="menu-item {{ Route::is('admin.menu*') ? 'active' : '' }}"><i class="fas fa-utensils"></i> Manajemen Menu</a></li>
            
            <li><a href="{{ route('admin.reviews.index') }}" class="menu-item {{ Route::is('admin.reviews*') ? 'active' : '' }}"><i class="fas fa-star"></i> Manajemen Review</a></li>
            {{-- 4. FITUR OWNER (PROMO, LAPORAN, USER) --}}
            @if(Auth::user()->role == 'owner')
                <li><a href="{{ route('admin.promotions.index') }}" class="menu-item {{ Route::is('admin.promotions*') ? 'active' : '' }}"><i class="fas fa-tags"></i> Diskon & Voucher</a></li>
                
                <li class="menu-header">ADMINISTRASI</li>
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
                <button id="sidebarToggle" class="btn-icon" style="background:none; border:none; font-size:1.2em; cursor:pointer; color:#333; margin-right: 15px;"><i class="fas fa-bars"></i></button>
                <span class="greeting-text">Halo, {{ Auth::user()->name ?? 'Admin' }}! 👋</span>
            </div>

            <div class="header-right">
                <div class="notif-wrapper" id="notifWrapper">
                    <div class="btn-notif" onclick="toggleNotifDropdown()">
                        <i class="fas fa-bell"></i>
                        <span class="badge-dot" style="display: none;" id="navBadge"></span>
                    </div>

                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header">
                            <h5>Pesanan Masuk</h5>
                        </div>
                        <div class="notif-body">
                            <div class="notif-empty"><i class="fas fa-bell-slash"></i><p>Tidak ada notifikasi baru.</p></div>
                        </div>
                        <div class="notif-footer">
                            <a href="{{ route('admin.orders.index') }}">Lihat Semua Pesanan <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="user-dropdown">
                    <div class="user-info-text">
                        <span class="user-name-bold">{{ Auth::user()->name ?? 'Administrator' }}</span>
                        <span class="user-role-badge">{{ ucfirst(Auth::user()->role ?? 'Admin') }}</span>
                    </div>
                    <div class="user-avatar-circle">{{ substr(Auth::user()->name ?? 'A', 0, 1) }}</div>
                </div>
            </div>
        </header>

        <div class="admin-content-inner">
            @yield('content')
        </div>

        <footer class="admin-footer">
            <div class="container-fluid"><p>&copy; {{ date('Y') }} Bakso Gala. All rights reserved.</p></div>
        </footer>
    </div>
</div>

{{-- SUARA ALARM BERISIK (SIRENE - LOOP) --}}
<audio id="alarmSound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto" loop></audio>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // --- 1. LOGIKA TOGGLE SIDEBAR (FIXED FOR MOBILE) ---
    function handleSidebarToggle(e) {
        e.preventDefault();
        
        if (window.innerWidth <= 992) {
            const menu = document.querySelector('.sidebar-menu');
            if (menu.style.display === 'block') {
                menu.style.display = 'none';
            } else {
                menu.style.display = 'block';
            }
        } else {
            const sidebar = document.querySelector('.admin-sidebar');
            const mainContent = document.querySelector('.admin-main-content');
            
            if (sidebar.style.display === 'none') {
                sidebar.style.display = 'flex';
                mainContent.style.width = 'calc(100% - 260px)';
            } else {
                sidebar.style.display = 'none';
                mainContent.style.width = '100%';
            }
        }
    }

    const desktopToggle = document.getElementById('sidebarToggle');
    const mobileToggle = document.getElementById('mobileSidebarToggle');

    if(desktopToggle) desktopToggle.addEventListener('click', handleSidebarToggle);
    if(mobileToggle) mobileToggle.addEventListener('click', handleSidebarToggle);


    // --- 2. NOTIFIKASI DROPDOWN ---
    function toggleNotifDropdown() {
        const dropdown = document.getElementById('notifDropdown');
        dropdown.classList.toggle('show');
    }

    document.addEventListener('click', function(event) {
        const wrapper = document.getElementById('notifWrapper');
        const dropdown = document.getElementById('notifDropdown');
        if (!wrapper.contains(event.target)) {
            dropdown.classList.remove('show');
        }
    });

    // --- 3. AUTO CHECK NEW ORDERS (ALARM BERISIK & AUTO CANCEL) ---
    // Pastikan ini berjalan di halaman selain login
    @if(Auth::check())
        let lastGlobalId = {{ \DB::table('orders')->max('id') ?? 0 }};
        let isAlertOpen = false; 
        const alarmAudio = document.getElementById('alarmSound');

        document.body.addEventListener('click', function() {
            if(alarmAudio.paused) {
                alarmAudio.muted = true;
                alarmAudio.play().then(() => {
                    alarmAudio.pause();
                    alarmAudio.muted = false;
                    alarmAudio.currentTime = 0;
                }).catch(e => {});
            }
        }, { once: true });

        function playLoudAlarm() {
            alarmAudio.currentTime = 0;
            alarmAudio.volume = 1.0; 
            alarmAudio.play().catch(error => console.log("Audio gagal play (klik layar dulu):", error));
        }

        function stopLoudAlarm() {
            alarmAudio.pause();
            alarmAudio.currentTime = 0;
        }
        
        function checkGlobalOrders() {
            if(isAlertOpen) return;

            fetch('{{ route("admin.orders.checkNew") }}?last_id=' + lastGlobalId)
                .then(response => response.json())
                .then(data => {
                    if (data.has_new) {
                        lastGlobalId = data.latest_id;
                        isAlertOpen = true;
                        
                        playLoudAlarm();
                        
                        const badge = document.getElementById('navBadge');
                        if(badge) badge.style.display = 'block';

                        let popupIcon = 'info';
                        let popupColor = '#3085d6';
                        
                        if (data.type === 'success') {
                            popupIcon = 'success';
                            popupColor = '#27ae60';
                        } else if (data.type === 'warning') {
                            popupIcon = 'warning';
                            popupColor = '#f39c12';
                        }

                        Swal.fire({
                            title: data.title,
                            text: data.message,
                            icon: popupIcon,
                            showCancelButton: true,
                            confirmButtonText: '🔊 MATIKAN ALARM & LIHAT',
                            cancelButtonText: 'Biarkan Berbunyi',
                            confirmButtonColor: popupColor,
                            cancelButtonColor: '#d33',
                            allowOutsideClick: false, 
                            allowEscapeKey: false,
                            backdrop: `rgba(0,0,0,0.8)`
                        }).then((res) => {
                            if(res.isConfirmed) {
                                stopLoudAlarm();
                                isAlertOpen = false;
                                window.location.href = "{{ route('admin.orders.index') }}";
                            } else {
                                isAlertOpen = false;
                            }
                        });
                    }
                })
                .catch(err => console.error('Silent Check Error:', err));
        }

        setInterval(checkGlobalOrders, 5000);
    @endif
</script>

@stack('scripts')
</body>
</html>