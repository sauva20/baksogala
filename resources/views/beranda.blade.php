<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bakso Gala - Kelezatan Tiada Tara</title>

    {{-- Memanggil Aset --}}
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/navbar.css') }}">
    <link rel="icon" href="{{ asset('assets/images/GALA.png') }}" type="image/png">

    {{-- Link Eksternal --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.cdnfonts.com/css/bolton-sans" rel="stylesheet">

    {{-- CSS Tambahan Khusus Slider Review --}}
    <style>
        .testimonials-slider {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            padding: 20px 5px;
            
            /* PERBAIKAN 1: Paksa konten mulai dari kiri (flex-start) bukan tengah */
            justify-content: flex-start !important;
            
            /* PERBAIKAN 2: scroll-behavior harus 'auto' agar teleportasi loop tidak kelihatan mundur */
            scroll-behavior: auto !important; 
            
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        .testimonials-slider::-webkit-scrollbar {
            display: none; /* Hide scrollbar for Chrome/Safari/Opera */
        }
        .testimonial-card {
            min-width: 320px; 
            max-width: 320px;
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            text-align: left; 
            border: 1px solid #eee;
            transition: transform 0.3s ease;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
        }
        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .customer-photo {
            width: 100%;
            height: 200px; 
            margin-bottom: 15px;
            border-radius: 10px; 
            overflow: hidden;
            border: 1px solid #eee;
            background-color: #f9f9f9;
        }
        .customer-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover; 
            transition: transform 0.3s ease;
        }
        .testimonial-card:hover .customer-photo img {
            transform: scale(1.05); 
        }

        .customer-name {
            font-weight: 800;
            color: #2F3D65;
            font-size: 1.1rem;
            margin-top: auto; 
        }
        .ai-badge {
            background: #e8f5e9; color: #2e7d32;
            font-size: 0.7rem; font-weight: bold;
            padding: 4px 10px; border-radius: 20px;
            display: inline-flex; align-items: center; gap: 4px;
            margin-top: 5px; align-self: flex-start;
        }
    </style>
</head>
<body>

@include('partials.navbar')

<main>
    {{-- HERO SECTION --}}
    <section class="hero-section">
        <div class="container">
            <h1>Bakso Gala: Kelezatan Tiada Tara, Dari Hati ke Meja Anda!</h1>
            <p>Nikmati Bakso Asli dengan Resep Rahasia Keluarga, Disajikan Hangat Kapan Saja.</p>
            <a href="{{ url('/menu') }}" class="btn btn-hero">Pesan Sekarang</a>
        </div>
    </section>

    {{-- ABOUT US --}}
    <section class="about-us-section">
        <div class="container">
            <h2>Tentang Bakso Gala</h2>
            <p>Bakso Gala hadir untuk memuaskan hasrat Anda akan bakso otentik dengan cita rasa yang tak terlupakan. Kami menggunakan bahan-bahan segar pilihan dan resep turun-temurun.</p>
            <div class="features-grid">
                <div class="feature-item">
                    <i class="fas fa-leaf"></i>
                    <h3>Bahan Segar Berkualitas</h3>
                    <p>Kami hanya menggunakan daging sapi dan bahan baku terbaik, dipilih langsung dari peternak lokal.</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-utensils"></i>
                    <h3>Resep Autentik Warisan</h3>
                    <p>Diwariskan dari generasi ke generasi, resep kami menjamin cita rasa bakso yang kaya dan khas.</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-home"></i>
                    <h3>Suasana Nyaman</h3>
                    <p>Nikmati hidangan Anda dalam suasana kafe yang hangat, bersih, dan ramah keluarga.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- MENU PREVIEW --}}
    <section class="menu-preview-section">
        <div class="container">
            <h2>Cicipi Kelezatan Andalan Kami</h2>
            <div class="menu-items-grid">
                @forelse($menu_items as $item)
                    <div class="menu-item">
                        <img src="{{ asset($item->image_url) }}" alt="{{ $item->name }}">
                        <h3>{{ $item->name }}</h3>
                        <p>{{ Str::limit($item->description, 60) }}</p>
                        <span class="price">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="text-center" style="grid-column: 1/-1;">Belum ada menu yang ditampilkan di beranda.</p>
                @endforelse
            </div>
            <a href="{{ url('/menu') }}" class="btn btn-secondary">Lihat Menu Lengkap</a>
        </div>
    </section>

    {{-- TESTIMONIALS (INFINITE SCROLL) --}}
    <section class="testimonials-section">
        <div class="container">
            <h2>Kata Mereka Tentang Bakso Gala</h2>
            
            @if(isset($reviews) && $reviews->count() > 0)
                <div class="testimonials-slider">
                    @foreach($reviews as $review)
                        <div class="testimonial-card">
                            <div class="customer-photo">
                                @if($review->photo)
                                    <img src="{{ asset('uploads/' . $review->photo) }}" alt="Foto Review">
                                @else
                                    <div style="width:100%; height:100%; background:#f0f0f0; display:flex; align-items:center; justify-content:center;">
                                         <i class="fas fa-image" style="font-size: 3rem; color:#ccc;"></i>
                                    </div>
                                @endif
                            </div>

                            <div style="color: #ffc700; margin-bottom: 10px; font-size: 0.9rem;">
                                @for($i=0; $i < $review->rating; $i++)
                                    <i class="fas fa-star"></i>
                                @endfor
                            </div>

                            <p style="font-style: italic; color: #555; font-size: 0.95rem; line-height: 1.5; margin-bottom: 15px;">
                                "{{ Str::limit($review->comment, 120) }}"
                            </p>

                            <cite class="customer-name">
                                - {{ $review->order->customer_name ?? 'Pelanggan Setia' }}
                            </cite>

                            <div class="ai-badge">
                                <i class="fas fa-check-circle"></i> Pilihan AI
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="testimonials-slider">
                    <div class="testimonial-card">
                        <div class="customer-photo">
                            <div style="width:100%; height:100%; background:#f0f0f0; display:flex; align-items:center; justify-content:center; color:#ccc;">
                                <i class="fas fa-store" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                        <p>"Jadilah orang pertama yang memberikan ulasan terbaik Anda dan tampil di sini!"</p>
                        <cite class="customer-name">- Admin Bakso Gala</cite>
                    </div>
                </div>
            @endif

        </div>
    </section>

    {{-- CTA FINAL --}}
    <section class="cta-final-section">
        <div class="container">
            <h2>Siap Menikmati Kelezatan Bakso Gala?</h2>
            <p>Kunjungi kami langsung atau pesan online sekarang!</p>
            <p class="contact-info-footer">Jl. Otto Iskandardinata No.115, Karanganyar, Kec. Subang, Kabupaten Subang, Jawa Barat 41211</p>
            <p class="contact-info-footer">Contact: <a href="tel:+62221234567">+62 881-0816-31531</a></p>
        </div>
    </section>
</main>

<footer>
    <div class="container">
        <div class="footer-cols">
            <div class="footer-col">
                <h3>Bakso Gala</h3>
                <p>Bakso Gala hadir untuk kelezatan sejati. Nikmati hidangan bakso otentik yang dibuat dengan cinta.</p>
            </div>
            <div class="footer-col">
                <h3>Informasi</h3>
                <ul>
                    <li><a href="{{ url('/tentang-kami') }}">Tentang Kami</a></li>
                    <li><a href="{{ url('/menu') }}">Menu</a></li>
                    <li><a href="{{ url('/faq') }}">FAQ</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Hubungi Kami</h3>
                <p>Jl. Otto Iskandardinata No.115, Subang</p>
                <p>Email: baksocapgala@gmail.com</p>
            </div>
            <div class="footer-col">
                <h3>Ikuti Kami</h3>
                <div class="social-links">
                    <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a href="#" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" target="_blank"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2025 Bakso Gala. Semua Hak Dilindungi.</p>
        </div>
    </div>
</footer>

<script>
    // --- 1. HAMBURGER MENU ---
    const hamburger = document.getElementById('hamburgerMenu');
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            document.getElementById('mainNav').classList.toggle('active');
        });
    }

    // --- 2. CONTINUOUS AUTO SCROLL ---
    document.addEventListener("DOMContentLoaded", function() {
        const slider = document.querySelector('.testimonials-slider');
        
        // Cek jika slider ada dan punya anak (card)
        if (slider && slider.children.length > 0) {
            
            // Gandakan isi slider (minimal 2x) agar loop tidak terputus
            const content = slider.innerHTML;
            slider.innerHTML += content; 
            
            let speed = 1; // Kecepatan pixel per step
            let isHovered = false;

            function move() {
                if (!isHovered) {
                    slider.scrollLeft += speed;
                    
                    // Reset ke 0 secara instan jika sudah sampai di setengah lebar total (akhir konten asli)
                    if (slider.scrollLeft >= (slider.scrollWidth / 2)) {
                        slider.scrollLeft = 0;
                    }
                }
                requestAnimationFrame(move);
            }

            // Jalankan animasi
            move();

            // Event listener untuk pause saat interaksi
            slider.addEventListener('mouseenter', () => isHovered = true);
            slider.addEventListener('mouseleave', () => isHovered = false);
            slider.addEventListener('touchstart', () => isHovered = true);
            slider.addEventListener('touchend', () => isHovered = false);
        }
    });
</script>
</body>
</html>