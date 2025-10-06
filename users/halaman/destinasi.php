<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tiket Wisata - Labuan Bajo</title>
</head>

<body class="bg-light text-dark">

  <!-- Navbar -->
  <?php include '../../includes/navbar.php'; ?>
  <?php include '../../includes/boot.php'; ?>

  <!-- Hero Section -->
  <section class="text-center text-white bg-dark py-5" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1558979158-65a1eaa08691?auto=format&fit=crop&w=1350&q=80'); background-size: cover; background-position: center;">
    <div class="container py-5">
      <h1 class="display-4 fw-bold mb-3">Jelajahi Keindahan Labuan Bajo</h1>
      <p class="lead mb-4">Temukan pengalaman tak terlupakan dengan paket wisata terbaik kami</p>
      <a href="#tickets" class="btn btn-primary btn-lg rounded-pill fw-semibold px-4">
        <i class="bi bi-ticket-detailed me-2"></i>Lihat Paket Tiket
      </a>
    </div>
  </section>

  <!-- Main Content -->
  <main class="container my-5">
    <div class="mb-4">
      <h2 class="fw-bold border-bottom pb-2">Semua Paket Tiket</h2>
    </div>

    <!-- Filter Section -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <select class="form-select">
          <option selected>Semua Kategori</option>
          <option>Adventure</option>
          <option>Snorkeling</option>
          <option>Trekking</option>
          <option>Cultural</option>
        </select>
      </div>
      <div class="col-md-3">
        <select class="form-select">
          <option selected>Semua Durasi</option>
          <option>1 Hari</option>
          <option>2 Hari 1 Malam</option>
          <option>3 Hari 2 Malam</option>
        </select>
      </div>
      <div class="col-md-3">
        <select class="form-select">
          <option selected>Semua Harga</option>
          <option>Di bawah Rp 500.000</option>
          <option>Rp 500.000 - Rp 1.000.000</option>
          <option>Rp 1.000.000 - Rp 2.000.000</option>
          <option>Di atas Rp 2.000.000</option>
        </select>
      </div>
      <div class="col-md-3">
        <button class="btn btn-primary w-100 rounded-pill fw-semibold">
          <i class="bi bi-funnel me-1"></i> Terapkan Filter
        </button>
      </div>
    </div>

    <!-- Ticket Cards -->
    <div class="row" id="tickets">
      <?php 
              include '../../database/konek.php';
    $tampil =$konek->query("SELECT * FROM tiket");
    foreach ($tampil as $data){
        ?>
      <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
          <img src="<?= $data['gambar'] ?>" class="card-img-top" alt="gagal load image" style="height:300px; object-fit:cover;">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title fw-semibold"><?= $data['nama_paket'] ?></h5>
            <p class="card-text flex-grow-1"><?= $data['deskripsi'] ?></p>
            <div class="d-flex justify-content-between align-items-center mt-auto">
              <span class="fw-bold text-primary">Rp<?= number_format($data['harga'], 0, ',', '.') ?></span>
              <a href="detail_destinasi.php?id=<?= $data['id'] ?>" class="btn btn-outline-primary rounded-pill btn-sm">Detail</a>
            </div>
          </div>
        </div>
      </div>
      
<?php } ?>
    </div>
</body>
</html>
<!-- Footer -->
  <?php include '../../includes/footer.php'; ?>
