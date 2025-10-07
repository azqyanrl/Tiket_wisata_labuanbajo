<?php
// ====== INCLUDE WAJIB (JANGAN DIUBAH) ======
include '../../database/konek.php';
include "session_cek.php";
include '../../includes/navbar.php';
include '../../includes/boot.php';

// ====== CEK LOGIN USER ======
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location='../login/login.php';</script>";
    exit;
}

$id_pengguna = intval($_SESSION['user_id']);

// ====== UPDATE OTOMATIS: BOOKING HANGUS JIKA LEWAT TANGGAL KUNJUNGAN ======
$query_update_status = $konek->prepare("
    UPDATE pemesanan 
    SET status = 'batal' 
    WHERE user_id = ? 
    AND status = 'pending' 
    AND tanggal_kunjungan < CURDATE()
");
$query_update_status->bind_param("i", $id_pengguna);
$query_update_status->execute();
$query_update_status->close();

// ====== AMBIL DATA RIWAYAT BOOKING USER ======
$query_riwayat = $konek->prepare("
    SELECT pemesanan.kode_booking,
           pemesanan.tanggal_kunjungan,
           pemesanan.jumlah_tiket,
           pemesanan.total_harga,
           pemesanan.status,
           pemesanan.created_at,
           tiket.nama_paket,
           tiket.gambar
    FROM pemesanan
    JOIN tiket ON pemesanan.tiket_id = tiket.id
    WHERE pemesanan.user_id = ?
    ORDER BY pemesanan.created_at DESC
");
$query_riwayat->bind_param("i", $id_pengguna);
$query_riwayat->execute();
$hasil_riwayat = $query_riwayat->get_result();
?>

<!-- ====== TAMPILAN (TIDAK DIUBAH) ====== -->
<div class="container my-5">
  <h3 class="text-center mb-4 fw-bold">Riwayat Booking Anda</h3>

  <div class="table-responsive">
    <table class="table table-bordered text-center align-middle">
      <thead class="table-primary">
        <tr>
          <th>Kode Booking</th>
          <th>Paket Wisata</th>
          <th>Tanggal Kunjungan</th>
          <th>Jumlah Tiket</th>
          <th>Total Harga</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($hasil_riwayat->num_rows > 0): ?>
          <?php while ($data_booking = $hasil_riwayat->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($data_booking['kode_booking']); ?></td>
              <td>
                <div class="d-flex align-items-center justify-content-center gap-2">
                  <?php if (!empty($data_booking['gambar'])): ?>
                    <img src="../../assets/images/<?= htmlspecialchars($data_booking['gambar']); ?>" 
                         alt="<?= htmlspecialchars($data_booking['nama_paket']); ?>" 
                         style="width: 60px; height: 45px; object-fit: cover; border-radius: 4px;">
                  <?php endif; ?>
                  <span><?= htmlspecialchars($data_booking['nama_paket']); ?></span>
                </div>
              </td>
              <td><?= htmlspecialchars($data_booking['tanggal_kunjungan']); ?></td>
              <td><?= intval($data_booking['jumlah_tiket']); ?></td>
              <td>Rp <?= number_format($data_booking['total_harga'], 0, ',', '.'); ?></td>
              <td>
                <?php if ($data_booking['status'] == 'pending'): ?>
                  <span class="badge bg-warning text-dark">Menunggu</span>
                <?php elseif ($data_booking['status'] == 'batal'): ?>
                  <span class="badge bg-danger">Batal</span>
                <?php else: ?>
                  <span class="badge bg-success">Berhasil</span>
                <?php endif; ?>
              </td>
              <td>
                <a href="cetak_booking.php?kode_booking=<?= urlencode($data_booking['kode_booking']); ?>" 
                   class="btn btn-sm btn-success">Cetak</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="text-muted">Belum ada data booking.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>
