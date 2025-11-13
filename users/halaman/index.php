<?php

include '../../database/konek.php';
include "session_cek.php"; // File ini untuk mengecek session, misalnya memuat navbar yang sesuai
include '../../includes/navbar.php';
include '../../includes/boot.php'; // Saya asumsikan ini file untuk Bootstrap CSS/JS
?>
<style>
     /* Wildlife Section */
    .wildlife-section {
      padding: 80px 0;
      background: var(--light-bg);
    }

    .wildlife-card {
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      transition: all 0.4s ease;
      height: 100%;
      border: none;
    }

    .wildlife-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .wildlife-img {
      height: 250px;
      object-fit: cover;
      transition: all 0.5s ease;
    }

    .wildlife-card:hover .wildlife-img {
      transform: scale(1.05);
    }

    .wildlife-title {
      color: var(--primary-color);
      font-weight: 700;
    }

    .wildlife-subtitle {
      color: #64748b;
      font-style: italic;
    }
    /* Destinations Section */
.destinations-section {
  padding: 80px 0;
}

.destination-card {
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  height: 100%;
  border: none;
  position: relative;
}

.destination-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
}

.destination-img {
  height: 250px;
  object-fit: cover;
  transition: all 0.5s ease;
}

.destination-card:hover .destination-img {
  transform: scale(1.05);
}

.destination-price {
  font-weight: 700;
  color: #1e40af; /* secondary color */
  font-size: 1.5rem;
}

.btn-destination {
  background: linear-gradient(135deg, #0ea5e9, #0284c7); /* primary gradient */
  border: none;
  border-radius: 30px;
  padding: 10px 25px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-destination:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(14, 165, 233, 0.3);
}

.features-list small {
  display: block;
  margin-bottom: 3px;
}

</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
      AOS.init({ duration: 1000, once: true });
    </script>

</head>
<body>
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
<!-- Destinations Section - Tiket -->
<section id="destinations" class="destinations-section">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <h2 class="fw-bold mb-3">Paket Wisata </h2>
      <p class="text-muted fs-5">Pilih paket terbaik untuk petualangan impian Anda</p>
    </div>

    <div class="row">
      <?php
      include '../../database/konek.php';
      $query = "SELECT t.*, k.nama as kategori_nama, l.nama_lokasi 
                FROM tiket t 
                LEFT JOIN kategori k ON t.kategori_id = k.id 
                LEFT JOIN lokasi l ON t.lokasi = l.nama_lokasi
                WHERE t.status='aktif' 
                ORDER BY t.created_at DESC 
                LIMIT 6";
      $result = $konek->query($query);

      if ($result && $result->num_rows > 0) {
          while ($data = $result->fetch_assoc()) {
              // Ambil fasilitas dari database, maksimal 3 item
              $fasilitasArray = explode(',', $data['fasilitas']);
              $fasilitasToShow = array_slice($fasilitasArray, 0, 3);
      ?>
          <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo rand(100, 300); ?>">
            <div class="destination-card">
              <div class="position-relative overflow-hidden">
                <img src="../../assets/images/tiket/<?= htmlspecialchars($data['gambar']); ?>" 
                     class="destination-img w-100" 
                     alt="<?= htmlspecialchars($data['nama_paket']); ?>">
                <div class="position-absolute top-0 end-0 p-2">
                  <span class="badge bg-primary rounded-pill">
                    <?= htmlspecialchars($data['kategori_nama']); ?>
                  </span>
                </div>
              </div>

              <div class="card-body p-4">
                <h5 class="fw-semibold mb-2"><?= htmlspecialchars($data['nama_paket']); ?></h5>
                <p class="text-muted mb-2"><i class="fas fa-clock me-2"></i><?= htmlspecialchars($data['durasi']); ?></p>
                <p class="text-muted mb-3"><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($data['lokasi']); ?></p>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div class="destination-price">
                    Rp <?= number_format($data['harga'], 0, ',', '.'); ?>
                  </div>
                  <div class="text-muted">
                    <small><i class="fas fa-users me-1"></i><?= $data['stok']; ?> tersedia</small>
                  </div>
                </div>
                
                <div class="features-list mb-3">
                  <?php foreach ($fasilitasToShow as $fasilitas) { ?>
                    <small class="text-muted">
                      <i class="fas fa-check text-success me-1"></i> <?= htmlspecialchars(trim($fasilitas)); ?>
                    </small><br>
                  <?php } ?>
                </div>
                
                <a href="detail_destinasi.php?id=<?= $data['id']; ?>" class="btn btn-destination w-100 text-light">
                  <i class="fas fa-shopping-cart me-2"></i>Pesan Sekarang
                </a>
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

    <div class="text-center mt-5" data-aos="fade-up">
      <a href="destinasi.php" class="btn btn-outline-primary btn-lg rounded-pill px-5">
        Lihat Semua Paket <i class="fas fa-arrow-right ms-2"></i>
      </a>
    </div>
  </div>
</section>

    <!-- Wildlife Section -->
  <section class="wildlife-section">
    <div class="container">
      <div class="text-center mb-5" data-aos="fade-up">
        <h2 class="fw-bold mb-3 text-uppercase text-primary">Satwa Taman Nasional Komodo</h2>
        <p class="text-muted fs-5">Keanekaragaman fauna yang dilindungi di kawasan Taman Nasional Komodo</p>
      </div>

      <div class="row justify-content-center g-4">
        <!-- Biawak Komodo -->
        <div class="col-md-6 col-lg-5" data-aos="fade-up" data-aos-delay="100">
          <div class="wildlife-card">
            <img src="../../assets/images/bg/komodo.jpg" class="wildlife-img w-100" alt="Biawak Komodo">
            <div class="card-body p-4">
              <h4 class="text-center fw-bold wildlife-title">Biawak Komodo</h4>
              <h6 class="text-center fst-italic wildlife-subtitle mb-3">Varanus komodoensis</h6>
              <p class="text-justify">
                <strong>Biawak Komodo</strong> merupakan kadal terbesar yang ada di dunia. Reptil raksasa karnivora ini hidup alami di 
                Taman Nasional Komodo, Pulau Longos, dan sebagian lembah Pulau Flores. 
                Keberadaan dan kelestarian populasi biawak komodo di Taman Nasional Komodo dijaga ketat oleh 
                Balai Taman Nasional Komodo dengan melibatkan masyarakat setempat. 
                Wisatawan yang beruntung dapat mengamati biawak komodo secara langsung hanya dari beberapa resort 
                seperti Resort Loh Buaya (Pulau Rinca), Resort Loh Liang (Pulau Komodo), dan Resort Padar Selatan (Pulau Padar).
              </p>
            </div>
          </div>
        </div>

        <!-- Kakatua Kecil Jambul Kuning -->
        <div class="col-md-6 col-lg-5" data-aos="fade-up" data-aos-delay="200">
          <div class="wildlife-card">
            <img src="../../assets/images/bg/kakatua2.jpg" class="wildlife-img w-100" alt="Kakatua Kecil Jambul Kuning">
            <div class="card-body p-4">
              <h4 class="text-center fw-bold wildlife-title">Kakatua Kecil Jambul Kuning</h4>
              <h6 class="text-center fst-italic wildlife-subtitle mb-3">Cacatua sulphurea</h6>
              <p class="text-justify">
                <strong>Kakatua kecil jambul kuning</strong> hidup alami di Taman Nasional Komodo, 
                utamanya di Pulau Rinca, Pulau Bero, dan Pulau Komodo. 
                Burung ini hidup berkoloni dan memiliki populasi yang cukup banyak di dalam kawasan taman nasional. 
                Penggunaan tempat tinggal yang sama dengan biawak komodo membuat populasinya stabil dari tahun ke tahun.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>
  </section>
</body>
</html>
<?php include '../../includes/footer.php'; ?>