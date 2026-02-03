<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Bakso Gala')</title>

    {{-- 1. PASTIKAN CSS UTAMA (STYLE.CSS) DIPANGGIL DI SINI --}}
    {{-- Di sinilah styling untuk Navbar dan Footer biasanya berada --}}
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    
    {{-- CSS Navbar (jika dipisah) --}}
    <link rel="stylesheet" href="{{ asset('assets/css/navbar.css') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Slot untuk CSS tambahan per halaman (misal: about.css) --}}
    @yield('styles') 
</head>
<body>

    {{-- 1. NAVBAR (Selalu Tampil) --}}
    {{-- Kita hapus @if di sini agar navbar muncul di semua halaman, termasuk login --}}
    @include('partials.navbar')

    <main>
        {{-- Konten halaman masuk di sini --}}
        @yield('content')
    </main>

    {{-- 2. FOOTER (Disembunyikan Khusus di Halaman Auth) --}}
    {{-- Kita biarkan @if ini agar footer tetap HILANG di halaman login/register --}}
    @if(Route::currentRouteName() !== 'auth.index')
        @include('partials.footer')
    @endif

    @stack('scripts')
</body>
</html>