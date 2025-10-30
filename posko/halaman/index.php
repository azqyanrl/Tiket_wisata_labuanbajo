<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'posko') {
    $_SESSION['error_message'] = 'Akses ditolak!';
    header('Location: login/login.php');
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';

// Pastikan lokasi tersedia
 $lokasi = $_SESSION['lokasi'] ?? '';

// Router halaman
 $page = isset($_GET['page']) ? $_GET['page'] : 'posko_dashboard';
 $allowed_pages = ['posko_dashboard', 'laporan_posko', 'verifikasi_tiket', 'cari_tiket'];

if (!in_array($page, $allowed_pages)) {
    $page = 'posko_dashboard';
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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <style>
    .sidebar {
      height: calc(100vh - 56px);
      overflow-y: auto;
    }
    .main-content {
      margin-top: 56px;
    }
    .no-print {
      @media print {
        display: none !important;
      }
    }
  </style>
</head>
<body class="bg-light">

  <!-- NAVBAR -->
  <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="#">Labuan Bajo Admin Posko</a>
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
      <nav class="col-md-3 col-lg-2 d-md-block bg-dark text-white sidebar position-fixed p-3">
        <ul class="nav nav-pills flex-column mb-auto">
          <li class="nav-item">
            <a href="?page=posko_dashboard" class="nav-link <?= ($page=='posko_dashboard')?'active text-dark bg-light':'text-white'; ?>">
              <i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
          </li>
          <li><a href="?page=verifikasi_tiket" class="nav-link <?= ($page=='verifikasi_tiket')?'active text-dark bg-light':'text-white'; ?>"><i class="bi bi-list-check me-2"></i> Verifikasi Tiket</a></li>
          <li><a href="?page=cari_tiket" class="nav-link <?= ($page=='cari_tiket')?'active text-dark bg-light':'text-white'; ?>"><i class="bi bi-search me-2"></i> Cari Tiket</a></li>
          <li><a href="?page=laporan_posko" class="nav-link <?= ($page=='laporan_posko')?'active text-dark bg-light':'text-white'; ?>"><i class="bi bi-bar-chart me-2"></i> Laporan</a></li>
          <li><a href="login/logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
        </ul>
      </nav>

      <!-- MAIN CONTENT -->
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 offset-md-3 offset-lg-2 main-content py-4">
        <!-- Notifikasi -->
        <?php if (isset($_SESSION['success_message'])): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
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
</body>
</html>