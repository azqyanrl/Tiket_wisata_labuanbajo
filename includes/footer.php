<?php 
// Optional: session check (hapus jika sudah ada di file lain)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header('Location: ../users/login/login.php');
    exit();
}
include __DIR__ . '/../users/halaman/session_cek.php';
?>

<!-- Bootstrap CSS & Icons (hapus jika sudah ada di layout utama) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
  /* Soft dark theme */
  .footer-soft-dark {
    background: linear-gradient(180deg, #1e2428 0%, #252b2f 100%);
    color: #d7dfe6;
    padding: 60px 0 30px;
    margin-top: 20px;
  }
  .footer-soft-dark a { color: #bfc9d1; text-decoration: none; transition: color .15s ease; }
  .footer-soft-dark a:hover { color: #ffffff; text-decoration: none; }

  .footer-soft-dark h5 { color: #f1f6f9; }
  .footer-soft-dark .muted { color: #98a3ad; }

  /* Modal soft dark */
  .modal-soft-dark .modal-content {
    background: linear-gradient(180deg, #2a3134, #23292d);
    color: #e6eef5;
    border: 1px solid rgba(255,255,255,0.04);
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.6);
  }
  .modal-soft-dark .modal-header {
    border-bottom: 1px solid rgba(255,255,255,0.03);
    padding: 1rem 1.25rem;
  }
  .modal-soft-dark .modal-title { font-weight: 600; color: #f8fbfc; }
  .modal-soft-dark .modal-body { color: #dce7ef; padding: 1rem 1.25rem; max-height: 55vh; overflow-y: auto; }

  hr.footer-divider { border-color: rgba(255,255,255,0.06); margin: 30px 0; }

  @media (max-width: 576px) {
    .footer-soft-dark { padding: 40px 12px; }
  }
</style>

<footer class="footer-soft-dark text-white">
  <div class="container">
    <div class="row">
      <div class="col-lg-4 mb-4">
        <h5 style="font-weight: 700; margin-bottom: 20px; font-size: 1.5rem;">
          <i class="bi bi-geo-alt-fill me-2"></i>Tiket Labuan Bajo
        </h5>
        <p class="muted" style="margin-bottom: 20px;">
          Platform terpercaya untuk pemesanan tiket perjalanan ke Labuan Bajo dan destinasi sekitarnya.
        </p>
        <div class="d-flex">
          <a href="#" class="me-3" style="font-size: 1.2rem;"><i class="bi bi-facebook"></i></a>
          <a href="#" class="me-3" style="font-size: 1.2rem;"><i class="bi bi-instagram"></i></a>
          <a href="#" class="me-3" style="font-size: 1.2rem;"><i class="bi bi-twitter"></i></a>
          <a href="#" style="font-size: 1.2rem;"><i class="bi bi-youtube"></i></a>
        </div>
      </div>

      <div class="col-lg-2 col-md-6 mb-4">
        <h5 style="font-weight: 600; margin-bottom: 20px;">Navigasi</h5>
        <ul class="list-unstyled">
          <li class="mb-2"><a href="../halaman/index.php">Beranda</a></li>
          <li class="mb-2"><a href="../halaman/destinasi.php">Destinasi</a></li>
          <li class="mb-2"><a href="../halaman/galeri.php">Galeri</a></li>
          <li class="mb-2"><a href="../halaman/riwayat.php">Riwayat</a></li>
          <li><a href="#" data-bs-toggle="modal" data-bs-target="#kontakModal">Kontak</a></li>
        </ul>
      </div>

      <div class="col-lg-2 col-md-6 mb-4">
        <h5 style="font-weight: 600; margin-bottom: 20px;">Informasi</h5>
        <ul class="list-unstyled">
          <li class="mb-2"><a href="#" data-bs-toggle="modal" data-bs-target="#tentangModal">Tentang Kami</a></li>
          <li class="mb-2"><a href="#" data-bs-toggle="modal" data-bs-target="#syaratModal">Syarat & Ketentuan</a></li>
          <li class="mb-2"><a href="#" data-bs-toggle="modal" data-bs-target="#kebijakanModal">Kebijakan Privasi</a></li>
          <li><a href="#" data-bs-toggle="modal" data-bs-target="#karirModal">Karir</a></li>
        </ul>
      </div>

      <div class="col-lg-4 mb-4">
        <h5 style="font-weight: 600; margin-bottom: 20px;">Kontak Kami</h5>
        <ul class="list-unstyled">
          <li class="mb-3 d-flex align-items-center"><i class="bi bi-geo-alt me-3" style="font-size:1.2rem;"></i>Jl. Soekarno Hatta No. 88, Labuan Bajo, NTT</li>
          <li class="mb-3 d-flex align-items-center"><i class="bi bi-telephone me-3" style="font-size:1.2rem;"></i>+62 838 1234 567</li>
          <li class="mb-3 d-flex align-items-center"><i class="bi bi-envelope me-3" style="font-size:1.2rem;"></i>azqyanurul27@gmail.com</li>
        </ul>
      </div>
    </div>
    <hr class="footer-divider">
    <div class="text-center muted" style="font-size:0.9rem;">
      <p>&copy; 2025 Tiket Labuan Bajo. Hak Cipta Dilindungi.</p>
    </div>
  </div>

  <!-- MODALS -->
  <div class="modal fade modal-soft-dark" id="tentangModal" tabindex="-1" aria-labelledby="tentangLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="tentangLabel">Tentang Kami</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Tiket Labuan Bajo adalah platform pemesanan tiket terpercaya untuk destinasi wisata terbaik di Nusa Tenggara Timur.
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade modal-soft-dark" id="syaratModal" tabindex="-1" aria-labelledby="syaratLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="syaratLabel">Syarat & Ketentuan</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Pengguna wajib memastikan data pemesanan benar dan mengikuti aturan yang berlaku di tempat wisata.
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade modal-soft-dark" id="kebijakanModal" tabindex="-1" aria-labelledby="kebijakanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="kebijakanLabel">Kebijakan Privasi</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Kami melindungi data pengguna dan tidak membagikan informasi pribadi kepada pihak ketiga tanpa izin.
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade modal-soft-dark" id="karirModal" tabindex="-1" aria-labelledby="karirLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="karirLabel">Karir</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Bergabunglah dengan tim kami untuk mengembangkan pariwisata Labuan Bajo yang berkelanjutan dan inovatif.
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade modal-soft-dark" id="kontakModal" tabindex="-1" aria-labelledby="kontakLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="kontakLabel">Kontak Kami</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Hubungi kami melalui email di <strong>azqyanurul27@gmail.com</strong> atau telepon <strong>+62 838 1234 567</strong>.
        </div>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap JS (wajib untuk modal berfungsi) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
