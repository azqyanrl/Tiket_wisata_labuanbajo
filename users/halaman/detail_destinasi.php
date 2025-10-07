<?php
// ====== INCLUDE WAJIB (JANGAN DIUBAH) ======
include '../../database/konek.php';
include "session_cek.php";
include '../../includes/navbar.php';
include '../../includes/boot.php';

// ====== AMBIL ID DARI URL ======
$paket_wisata_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($paket_wisata_id <= 0) {
    echo "<div class='container my-5'><div class='alert alert-danger'>ID destinasi tidak valid.</div></div>";
    include '../../includes/footer.php';
    exit;
}

// ====== AMBIL DATA PAKET WISATA DARI DATABASE ======
$query_paket = $konek->prepare("SELECT * FROM tiket WHERE id = ?");
$query_paket->bind_param("i", $paket_wisata_id);
$query_paket->execute();
$hasil_paket = $query_paket->get_result();
$data_paket = $hasil_paket->fetch_assoc();
$query_paket->close();

if (!$data_paket) {
    echo "<div class='container my-5'><div class='alert alert-warning'>Data destinasi tidak ditemukan.</div></div>";
    include '../../includes/footer.php';
    exit;
}
?>

<!-- ====== HERO SECTION (TIDAK DIUBAH TAMPILANNYA) ====== -->
<section class="hero-section" style="height: 60vh; position: relative; overflow: hidden;">
  <img src="../../assets/images/<?= htmlspecialchars($data_paket['gambar']); ?>" 
       alt="<?= htmlspecialchars($data_paket['nama_paket']); ?>" 
       style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
  <div class="hero-overlay" style="position: absolute; inset: 0; background: rgba(0, 0, 0, 0.4); display: flex; align-items: center; justify-content: center;">
    <h1 class="text-white fw-bold display-5"><?= htmlspecialchars($data_paket['nama_paket']); ?></h1>
  </div>
</section>

<!-- ====== DETAIL DESTINASI & FORM BOOKING ====== -->
<div class="container my-5">
  <div class="row">
    <!-- Kolom Deskripsi -->
    <div class="col-md-8">
      <h3 class="fw-bold"><?= htmlspecialchars($data_paket['nama_paket']); ?></h3>
      <p><?= nl2br(htmlspecialchars($data_paket['deskripsi'])); ?></p>

      <ul class="list-unstyled">
        <li><strong>Durasi:</strong> <?= htmlspecialchars($data_paket['durasi']); ?></li>
        <li><strong>Kategori:</strong> <?= htmlspecialchars($data_paket['kategori']); ?></li>
        <li><strong>Harga:</strong> Rp <?= number_format($data_paket['harga'], 0, ',', '.'); ?></li>
      </ul>
    </div>

    <!-- Kolom Booking -->
    <div class="col-md-4">
      <div class="card border-0 shadow-sm p-3">
        <h5 class="text-center mb-3">Form Booking</h5>

        <form action="pesan_tiket.php" method="get">
          <input type="hidden" name="id_paket_wisata" value="<?= $data_paket['id']; ?>">

          <div class="mb-3">
            <label for="tanggal_kunjungan_user" class="form-label">Tanggal Kunjungan</label>
            <input type="date" name="tanggal_kunjungan_user" id="tanggal_kunjungan_user" class="form-control" required min="<?= date('Y-m-d'); ?>">
          </div>

          <div class="mb-3">
            <label for="jumlah_tiket_dipesan" class="form-label">Jumlah Tiket</label>
            <input type="number" name="jumlah_tiket_dipesan" id="jumlah_tiket_dipesan" class="form-control" value="1" min="1" required>
          </div>

          <button type="submit" class="btn btn-primary w-100">Booking Sekarang</button>
        </form>

        <small class="text-muted d-block text-center mt-2">
          Pembayaran dilakukan langsung kepada admin (offline).
        </small>
      </div>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>
