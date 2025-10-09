<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak!'); document.location.href='../login/login.php';</script>";
    exit;
}

include '../../database/konek.php';
include '../boot.php';

// Router: Tentukan halaman yang akan dimuat berdasarkan URL
 $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Daftar halaman yang diizinkan
 $allowed_pages = ['dashboard', 'kelola_pemesanan', 'kelola_tiket', 'kelola_user', 'kelola_galeri', 'laporan', 'input_pembayaran'];

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard'; // Kembali ke default
}

// --- PERUBAHAN PENTING DI SINI (DIKEMBALIKAN KE SEMULA) ---
// Path menuju file halaman (di folder yang sama dengan index.php)
 $content_file = __DIR__ . '/' . $page . '.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Labuan Bajo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 48px 0 0; }
        .sidebar-sticky { position: relative; top: 0; height: calc(100vh - 48px); padding-top: .5rem; overflow-x: hidden; overflow-y: auto; }
        .sidebar .nav-link { font-weight: 500; color: #fff; padding: 0.75rem 1rem; border-radius: 0.375rem; margin-bottom: 0.25rem; }
        .sidebar .nav-link:hover { color: #fff; background-color: rgba(255, 255, 255, .1); }
        .sidebar .nav-link.active { color: #0d6efd; background-color: #fff; }
        .main-content { margin-left: 240px; }
        @media (max-width: 767.98px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body>
<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
  <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="#">Labuan Bajo Admin</a>
  <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="navbar-nav">
    <div class="nav-item text-nowrap">
      <a class="nav-link px-3 text-white" href="#">Selamat datang, <?php echo $_SESSION['username']; ?></a>
    </div>
  </div>
</header>

<div class="container-fluid">
  <div class="row">
    <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
      <div class="position-sticky pt-3 sidebar-sticky">
        <ul class="nav flex-column">
          <li class="nav-item"><a class="nav-link <?php echo ($page == 'dashboard') ? 'active' : ''; ?>" href="?page=dashboard"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
          <li class="nav-item"><a class="nav-link <?php echo ($page == 'kelola_pemesanan') ? 'active' : ''; ?>" href="?page=kelola_pemesanan"><i class="bi bi-list-check me-2"></i> Kelola Pemesanan</a></li>
          <li class="nav-item"><a class="nav-link <?php echo ($page == 'input_pembayaran') ? 'active' : ''; ?>" href="?page=input_pembayaran"><i class="bi bi-cash-coin me-2"></i> Input Pembayaran</a></li>
          <li class="nav-item"><a class="nav-link <?php echo ($page == 'kelola_tiket') ? 'active' : ''; ?>" href="?page=kelola_tiket"><i class="bi bi-ticket-perforated me-2"></i> Kelola Tiket</a></li>
          <li class="nav-item"><a class="nav-link <?php echo ($page == 'kelola_user') ? 'active' : ''; ?>" href="?page=kelola_user"><i class="bi bi-people me-2"></i> Kelola Pengguna</a></li>
          <li class="nav-item"><a class="nav-link <?php echo ($page == 'kelola_galeri') ? 'active' : ''; ?>" href="?page=kelola_galeri"><i class="bi bi-images me-2"></i> Kelola Galeri</a></li>
          <li class="nav-item"><a class="nav-link <?php echo ($page == 'laporan') ? 'active' : ''; ?>" href="?page=laporan"><i class="bi bi-file-earmark-bar-graph me-2"></i> Laporan</a></li>
        </ul>
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase"><span>Pengaturan</span></h6>
        <ul class="nav flex-column mb-2">
          <li class="nav-item"><a class="nav-link text-danger" href="keluar.php"><i class="bi bi-box-arrow-right me-2"></i> Keluar</a></li>
        </ul>
      </div>
    </nav>
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
      <?php
      if (file_exists($content_file)) {
          include $content_file;
      } else {
          echo '<div class="alert alert-danger">Halaman tidak ditemukan.</div>';
      }
      ?>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>