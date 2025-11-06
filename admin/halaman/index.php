<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak!'); document.location.href='../login/login.php';</script>";
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';

// Router halaman
 $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
 $allowed_pages = ['dashboard', 'kelola_pemesanan', 'kelola_tiket','kelola_kategori','kelola_user', 'kelola_galeri', 'laporan','laporan_stok','input_pembayaran', 'admin_profile', 'posko_register', 'statistik_posko', 'detail_pemesanan'];

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}
 $content_file = __DIR__ . '/' . $page . '.php';

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel - Labuan Bajo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

  <!-- NAVBAR -->
  <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="#">Labuan Bajo Admin</a>
    <div class="navbar-nav ms-auto">
      <div class="nav-item text-nowrap">
        <span class="nav-link px-3 text-white">Halo, <?= htmlspecialchars($_SESSION['username']); ?></span>
      </div>
    </div>
  </header>

  <!-- CONTAINER -->
  <div class="container-fluid">
    <div class="row">
      
      <!-- SIDEBAR -->
      <nav class="col-md-3 col-lg-2 d-md-block bg-dark text-white position-fixed vh-100 p-3">
        <ul class="nav nav-pills flex-column mb-auto">
          <li class="nav-item">
            <a href="?page=dashboard" class="nav-link <?= ($page=='dashboard')?'active text-dark bg-light':'text-white'; ?>">
              <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
          </li>
          <li><a href="?page=kelola_pemesanan" class="nav-link <?= ($page=='kelola_pemesanan')?'active text-dark bg-light':'text-white'; ?>"><i class="bi bi-list-check me-2"></i> Kelola Pemesanan</a></li>
          <li><a href="?page=input_pembayaran" class="nav-link <?= ($page=='input_pembayaran')?'active text-dark bg-light':'text-white'; ?>"><i class="bi bi-cash-coin me-2"></i> Input Pembayaran</a></li>
          <li><a href="?page=kelola_tiket" class="nav-link <?= ($page=='kelola_tiket')?'active text-dark bg-light':'text-white'; ?>"><i class="bi bi-ticket-perforated me-2"></i> Kelola Tiket</a></li>
          <li><a href="?page=kelola_kategori" class="nav-link <?= ($page=='kelola_kategori')?'active text-dark bg-light':'text-white'; ?>"><i class="bi bi-grid me-2"></i> Kelola Kategori</a></li>
          <li><a href="?page=kelola_user" class="nav-link <?= ($page=='kelola_user')?'active text-dark bg-light':'text-white'; ?>"><i class="bi bi-people me-2"></i> Kelola User</a></li>
          <li><a href="?page=kelola_galeri" class="nav-link <?= ($page=='kelola_galeri')?'active text-dark bg-light':'text-white'; ?>"><i class="bi bi-images me-2"></i> Kelola Galeri</a></li>
          <li><a href="?page=laporan_stok" class="nav-link <?= ($page=='laporan_stok')?'active text-dark bg-light':'text-white'; ?>"><i class="bi bi-file-earmark-bar-graph me-2"></i> Laporan Stok</a></li>
          <li><a href="?page=laporan" class="nav-link <?= ($page=='laporan')?'active text-dark bg-light':'text-white'; ?>"><i class="bi bi-bar-chart me-2"></i> Laporan</a></li>
          <li><a href="?page=statistik_posko" class="nav-link <?= ($page=='statistik_posko')?'active text-dark bg-light':'text-white'; ?>"><i class="bi bi-graph-up me-2"></i> Statistik Posko</a></li>
        </ul>

        <hr class="text-secondary">

        <ul class="nav flex-column">
          <li><a href="?page=admin_profile" class="nav-link <?= ($page=='admin_profile')?'active text-dark bg-light':'text-white'; ?>"><i class="bi bi-person-circle me-2"></i> Profile Admin</a></li>
          <li><a href="?page=posko_register" class="nav-link <?= ($page=='posko_register')?'active text-dark bg-light':'text-white'; ?>"><i class="bi bi-person-circle me-2"></i> Register posko</a></li>
          <li><a href="../login/logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
        </ul>
      </nav>

      <!-- MAIN CONTENT -->
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 offset-md-3 offset-lg-2 py-4">
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