<?php
require_once __DIR__ . '/common.php';

$username = $_SESSION['username'] ?? null;
?>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

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



<a href="https://wa.me/6281234567890?text=Halo,%20saya%20tertarik%20dengan%20paket%20wisata%20Labuan%20Bajo" 
     class="whatsapp-float" target="_blank">
    <i class="fab fa-whatsapp"></i>
  </a>
<style>
  /* Floating WhatsApp Button */
    .whatsapp-float {
      position: fixed;
      bottom: 30px;
      right: 30px;
      background: #25d366;
      color: white;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 30px;
      box-shadow: 0 5px 20px rgba(37, 211, 102, 0.3);
      z-index: 1000;
      transition: all 0.3s ease;
    }

    .whatsapp-float:hover {
      transform: scale(1.1);
      box-shadow: 0 8px 30px rgba(37, 211, 102, 0.4);
    }
  </style>