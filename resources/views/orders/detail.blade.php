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

    <style>
        /* CONTAINER SLIDER */
        .testimonials-slider {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            padding: 20px 5px;
            /* MATIKAN magnet CSS supaya JS bisa gerak mulus pixel demi pixel */
            scroll-snap-type: none !important; 
            scroll-behavior: auto !important;
            -ms-overflow-style: none;
            scrollbar-width: none;
            justify-content: flex-start;
        }
        
        .testimonials-slider::-webkit-scrollbar {
            display: none;
        }

        /* KARTU REVIEW */
        .testimonial-card {
            min-width: 300px;
            max-width: 300px;
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            text-align: left;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .customer-photo {
            width: 100%;
            height: 200px;
            margin-bottom: 15px;
            border-radius: 10px;
            overflow: hidden;
            background-color: #f9f9f9;
        }
        .customer-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
    {{-- TESTIMONIALS SECTION --}}
    <section class="testimonials-section">
        <div class="container">
            <h2>Kata Mereka Tentang Bakso Gala</h2>
            
            @if(isset($reviews) && $reviews->count() > 0)
                <div class="testimonials-slider" id="reviewSlider">
                    {{-- Urutan Terbaru (Desc) --}}
                    @foreach($reviews->sortByDesc('created_at') as $review)
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
                            <div style="color: #ffc700; margin-bottom: 10px;">
                                @for($i=0; $i < $review->rating; $i++) <i class="fas fa-star"></i> @endfor
                            </div>
                            <p style="font-style: italic; color: #555;">"{{ Str::limit($review->comment, 120) }}"</p>
                            <cite class="customer-name">- {{ $review->order->customer_name ?? 'Pelanggan Setia' }}</cite>
                            <div class="ai-badge"><i class="fas fa-check-circle"></i> Pilihan AI</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</main>

<script>
    // 1. HAMBURGER MENU
    const hamburger = document.getElementById('hamburgerMenu');
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            document.getElementById('mainNav').classList.toggle('active');
        });
    }

    // 2. TRUE INFINITE AUTO SCROLL (BISA SCROLL KIRI & KANAN TANPA BATAS)
    document.addEventListener("DOMContentLoaded", function() {
        const slider = document.getElementById('reviewSlider');
        if (!slider || slider.children.length === 0) return;

        // Gandakan konten 3 kali (biar bisa scroll ke kiri juga dari awal)
        const content = slider.innerHTML;
        slider.innerHTML = content + content + content;

        let isPaused = false;
        let speed = 1; 

        // Set posisi awal di tengah (supaya bisa scroll ke kiri langsung)
        slider.scrollLeft = slider.scrollWidth / 3;

        function animate() {
            if (!isPaused) {
                slider.scrollLeft += speed;

                // Reset posisi secara halus tanpa kelihatan mata
                if (slider.scrollLeft >= (slider.scrollWidth * 2 / 3)) {
                    slider.scrollLeft = slider.scrollWidth / 3;
                } else if (slider.scrollLeft <= 0) {
                    slider.scrollLeft = slider.scrollWidth / 3;
                }
            }
            requestAnimationFrame(animate);
        }

        requestAnimationFrame(animate);

        // Berhenti saat disentuh/hover
        const stop = () => isPaused = true;
        const start = () => isPaused = false;

        slider.addEventListener('mouseenter', stop);
        slider.addEventListener('mouseleave', start);
        slider.addEventListener('touchstart', stop);
        slider.addEventListener('touchend', start);
    });
</script>
</body>
</html>