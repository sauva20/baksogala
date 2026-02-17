@extends('layouts.app')

@section('title', 'Tentang Kami - Bakso Gala')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/about.css') }}">
    <link rel="icon" href="{{ asset('assets/images/GALA.png') }}" type="image/png">
@endsection

@section('content')

<main class="about-page">
    
    <header class="about-hero">
        <div class="container">
            <h1>Mengenal Lebih Dekat <strong>Bakso Gala</strong></h1>
            <p class="subtitle">Kelezatan Bakso Otentik, Dibuat dengan Cinta dan Resep Rahasia Keluarga.</p>
        </div>
    </header>

    <div class="container content-container">
        
        <section class="content-section" id="kisah-kami">
            <div class="section-icon"><i class="fas fa-utensils"></i></div>
            <h2>Kisah Bakso Gala</h2>
            <p>Berawal dari resep bakso turun-temurun, Bakso Gala lahir dari impian untuk menyajikan bakso dengan cita rasa otentik yang tak terlupakan. Dimulai dari ide kecil, kami selalu berpegang teguh pada kualitas bahan baku dan proses pembuatan yang higienis. Setiap mangkuk bakso yang kami sajikan adalah perwujudan dari dedikasi kami untuk memberikan pengalaman kuliner terbaik bagi setiap pelanggan.</p>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-icon"><i class="fas fa-seedling"></i></div>
                    <div class="timeline-content">
                        <h4>2023 - Titik Awal</h4>
                        <p>Bakso Gala pertama kali didirikan dengan ide yang di kembangkan dari bakmie cap gala, dengan fokus pada bakso kuah klasik yang sederhana namun kaya rasa.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-icon"><i class="fas fa-fire"></i></div>
                    <div class="timeline-content">
                        <h4>2025 - Inovasi Bakso</h4>
                        <p>Febuari memperkenalkan inovasi baru: **Bakso Cap Gala** dengan isian daging yang khas, yang langsung menjadi favorit pelanggan dan membuat nama kami semakin dikenal.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-icon"><i class="fas fa-store-alt"></i></div>
                    <div class="timeline-content">
                        <h4>2025 - Perkembangan</h4>
                        <p>Untuk menjangkau lebih banyak penikmat bakso, merubah dan mengpgrade inovasi kami menjadi bakso khas yaitu bakso cap gala, menawarkan suasana kafe yang lebih nyaman dan modern.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="content-section" id="visi-misi">
            <div class="section-icon"><i class="fas fa-bullseye"></i></div>
            <h2>Visi & Misi Kami</h2>
            <div class="vision-mission-grid">
                <div class="grid-item">
                    <h4>Visi</h4>
                    <p>Menjadi kafe bakso terdepan yang dikenal karena kelezatan dan kualitas otentiknya, serta menjadi tempat berkumpul favorit keluarga dan teman.</p>
                </div>
                <div class="grid-item">
                    <h4>Misi</h4>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Menyajikan hidangan bakso berkualitas tinggi dengan bahan-bahan segar pilihan.</li>
                        <li><i class="fas fa-check-circle"></i> Memberikan pelayanan yang ramah, cepat, dan profesional.</li>
                        <li><i class="fas fa-check-circle"></i> Menciptakan suasana kafe yang nyaman, bersih, dan menyenangkan bagi pelanggan.</li>
                        <li><i class="fas fa-check-circle"></i> Terus berinovasi dengan menu-menu baru yang menggugah selera.</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="content-section" id="lokasi">
            <div class="section-icon"><i class="fas fa-map-marked-alt"></i></div>
            <h2>Lokasi & Layanan Kami</h2>
            <p>Kunjungi kami di salah satu lokasi kami untuk merasakan langsung kelezatan bakso kami atau pesan online dari rumah.</p>

            <div class="location-tabs">
                <button class="tab-button active" data-target="#pusat"><i class="fas fa-star"></i> Cabang Pusat</button>
            </div>

            <div class="location-content-container">
                <div id="pusat" class="location-content active">
                    <h3>Detail Lokasi</h3>
                    <p><strong>Bakso Gala, Subang</strong><br>
                    Jl. Otto Iskandardinata No.115, Karanganyar, Kec. Subang, Kabupaten Subang, Jawa Barat 41211</p>
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3963.666663242784!2d107.7584852758652!3d-6.563660364160416!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e693b7724391e43%3A0xc3f98295679f228d!2sJl.%20Otto%20Iskandardinata%20No.115%2C%20Karanganyar%2C%20Kec.%20Subang%2C%20Kabupaten%20Subang%2C%20Jawa%20Barat%2041211!5e0!3m2!1sen!2sid!4v1709228945632!5m2!1sen!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </section>

        <section class="content-section" id="kontak">
            <div class="section-icon"><i class="fas fa-phone-volume"></i></div>
            <h2>Kontak & Jam Operasional</h2>
            <p>Untuk pertanyaan, pemesanan, atau info lainnya, hubungi kami di:</p>
            <div class="contact-grid">
                <div class="contact-item">
                    <i class="fab fa-whatsapp"></i>
                    <div>
                        <strong>Contact</strong>
                        <a href="https://wa.me/6281234567890" target="_blank">+62 881-0816-31531</a>
                    </div>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <strong>Email</strong>
                        <a href="mailto:baksocapgala@gmail.com">baksocapgala@gmail.com</a>
                    </div>
                </div>
                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong>Jam Buka</strong>
                        <span>Senin : Libur</span>
                        <span>Selasa - Kamis: 11.00 - 20.00 WIB</span>
                        <span>Jumat & Sabtu: 11.30 - 21.00 WIB</span>
                        <span>Minggu: 11.00 - 20.00 WIB</span>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.tab-button');
        const contents = document.querySelectorAll('.location-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const target = document.querySelector(tab.dataset.target);
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                contents.forEach(c => c.classList.remove('active'));
                target.classList.add('active');
            });
        });
    });
</script>
@endpush