<?php
// ====== INCLUDE WAJIB (JANGAN DIUBAH) ======
include '../../database/konek.php';
include "session_cek.php";
include '../../includes/navbar.php';
include '../../includes/boot.php';

// ====== AMBIL KODE BOOKING DARI URL ======
$kode_booking_user = isset($_GET['kode_booking']) ? trim($_GET['kode_booking']) : '';

if (empty($kode_booking_user)) {
    echo "<div class='container my-5'><div class='alert alert-danger text-center'>Kode booking tidak ditemukan.</div></div>";
    include '../../includes/footer.php';
    exit;
}

// ====== AMBIL DATA BOOKING DARI DATABASE ======
$query_booking = $konek->prepare("
    SELECT 
        pemesanan.kode_booking,
        pemesanan.tanggal_kunjungan,
        pemesanan.jumlah_tiket,
        pemesanan.total_harga,
        pemesanan.status,
        pemesanan.metode_pembayaran,
        pemesanan.created_at,
        tiket.nama_paket,
        tiket.harga,
        tiket.durasi,
        users.nama_lengkap,
        users.email
    FROM pemesanan
    JOIN tiket ON pemesanan.tiket_id = tiket.id
    JOIN users ON pemesanan.user_id = users.id
    WHERE pemesanan.kode_booking = ?
    LIMIT 1
");
$query_booking->bind_param("s", $kode_booking_user);
$query_booking->execute();
$hasil_booking = $query_booking->get_result();
$data_booking = $hasil_booking->fetch_assoc();
$query_booking->close();

if (!$data_booking) {
    echo "<div class='container my-5'><div class='alert alert-warning text-center'>Data booking tidak ditemukan.</div></div>";
    include '../../includes/footer.php';
    exit;
}

// ====== CEK KEPEMILIKAN BOOKING (HANYA PEMESAN YANG BOLEH CETAK) ======
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $data_booking['user_id']) {
    echo "<div class='container my-5'><div class='alert alert-danger text-center'>Anda tidak memiliki izin untuk melihat bukti booking ini.</div></div>";
    include '../../includes/footer.php';
    exit;
}
?>

<!-- ====== TAMPILAN (TIDAK DIUBAH) ====== -->
<div class="container my-5">
  <div class="card p-4 shadow-lg border-0">
    <h4 class="text-center mb-4 fw-bold">Bukti Booking Tiket</h4>

    <div class="row mb-2">
      <div class="col-md-6"><strong>Kode Booking</strong></div>
      <div class="col-md-6"><?= htmlspecialchars($data_booking['kode_booking']); ?></div>
    </div>

    <div class="row mb-2">
      <div class="col-md-6"><strong>Nama Pemesan</strong></div>
      <div class="col-md-6"><?= htmlspecialchars($data_booking['nama_lengkap']); ?></div>
    </div>

    <div class="row mb-2">
      <div class="col-md-6"><strong>Email</strong></div>
      <div class="col-md-6"><?= htmlspecialchars($data_booking['email']); ?></div>
    </div>

    <div class="row mb-2">
      <div class="col-md-6"><strong>Nama Paket Wisata</strong></div>
      <div class="col-md-6"><?= htmlspecialchars($data_booking['nama_paket']); ?></div>
    </div>

    <div class="row mb-2">
      <div class="col-md-6"><strong>Durasi Wisata</strong></div>
      <div class="col-md-6"><?= htmlspecialchars($data_booking['durasi']); ?></div>
    </div>

    <div class="row mb-2">
      <div class="col-md-6"><strong>Tanggal Kunjungan</strong></div>
      <div class="col-md-6"><?= htmlspecialchars($data_booking['tanggal_kunjungan']); ?></div>
    </div>

    <div class="row mb-2">
      <div class="col-md-6"><strong>Jumlah Tiket</strong></div>
      <div class="col-md-6"><?= intval($data_booking['jumlah_tiket']); ?></div>
    </div>

    <div class="row mb-2">
      <div class="col-md-6"><strong>Total Harga</strong></div>
      <div class="col-md-6">Rp <?= number_format($data_booking['total_harga'], 0, ',', '.'); ?></div>
    </div>

    <div class="row mb-2">
      <div class="col-md-6"><strong>Metode Pembayaran</strong></div>
      <div class="col-md-6 text-capitalize"><?= htmlspecialchars($data_booking['metode_pembayaran']); ?></div>
    </div>

    <div class="row mb-2">
      <div class="col-md-6"><strong>Status</strong></div>
      <div class="col-md-6 text-capitalize"><?= htmlspecialchars($data_booking['status']); ?></div>
    </div>

    <hr class="my-3">

    <p class="text-center text-muted mb-4">
      Silakan tunjukkan bukti booking ini kepada admin saat melakukan pembayaran.
    </p>

    <div class="text-center">
      <button class="btn btn-primary" onclick="window.print()">Cetak Bukti Booking</button>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>
