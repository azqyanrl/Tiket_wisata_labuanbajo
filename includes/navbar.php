<?php
require_once __DIR__ . '/common.php';

$username = $_SESSION['username'] ?? null;
?>

<nav class="navbar navbar-expand-lg fixed-top navbar-dark"
     style="background:rgba(0,0,0,0.19); padding:10px 0; transition:all 0.3s; z-index:9999;">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php" style="font-size:22px;">LabuanBajoTrip</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
        <li class="nav-item"><a class="nav-link" href="destinasi.php">Paket Wisata</a></li>
        <li class="nav-item"><a class="nav-link" href="galeri.php">Galeri</a></li>
        <li class="nav-item"><a class="nav-link" href="riwayat.php">Riwayat</a></li>

        <?php if ($username): ?>
          <li class="nav-item">
            <a href="profile.php" class="nav-link fw-semibold ms-2 text-light">
              <i class="bi bi-person-circle me-1"></i> Profile
            </a>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="../../login/login.php">Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
