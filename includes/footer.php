<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header('Location: ../users/login/login.php');
    exit();
}
include __DIR__ . '/../users/halaman/session_cek.php';
?> 
    <footer class="text-white" style="background-color: #212529; padding: 60px 0 30px; margin-top:20px;">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 style="font-weight: 700; margin-bottom: 20px; font-size: 1.5rem;">
                        <i class="bi bi-geo-alt-fill" style="margin-right: 8px;"></i>Tiket Labuan Bajo
                    </h5>
                    <p style="color: #adb5bd; margin-bottom: 20px;">Platform terpercaya untuk pemesanan tiket perjalanan ke Labuan Bajo dan destinasi sekitarnya.</p>
                    <div class="d-flex">
                        <a href="#" class="text-white me-3" style="font-size: 1.2rem;"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white me-3" style="font-size: 1.2rem;"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white me-3" style="font-size: 1.2rem;"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white" style="font-size: 1.2rem;"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 style="font-weight: 600; margin-bottom: 20px;">Navigasi</h5>
                    <ul class="list-unstyled" style="padding: 0;">
                        <li class="mb-2"><a href="../admin/halaman/beranda.php" style="color: #adb5bd; text-decoration: none;">Beranda</a></li>
                        <li class="mb-2"><a href="../admin/halaman/destinasi.php" style="color: #adb5bd; text-decoration: none;">Destinasi</a></li>
                        <li class="mb-2"><a href="../admin/halaman/destinasi.php" style="color: #adb5bd; text-decoration: none;"> Galeri</a></li>
                        <li class="mb-2"><a href="../admin/halaman/riwayat.php" style="color: #adb5bd; text-decoration: none;">Riwayat</a></li>
                        <li><a href="#" style="color: #adb5bd; text-decoration: none;">Kontak</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 style="font-weight: 600; margin-bottom: 20px;">Informasi</h5>
                    <ul class="list-unstyled" style="padding: 0;">
                        <li class="mb-2"><a href="#" style="color: #adb5bd; text-decoration: none;">Tentang Kami</a></li>
                        <li class="mb-2"><a href="#" style="color: #adb5bd; text-decoration: none;">Syarat & Ketentuan</a></li>
                        <li class="mb-2"><a href="#" style="color: #adb5bd; text-decoration: none;">Kebijakan Privasi</a></li>
                        <li><a href="#" style="color: #adb5bd; text-decoration: none;">Karir</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <h5 style="font-weight: 600; margin-bottom: 20px;">Kontak Kami</h5>
                    <ul class="list-unstyled" style="padding: 0;">
                        <li class="mb-3 d-flex align-items-center" style="color: #adb5bd;">
                            <i class="bi bi-geo-alt me-3" style="font-size: 1.2rem;"></i>
                            <span>Jl. Soekarno Hatta No. 88, Labuan Bajo, NTT</span>
                        </li>
                        <li class="mb-3 d-flex align-items-center" style="color: #adb5bd;">
                            <i class="bi bi-telephone me-3" style="font-size: 1.2rem;"></i>
                            <span>+62 838 1234 567</span>
                        </li>
                        <li class="mb-3 d-flex align-items-center" style="color: #adb5bd;">
                            <i class="bi bi-envelope me-3" style="font-size: 1.2rem;"></i>
                            <span>azqyanurul27@gmail.com</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <hr style="border-color: #495057; margin: 30px 0;">
            
            <div class="text-center" style="color: #adb5bd; font-size: 0.9rem;">
                <p>&copy; 2025 Tiket Labuan Bajo. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>