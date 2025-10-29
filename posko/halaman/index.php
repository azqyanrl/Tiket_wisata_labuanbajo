<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login/login.php');
    exit;
}

if ($_SESSION['role'] !== 'posko') {
    $_SESSION['error_message'] = 'Akses ditolak! Harus login sebagai admin posko.';
    header('Location: login/login.php');
    exit;
}

$page = $_GET['page'] ?? 'dashboard';
$allowed_pages = ['dashboard', 'verifikasi_tiket', 'cari_tiket', 'laporan_posko'];
if (!in_array($page, $allowed_pages)) $page = 'dashboard';

include '../../database/konek.php';
include '../../includes/boot.php';
$lokasi = $_SESSION['lokasi'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sistem Posko - <?= htmlspecialchars($lokasi) ?></title>
</head>
<body>
<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
  <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="index.php">Sistem Posko</a>
  <button class="navbar-toggler d-md-none collapsed" type="button" data-bs-toggle="collapse" 
          data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" 
          aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="navbar-nav">
    <div class="nav-item text-nowrap">
      <a class="nav-link px-3" href="login/logout.php">Logout</a>
    </div>
  </div>
</header>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
      <div class="position-sticky pt-3">
        <div class="text-center mb-3">
          <span class="fw-bold text-primary"><?= htmlspecialchars($lokasi) ?></span>
        </div>
        <ul class="nav flex-column">
          <li class="nav-item">
            <a class="nav-link <?= $page == 'posko_dashboard' ? 'active text-primary fw-bold' : '' ?>" 
               href="index.php?page=dashboard">
              <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $page == 'verifikasi_tiket' ? 'active text-primary fw-bold' : '' ?>" 
               href="index.php?page=verifikasi_tiket">
              <i class="bi bi-ticket-perforated me-2"></i>Verifikasi Tiket
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $page == 'cari_tiket' ? 'active text-primary fw-bold' : '' ?>" 
               href="index.php?page=cari_tiket">
              <i class="bi bi-search me-2"></i>Cari Tiket
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $page == 'laporan_posko' ? 'active text-primary fw-bold' : '' ?>" 
               href="index.php?page=laporan_posko">
              <i class="bi bi-graph-up me-2"></i>Laporan
            </a>
          </li>
        </ul>
        <hr>
        <ul class="nav flex-column mb-2">
          <li class="nav-item">
            <a class="nav-link" href="#">
              <i class="bi bi-person-circle me-2"></i>Profil
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="login/logout.php">
              <i class="bi bi-box-arrow-right me-2"></i>Logout
            </a>
          </li>
        </ul>
      </div>
    </nav>

    <!-- Main content -->
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
      <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($_SESSION['success_message']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($_SESSION['error_message']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
      <?php endif; ?>

      <?php
      $page_file = 'pages/' . $page . '.php';
      if (file_exists($page_file)) {
          include $page_file;
      } else {
          echo '<div class="alert alert-danger">Halaman tidak ditemukan.</div>';
      }
      ?>
    </main>
  </div>
</div>

</body>
</html>
