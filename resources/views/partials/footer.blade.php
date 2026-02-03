{{-- File: resources/views/partials/footer.blade.php --}}
<footer>
    <div class="container">
        <div class="footer-cols">
            <div class="footer-col">
                <h3>Bakso Gala</h3>
                <p>Bakso Gala hadir untuk kelezatan sejati. Nikmati hidangan bakso otentik yang dibuat dengan cinta dan resep rahasia keluarga.</p>
            </div>
            <div class="footer-col">
                <h3>Informasi</h3>
                <ul>
                    {{-- Perhatikan cara penulisan link di Laravel --}}
                    <li><a href="{{ url('/tentang-kami') }}">Tentang Kami</a></li>
                    <li><a href="{{ url('/menu') }}">Menu</a></li>
                    <li><a href="{{ url('/faq') }}">FAQ</a></li>
                    <li><a href="{{ url('/kebijakan-privasi') }}">Kebijakan Privasi</a></li>
                    <li><a href="{{ url('/syarat-ketentuan') }}">Syarat & Ketentuan</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Hubungi Kami</h3>
                <p>Jl. Otto Iskandardinata No.115, Karanganyar, Kec. Subang, Kabupaten Subang, Jawa Barat 41211</p>
                <p>Telepon: (022) 123-xxx</p>
                <p>Email: info@baksogala.com</p>
                <p>Jam Buka: Senin - Jumat, 11.00 - 20.00 WIB</p>
                <p>Jam Buka: Sabtu & Minggu, 11.30 - 21.00 WIB</p>
            </div>
            <div class="footer-col">
                <h3>Ikuti Kami</h3>
                <div class="social-links">
                    <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a href="#" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" target="_blank"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2025 Bakso Gala. Semua Hak Dilindungi.</p>
        </div>
    </div>
</footer>