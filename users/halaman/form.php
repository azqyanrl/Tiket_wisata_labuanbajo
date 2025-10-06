<?php
// Ambil data dari form
$user_id   = $_SESSION['user_id'];
$tiket_id  = $_POST['ticket_id'];
$jumlah    = $_POST['jumlah'];
$tanggal   = $_POST['tanggal']; // tambahkan input tanggal di form

// Ambil harga dan stok default tiket
$q_tiket = $konek->query("SELECT harga, stok FROM tiket WHERE id = $tiket_id");
$tiket   = $q_tiket->fetch_assoc();
$harga_total = $tiket['harga'] * $jumlah;

// --- CEK STOK HARIAN ---
$q_stok = $konek->query("SELECT * FROM stok_harian WHERE tiket_id = $tiket_id AND tanggal = '$tanggal'");

if ($q_stok->num_rows == 0) {
  // Belum ada stok untuk tanggal itu → buat baru
  $stok_default = $tiket['stok'];
  $konek->query("INSERT INTO stok_harian (tiket_id, tanggal, stok_awal, stok_tersisa)
                 VALUES ($tiket_id, '$tanggal', $stok_default, $stok_default)");
  $stok_tersisa = $stok_default;
} else {
  $data_stok = $q_stok->fetch_assoc();
  $stok_tersisa = $data_stok['stok_tersisa'];
}

// --- VALIDASI JUMLAH PESANAN ---
if ($stok_tersisa < $jumlah) {
  echo "<script>alert('Stok tiket tidak cukup untuk tanggal tersebut. Tersisa: $stok_tersisa'); history.back();</script>";
  exit;
}

// --- KURANGI STOK ---
$konek->query("UPDATE stok_harian 
               SET stok_tersisa = stok_tersisa - $jumlah 
               WHERE tiket_id = $tiket_id AND tanggal = '$tanggal'");

// --- SIMPAN PEMESANAN ---
$sql = "INSERT INTO pemesanan (user_id, tiket_id, tanggal_kunjungan, jumlah_tiket, total_harga, status)
        VALUES ('$user_id', '$tiket_id', '$tanggal', '$jumlah', '$harga_total', 'pending')";

if ($konek->query($sql)) {
  echo "<script>alert('Tiket berhasil dipesan! Silakan lanjut ke pembayaran.'); window.location='../transaksi/riwayat.php';</script>";
} else {
  echo "<script>alert('Terjadi kesalahan: " . $konek->error . "'); history.back();</script>";
}
?>
