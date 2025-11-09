<?php

include '../../database/konek.php';
include "session_cek.php"; // File ini untuk mengecek session, misalnya memuat navbar yang sesuai
include '../../includes/navbar.php';
include '../../includes/boot.php'; // Saya asumsikan ini file untuk Bootstrap CSS/JS
?>

<div class="hero-section position-relative d-flex align-items-center vh-100">
    <!-- Background image -->
    <img src="../../assets/images/bg/padar3.jpg"
         alt="Labuan Bajo"
         class="w-100 h-100 position-absolute top-0 start-0 object-fit-cover"
         style="z-index: -2;">

    <!-- Overlay gelap -->
    <div class="position-absolute top-0 start-0 w-100 h-100" style="z-index: -1; background: rgba(0, 0, 0, 0.51);"></div>

    <div class="container position-relative text-white">
        <div class="row">
            <div class="col-lg-6">
                <h1 class="fw-bold display-4 mb-4 lh-sm">Jelajahi Keindahan Labuan Bajo</h1>
                <p class="fs-5 mb-4 opacity-75">
                    Pesan tiket perjalanan Anda ke surga tersembunyi di Indonesia. 
                    Nikmati pengalaman tak terlupakan dengan pemandangan spektakuler dan budaya yang memukau.
                </p>
                <a href="destinasi.php" class="btn btn-primary btn-lg rounded-pill px-4 fw-semibold">
                    <i class="bi bi-search me-2"></i>Cari Tiket
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
  <div class="card shadow-lg border-0 rounded-4" style="height: 400px; overflow: hidden;">
    <div class="row g-0 h-100">
      <!-- Gambar di kiri -->
      <div class="col-md-5 h-100">
        <img src="../../assets/images/bg/komodo2.jpg" 
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
    <h3 class="mb-4 text-center">Paket Wisata</h3>
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
                        <img src="../../assets/images/tiket/<?= htmlspecialchars($data['gambar']); ?>" class="card-img-top" alt="<?= htmlspecialchars($data['nama_paket']); ?>" style="height: 210px; width: 100%; object-fit: cover;">
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