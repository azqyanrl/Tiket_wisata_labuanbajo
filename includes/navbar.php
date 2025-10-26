<?php
require_once __DIR__ . '/common.php';

$username = $_SESSION['username'] ?? null;
$photo = $_SESSION['profile_photo'] ?? null;
$initial = $username ? strtoupper(substr($username, 0, 1)) : '?';
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
        <li class="nav-item"><a class="nav-link" href="destinasi.php">Destinasi</a></li>
        <li class="nav-item"><a class="nav-link" href="galeri.php">Galeri</a></li>
        <li class="nav-item"><a class="nav-link" href="riwayat.php">Riwayat</a></li>
    <?php if ($username): ?>
      <li class="nav-item">
        <a href="profile.php" class="nav-link p-0 ms-3">
          <?php if ($photo): ?>
            <img src="<?= '../../assets/images/profile/' . e($photo) ?>"
                 alt="avatar" class="rounded-circle" width="36" height="36" style="object-fit:cover;">
          <?php else: ?>
            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
                 style="width:36px; height:36px; font-weight:bold; font-size:16px;">
              <?= e($initial) ?>
            </div>a
          <?php endif; ?>
        </a>
      </li>
    <?php else: ?>
      <li class="nav-item"><a class="nav-link" href="../login/login.php">Login</a></li>
    <?php endif; ?>
  </ul>
</div>

  </div>
</nav>
