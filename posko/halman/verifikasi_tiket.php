<?php
// posko/verifikasi_tiket.php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'posko') {
    $_SESSION['error_message'] = 'Akses ditolak!';
    header('Location: login/login.php'); exit;
}
include '../database/konek.php';
$lokasi = $_SESSION['lokasi'] ?? '';

if (!isset($_GET['kode']) || trim($_GET['kode']) === '') {
    header('Location: posko_dashboard.php'); exit;
}
$kode = $_GET['kode'];

// ambil pemesanan & pastikan lokasi cocok
$sql = "SELECT p.*, t.nama_paket, t.lokasi FROM pemesanan p JOIN tiket t ON p.tiket_id=t.id WHERE p.kode_booking = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $kode);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo "Tiket tidak ditemukan."; exit;
}
$row = $res->fetch_assoc();
if ($row['lokasi'] !== $lokasi) {
    echo "Tiket ini bukan untuk posko Anda."; exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metode = $_POST['metode_pembayaran'] ?? 'cash';
    $status = $_POST['status'] ?? 'dibayar';
    $up = $conn->prepare("UPDATE pemesanan SET metode_pembayaran = ?, status = ? WHERE kode_booking = ?");
    $up->bind_param('sss', $metode, $status, $kode);
    if ($up->execute()) {
        // opsional: catat history atau kirim notifikasi
        header('Location: posko_dashboard.php');
        exit;
    } else {
        $error = "Gagal memperbarui: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Verifikasi Tiket</title>
<link rel="stylesheet" href="../includes/bootstrap.css"></head>
<body>
<div class="container mt-4">
  <h3>Verifikasi - <?=htmlspecialchars($row['kode_booking'])?></h3>
  <?php if(!empty($error)): ?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>
  <p>Paket: <?=htmlspecialchars($row['nama_paket'])?> | Tanggal: <?=htmlspecialchars($row['tanggal_kunjungan'])?></p>
  <form method="post">
    <div class="mb-3">
      <label>Metode Pembayaran</label>
      <select name="metode_pembayaran" class="form-control">
        <option value="cash" <?=($row['metode_pembayaran']=='cash')?'selected':''?>>Cash</option>
        <option value="qris" <?=($row['metode_pembayaran']=='qris')?'selected':''?>>QRIS</option>
        <option value="online" <?=($row['metode_pembayaran']=='online')?'selected':''?>>Online</option>
      </select>
    </div>
    <div class="mb-3">
      <label>Status</label>
      <select name="status" class="form-control">
        <option value="pending" <?=($row['status']=='pending')?'selected':''?>>pending</option>
        <option value="dibayar" <?=($row['status']=='dibayar')?'selected':''?>>dibayar</option>
        <option value="selesai" <?=($row['status']=='selesai')?'selected':''?>>selesai</option>
        <option value="batal" <?=($row['status']=='batal')?'selected':''?>>batal</option>
      </select>
    </div>
    <button type="submit" class="btn btn-success">Simpan</button>
    <a href="posko_dashboard.php" class="btn btn-secondary">Kembali</a>
  </form>
</div>
</body>
</html>
