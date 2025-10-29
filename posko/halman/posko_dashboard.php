<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'posko') {
    $_SESSION['error_message'] = 'Akses ditolak! Harus login sebagai admin posko.';
    header('Location: login/login.php');
    exit;
}
include '../database/konek.php';
$lokasi = $_SESSION['lokasi'] ?? null;

$sql = "SELECT p.*, t.nama_paket, t.lokasi
        FROM pemesanan p
        JOIN tiket t ON p.tiket_id = t.id
        WHERE t.lokasi = ?
        ORDER BY p.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $lokasi);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Dashboard Posko</title>
<link rel="stylesheet" href="../includes/bootstrap.css"></head>
<body>
<div class="container mt-4">
  <div class="d-flex justify-content-between">
    <h2>Dashboard Posko - Lokasi: <?=htmlspecialchars($lokasi)?></h2>
    <div><a class="btn btn-secondary" href="login/logout.php">Logout</a></div>
  </div>

  <table class="table table-striped mt-3">
    <thead><tr><th>Kode Booking</th><th>Paket</th><th>Tanggal</th><th>Jumlah</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php while($row = $res->fetch_assoc()): ?>
      <tr>
        <td><?=htmlspecialchars($row['kode_booking'])?></td>
        <td><?=htmlspecialchars($row['nama_paket'])?></td>
        <td><?=htmlspecialchars($row['tanggal_kunjungan'])?></td>
        <td><?=htmlspecialchars($row['jumlah_tiket'])?></td>
        <td><?=htmlspecialchars(number_format($row['total_harga'],2,',','.'))?></td>
        <td><?=htmlspecialchars($row['status'])?></td>
        <td><a class="btn btn-primary btn-sm" href="verifikasi_tiket.php?kode=<?=urlencode($row['kode_booking'])?>">Verifikasi</a></td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
