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
<?php
include '../../database/konek.php';
include "session_cek.php";
include '../../includes/navbar.php';
include '../../includes/boot.php';

// Logika pencarian
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
if ($cari != '') {
  $stmt = $konek->prepare("SELECT * FROM tiket WHERE status='aktif' AND nama_paket LIKE ?");
  $param = "%$cari%";
  $stmt->bind_param("s", $param);
  $stmt->execute();
  $result = $stmt->get_result();
} else {
  $result = $konek->query("SELECT * FROM tiket WHERE status='aktif' ORDER BY created_at DESC");
}
?>

<div class="container my-5">
  <h3 class="text-center mb-4">Destinasi Wisata</h3>

  <!-- Form Search -->
  <form class="d-flex justify-content-center mb-4" method="get" action="">
    <input type="text" name="cari" class="form-control w-50 me-2" placeholder="Cari destinasi..." value="<?= htmlspecialchars($cari); ?>">
    <button type="submit" class="btn btn-primary">Cari</button>
  </form>

  <div class="row">
    <?php
    if ($result && $result->num_rows > 0) {
      while ($data = $result->fetch_assoc()) {
        ?>
        <div class="col-md-4 mb-4">
          <div class="card h-100 shadow-sm border-0">
            <img src="../../assets/images/<?= htmlspecialchars($data['gambar']); ?>" class="card-img-top" alt="<?= htmlspecialchars($data['nama_paket']); ?>">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($data['nama_paket']); ?></h5>
              <p class="text-muted small"><?= htmlspecialchars(substr($data['deskripsi'], 0, 100)); ?>...</p>
              <p class="fw-bold text-primary">Rp <?= number_format($data['harga'], 0, ',', '.'); ?></p>
            </div>
            <div class="card-footer bg-white text-end border-0">
              <a href="detail_destinasi.php?id=<?= $data['id']; ?>" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
            </div>
          </div>
        </div>
        <?php
      }
    } else {
      echo "<div class='col-12 text-center text-muted'>Tidak ada destinasi ditemukan.</div>";
    }
    ?>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>

