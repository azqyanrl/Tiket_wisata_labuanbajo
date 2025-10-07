<?php
include '../../database/konek.php';
include "session_cek.php";
include '../../includes/navbar.php';
include '../../includes/boot.php';
?>
 <!-- Hero Section -->
  <div style="background:linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('../../assets/images/hero/bermuda.jpg'); 
              background-size:cover; background-position:center; color:white; padding:100px 0; text-align:center; margin-bottom:10px;">
    <div class="container">
      <h1 style="font-size:3rem; font-weight:700; margin-bottom:20px;">Jelajahi Keindahan Labuan Bajo</h1>
      <p style="font-size:1.2rem; margin-bottom:30px;">Temukan pengalaman tak terlupakan dengan paket wisata terbaik kami</p>
      <a href="destinasi.php" class="btn btn-primary btn-lg" style="border-radius:30px; font-weight:600; padding:10px 25px;"> Pesan Sekarang</a>
    </div>
  </div>
<div class="container my-5">
  <h3 class="mb-4 text-center">Selamat Datang di Labuan Bajo</h3>
  <div class="row">
    <?php
    // Ambil data tiket yang aktif
    $query = "SELECT * FROM tiket WHERE status='aktif' ORDER BY created_at DESC LIMIT 6";
    $result = $konek->query($query);

    if ($result && $result->num_rows > 0) {
      while ($data = $result->fetch_assoc()) {
        ?>
        <div class="col-md-4 mb-4">
          <div class="card h-100 shadow-sm border-0">
            <img src="../../assets/images/<?= htmlspecialchars($data['gambar']); ?>" class="card-img-top" alt="<?= htmlspecialchars($data['nama_paket']); ?>">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($data['nama_paket']); ?></h5>
              <p class="text-muted mb-1"><?= htmlspecialchars($data['durasi']); ?></p>
              <p class="fw-bold text-primary">Rp <?= number_format($data['harga'], 0, ',', '.'); ?></p>
              <a href="detail_destinasi.php?id=<?= $data['id']; ?>" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
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



