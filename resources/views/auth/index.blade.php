@extends('layouts.app')

@section('title', 'Login & Register - Bakso Gala')

@section('styles')
    <link rel="icon" href="{{ asset('assets/images/GALA.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">
    {{-- Kita load Font Awesome untuk ikon sosial media/input --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection

@section('content')
<main class="auth-page">
    {{-- Container Utama dengan ID untuk Javascript --}}
    <div class="container-auth" id="container">
        
        {{-- FORM REGISTER (Sign Up) --}}
        <div class="form-container sign-up-container">
            <form action="{{ route('register.process') }}" method="POST">
                @csrf
                <h1>Buat Akun</h1>
                <div class="social-container">
                    <a href="#" class="social"><i class="fab fa-google"></i></a>
                    <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
                </div>
                <span>atau gunakan email untuk pendaftaran</span>
                
                <input type="text" name="name" placeholder="Nama Lengkap" required />
                <input type="tel" name="phone_number" placeholder="Nomor Telepon" required />
                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="password" placeholder="Kata Sandi" required />
                
                <button type="submit">Daftar</button>
            </form>
        </div>

        {{-- FORM LOGIN (Sign In) --}}
        <div class="form-container sign-in-container">
            <form action="{{ route('login.process') }}" method="POST">
                @csrf
                <h1>Login</h1>
                <div class="social-container">
                    <a href="#" class="social"><i class="fab fa-google"></i></a>
                    <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
                </div>
                <span>atau gunakan akun anda</span>
                
                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="password" placeholder="Kata Sandi" required />
                
                <a href="#" class="forgot-pass">Lupa Kata Sandi?</a>
                <button type="submit">Masuk</button>
            </form>
        </div>

        {{-- OVERLAY (PANEL BERGERAK) --}}
        <div class="overlay-container">
            <div class="overlay">
                {{-- Panel Kiri (Muncul saat mode Register aktif) --}}
                <div class="overlay-panel overlay-left">
                    <h1>Selamat Datang Kembali!</h1>
                    <p>Untuk tetap terhubung dengan kami, silakan login dengan info pribadi Anda</p>
                    <button class="ghost" id="signIn">Masuk</button>
                </div>
                
                {{-- Panel Kanan (Muncul saat mode Login aktif) --}}
                <div class="overlay-panel overlay-right">
                    <h1>Halo, Sobat Gala!</h1>
                    <p>Masukkan detail pribadi Anda dan mulailah perjalanan kuliner bersama kami</p>
                    <button class="ghost" id="signUp">Daftar</button>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    // Script untuk Animasi Sliding
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const container = document.getElementById('container');

    signUpButton.addEventListener('click', () => {
        container.classList.add("right-panel-active");
    });

    signInButton.addEventListener('click', () => {
        container.classList.remove("right-panel-active");
    });
</script>
@endpush