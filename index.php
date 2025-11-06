<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Labuan Bajo Trip</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
</head>
<body style="font-family:'Poppins', sans-serif;">

  <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top navbar-dark" 
        style="background:rgba(0, 0, 0, 0); padding:15px 0; font-size:17px;">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#" style="font-size:22px;">LabuanBajoTrip</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link active" href="#">Beranda</a></li>
            <li class="nav-item"><a class="nav-link" href="#destinasi">Destinasi</a></li>
            <li class="nav-item">
            <a class="nav-link btn btn-primary text-white px-4 ms-3" href="users/login/login.php" 
                style="border-radius:25px; padding:8px 18px; font-size:15px;">
            Login
            </a>
            </li>
        </ul>
        </div>
    </div>
    </nav>


<div class="hero-section position-relative d-flex align-items-center vh-100">
    <!-- Background image -->
    <img src="assets/images/bg/padar3.jpg"
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
                <a href="#destinasi" class="btn btn-primary btn-lg rounded-pill px-4 fw-semibold">
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
        <img src="assets/uploads/komodo.jpg" 
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


<!-- Destinasi -->
<section id="destinasi" style="padding:60px 0; background:#f8f9fa;">
<div class="container my-5">
    <h3 class="mb-4 text-center">Paket Wisata</h3>
    <div class="row">
        <?php
        include 'database/konek.php';
        $query = "SELECT * FROM tiket WHERE status='aktif' ORDER BY created_at DESC LIMIT 3";
        $result = $konek->query($query);

        if ($result && $result->num_rows > 0) {
            while ($data = $result->fetch_assoc()) {
        ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <img src="assets/images/tiket/<?= htmlspecialchars($data['gambar']); ?>" class="card-img-top" alt="<?= htmlspecialchars($data['nama_paket']); ?>" style="height: 210px; width: 100%; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($data['nama_paket']); ?></h5>
                            <p class="text-muted mb-1"><?= htmlspecialchars($data['durasi']); ?></p>
                            <p class="fw-bold text-primary mt-auto">Rp <?= number_format($data['harga'], 0, ',', '.'); ?></p>
                            <a href="users/login/login.php?id=<?= $data['id']; ?>" class="btn btn-sm btn-outline-primary mt-2">Login</a>
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
</section>

  <!-- Footer -->
  <footer style="background:#111; color:white; text-align:center; padding:20px;">
    <p style="margin-bottom:10px;">&copy; 2025 LabuanBajoTrip. All Rights Reserved.</p>
    <div>
      <a href="#" style="color:white; margin:0 10px;"><i class="fab fa-facebook fa-lg"></i></a>
      <a href="#" style="color:white; margin:0 10px;"><i class="fab fa-instagram fa-lg"></i></a>
      <a href="#" style="color:white; margin:0 10px;"><i class="fab fa-twitter fa-lg"></i></a>
    </div>
  </footer>

</body>
</html>
