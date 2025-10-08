<?php

include '../../database/konek.php';
include "session_cek.php"; // File ini untuk mengecek session, misalnya memuat navbar yang sesuai
include '../../includes/navbar.php';
include '../../includes/boot.php'; // Saya asumsikan ini file untuk Bootstrap CSS/JS
?>

<!-- Hero Section -->
<section class="text-center text-white bg-dark py-5" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1558979158-65a1eaa08691?auto=format&fit=crop&w=1350&q=80'); background-size: cover; background-position: center;">
    <div class="container py-5">
        <h1 class="display-4 fw-bold mb-3">Jelajahi Keindahan Labuan Bajo</h1>
        <p class="lead mb-4">Temukan pengalaman tak terlupakan dengan paket wisata terbaik kami</p>
        <a href="destinasi.php" class="btn btn-primary btn-lg rounded-pill fw-semibold px-4">
            <i class="bi bi-ticket-detailed me-2"></i>Lihat Paket Tiket
        </a>
    </div>
</section>
<div class="container my-5">
  <div class="card shadow-lg border-0 rounded-4" style="height: 400px; overflow: hidden;">
    <div class="row g-0 h-100">
      <!-- Gambar di kiri -->
      <div class="col-md-5 h-100">
        <img src="https://images.unsplash.com/photo-1590523278191-995cbcda646b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" 
             class="img-fluid h-100 rounded-start-4" 
             alt="Labuan Bajo"
             style="object-fit: cover; width: 100%; height: 100%;">
      </div>
      <!-- Teks di kanan -->
      <div class="col-md-7 d-flex align-items-center text-start">
        <div class="p-4">
          <h4 class="fw-bold mb-2">Labuan Bajo</h4>
          <p class="text-muted mb-3 fs-5">
            Labuan Bajo adalah kota kecil di ujung barat Pulau Flores, Nusa Tenggara Timur, yang kini menjadi destinasi wisata unggulan Indonesia sekaligus gerbang menuju Taman Nasional Komodo. Dikenal dengan panorama alamnya yang memukau, Labuan Bajo menawarkan keindahan pulau-pulau eksotis, lautan jernih dengan spot diving kelas dunia, hingga pemandangan savana dan sunset yang menawan. Selain sebagai rumah bagi komodo, kota ini juga menghadirkan kekayaan budaya lokal Flores, kuliner khas, serta pengalaman berlayar dengan kapal phinisi, menjadikannya salah satu ikon pariwisata Indonesia yang mendunia.
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="container my-5">
    <h3 class="mb-4 text-center">Paket Wisata Unggulan</h3>
    <div class="row">
        <?php
        // Ambil data tiket yang aktif
        $query = "SELECT * FROM tiket WHERE status='aktif' ORDER BY created_at DESC LIMIT 3";
        $result = $konek->query($query);

        if ($result && $result->num_rows > 0) {
            while ($data = $result->fetch_assoc()) {
        ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <img src="../../assets/images/<?= htmlspecialchars($data['gambar']); ?>" class="card-img-top" alt="<?= htmlspecialchars($data['nama_paket']); ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($data['nama_paket']); ?></h5>
                            <p class="text-muted mb-1"><?= htmlspecialchars($data['durasi']); ?></p>
                            <p class="fw-bold text-primary mt-auto">Rp <?= number_format($data['harga'], 0, ',', '.'); ?></p>
                            <a href="detail_destinasi.php?id=<?= $data['id']; ?>" class="btn btn-sm btn-outline-primary mt-2">Lihat Detail</a>
                        </div>
                    </div>
                </div>
        <?php
            }
        } else {
            echo "<div class='col-12 text-center text-muted'>Belum ada paket wisata tersedia.</div>";
        }
        ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>