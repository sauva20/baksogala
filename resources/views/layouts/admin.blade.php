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
        /* --- SIDEBAR & LAYOUT --- */
        .admin-wrapper { display: flex; min-height: 100vh; width: 100%; overflow-x: hidden; }
        .admin-sidebar { width: 260px !important; min-width: 260px !important; flex-shrink: 0 !important; background-color: #2c3e50; color: white; min-height: 100vh; display: flex; flex-direction: column; transition: 0.3s; }
        .sidebar-logo { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-logo img { max-width: 60px; height: auto; display: block; margin: 0 auto 10px auto; }
        .admin-main-content { flex-grow: 1; width: calc(100% - 260px); background-color: #f4f6f9; display: flex; flex-direction: column; }

        /* --- HEADER --- */
        .admin-header { display: flex; justify-content: space-between; align-items: center; height: 70px; padding: 0 30px; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 25px; flex-shrink: 0; position: relative; z-index: 100; }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .header-left .greeting-text { font-size: 1.1em; color: #2c3e50; font-weight: 600; }
        .header-right { display: flex; align-items: center; gap: 25px; }

        /* --- NOTIFIKASI DROPDOWN --- */
        .notif-wrapper { position: relative; cursor: pointer; }
        .btn-notif { color: #555; font-size: 1.3rem; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: 0.3s; }
        .btn-notif:hover { background-color: #f0f2f5; color: #2c3e50; }
        .badge-dot { position: absolute; top: 8px; right: 8px; width: 10px; height: 10px; background-color: #e74c3c; border-radius: 50%; border: 2px solid white; animation: pulse-dot 2s infinite; }
        @keyframes pulse-dot { 0% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0.7); } 70% { box-shadow: 0 0 0 5px rgba(231, 76, 60, 0); } 100% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0); } }

        /* Dropdown Box */
        .notif-dropdown { position: absolute; top: 55px; right: -10px; width: 320px; background: white; border-radius: 12px; box-shadow: 0 5px 25px rgba(0,0,0,0.15); overflow: hidden; display: none; border: 1px solid #eee; transform-origin: top right; animation: scaleIn 0.2s ease-out; }
        .notif-dropdown.show { display: block; }
        @keyframes scaleIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
        
        .notif-header { background-color: #2c3e50; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .notif-header h5 { margin: 0; font-size: 0.95rem; font-weight: 600; }
        .notif-header span { background: #e74c3c; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: bold; }
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
        .logout-btn-sidebar { width: 100%; text-align: left; background: none; border: none; color: #cbd5e0; padding: 12px 20px; cursor: pointer; display: flex; align-items: center; gap: 10px; font-size: 1em; transition: 0.3s; }
        .logout-btn-sidebar:hover { background-color: rgba(255,255,255,0.1); color: white; }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <aside class="admin-sidebar">
        <div class="sidebar-logo">
            <img src="{{ asset('assets/images/GALA.png') }}" alt="Gala" onerror="this.style.display='none'">
            <h3 style="color: white; margin:0; font-size: 1.2em;">Bakso Gala</h3>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="{{ route('admin.dashboard') }}" class="menu-item {{ Route::is('admin.dashboard') ? 'active' : '' }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li class="menu-header">OPERASIONAL</li>
            <li><a href="{{ route('admin.orders.index') }}" class="menu-item {{ Route::is('admin.orders*') ? 'active' : '' }}"><i class="fas fa-receipt"></i> Pesanan</a></li>
            <li><a href="{{ route('admin.menu.index') }}" class="menu-item {{ Route::is('admin.menu*') ? 'active' : '' }}"><i class="fas fa-utensils"></i> Manajemen Menu</a></li>
            <li><a href="{{ route('admin.promotions.index') }}" class="menu-item {{ Route::is('admin.promotions*') ? 'active' : '' }}"><i class="fas fa-tags"></i> Diskon & Voucher</a></li>
            <li class="menu-header">ADMINISTRASI</li>
            <li><a href="{{ route('admin.reports.index') }}" class="menu-item {{ Route::is('admin.reports*') ? 'active' : '' }}"><i class="fas fa-chart-line"></i> Laporan</a></li>
            <li><a href="{{ route('admin.users.index') }}" class="menu-item {{ Route::is('admin.users*') ? 'active' : '' }}"><i class="fas fa-users"></i> Data Pelanggan</a></li>
            <li><a href="{{ route('admin.logs.index') }}" class="menu-item {{ Route::is('admin.logs*') ? 'active' : '' }}"><i class="fas fa-history"></i> Riwayat Log</a></li>
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
                {{-- DATA PESANAN BARU UNTUK DROPDOWN --}}
                @php
                    $newOrdersList = \DB::table('orders')->where('status', 'new')->orderBy('created_at', 'desc')->limit(5)->get();
                    $newCount = $newOrdersList->count();
                @endphp

                {{-- NOTIFIKASI DROPDOWN --}}
                <div class="notif-wrapper" id="notifWrapper">
                    <div class="btn-notif" onclick="toggleNotifDropdown()">
                        <i class="fas fa-bell"></i>
                        {{-- Badge Merah --}}
                        <span class="badge-dot" style="{{ $newCount > 0 ? 'display: block;' : 'display: none;' }}" id="navBadge"></span>
                    </div>

                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header">
                            <h5>Pesanan Masuk</h5>
                            <span>{{ $newCount }} Baru</span>
                        </div>
                        <div class="notif-body">
                            @forelse($newOrdersList as $nOrder)
                                <a href="{{ route('admin.orders.index', ['status' => 'new']) }}" class="notif-item unread">
                                    <div class="notif-icon"><i class="fas fa-utensils"></i></div>
                                    <div class="notif-content">
                                        <div>{{ $nOrder->customer_name }}</div>
                                        <p>{{ Str::before($nOrder->shipping_address, '-') }}</p>
                                        <span class="notif-time">{{ \Carbon\Carbon::parse($nOrder->created_at)->diffForHumans() }}</span>
                                    </div>
                                </a>
                            @empty
                                <div class="notif-empty"><i class="fas fa-bell-slash"></i><p>Tidak ada pesanan baru.</p></div>
                            @endforelse
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

{{-- AUDIO NOTIFIKASI --}}
<audio id="globalNotifSound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stack('scripts')

<script>
    // 1. SIDEBAR & DROPDOWN TOGGLE
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        const sidebar = document.querySelector('.admin-sidebar');
        sidebar.style.display = (sidebar.style.display === 'none') ? 'flex' : 'none';
    });

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

    // 2. SISTEM NOTIFIKASI REAL-TIME (GLOBAL)
    
    // PERBAIKAN UTAMA: Hapus penggunaan LocalStorage untuk ID. 
    // Kita inisialisasi ulang ID setiap kali halaman direfresh agar tidak nyangkut.
    let lastGlobalId = {{ \DB::table('orders')->max('id') ?? 0 }};
    
    console.log("Notifikasi Aktif. Memantau ID > " + lastGlobalId);

    function checkGlobalOrders() {
        fetch('{{ route("admin.orders.checkNew") }}?last_id=' + lastGlobalId)
            .then(response => response.json())
            .then(data => {
                // Debugging: Cek console browser Anda
                // console.log("Cek pesanan...", data); 

                if (data.has_new) {
                    console.log("PESANAN BARU DITEMUKAN! ID: " + data.latest_id);
                    
                    // Update ID terakhir
                    lastGlobalId = data.latest_id;
                    
                    // A. Bunyikan Suara
                    const audio = document.getElementById('globalNotifSound');
                    if(audio) audio.play().catch(e => console.log('Klik halaman dulu agar suara jalan.'));

                    // B. Munculkan Badge Merah
                    const badge = document.getElementById('navBadge');
                    if(badge) badge.style.display = 'block';

                    // C. Notifikasi Windows/System
                    if (Notification.permission === "granted") {
                        const notif = new Notification("PESANAN BARU MASUK!", {
                            body: "Pelanggan menunggu! Klik untuk melihat.",
                            icon: "https://cdn-icons-png.flaticon.com/512/1046/1046784.png"
                        });
                        notif.onclick = function() {
                            window.focus();
                            window.location.href = "{{ route('admin.orders.index') }}"; 
                        };
                    }

                    // D. POPUP TENGAH LAYAR (SWEETALERT) DENGAN TOMBOL REFRESH
                    Swal.fire({
                        title: '🔔 ADA PESANAN BARU!',
                        html: '<div style="font-size:1.1em; margin-bottom:10px;">Ada pelanggan baru saja memesan!</div>',
                        icon: 'warning',
                        // Gambar animasi (Opsional, hapus jika link mati)
                        imageUrl: 'https://media.giphy.com/media/l0HlBO7eyXzSZkJri/giphy.gif',
                        imageWidth: 200,
                        imageHeight: 150,
                        
                        showCancelButton: true,
                        confirmButtonText: '⚡ Refresh Halaman',
                        cancelButtonText: 'Tutup',
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        backdrop: `rgba(0,0,123,0.4)`
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('admin.orders.index') }}";
                        }
                    });
                }
            })
            .catch(err => console.error('Silent Check Error:', err));
    }

    // Minta izin notifikasi saat awal buka
    document.addEventListener('DOMContentLoaded', () => {
        if (Notification.permission !== "granted" && Notification.permission !== "denied") {
            Notification.requestPermission();
        }
    });

    // Cek setiap 5 detik
    setInterval(checkGlobalOrders, 5000);
</script>
</body>
</html>