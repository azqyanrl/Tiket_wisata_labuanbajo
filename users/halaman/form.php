<?php
include '../../database/konek.php';
include "session_cek.php";

// Validasi user login
if (!isset($_SESSION['user_id'])) {
  echo "<script>alert('Silakan login terlebih dahulu.'); window.location='../login/login.php';</script>";
  exit;
}

$user_id = $_SESSION['user_id'];
$id_tiket = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tanggal_kunjungan = $_GET['tanggal_kunjungan'] ?? '';
$jumlah_tiket = isset($_GET['jumlah_tiket']) ? intval($_GET['jumlah_tiket']) : 1;

// Validasi tanggal kunjungan
if (empty($tanggal_kunjungan) || strtotime($tanggal_kunjungan) < strtotime(date('Y-m-d'))) {
  echo "<script>alert('Tanggal kunjungan tidak valid.'); history.back();</script>";
  exit;
}

// Ambil data tiket
$stmt = $konek->prepare("SELECT * FROM tiket WHERE id=?");
$stmt->bind_param("i", $id_tiket);
$stmt->execute();
$tiket = $stmt->get_result()->fetch_assoc();

if (!$tiket) {
  echo "<script>alert('Data tiket tidak ditemukan.'); window.location='destinasi.php';</script>";
  exit;
}

$total_harga = $tiket['harga'] * $jumlah_tiket;
$kode_booking = 'LB' . date('YmdHis') . rand(100, 999);
$jenis = 'booking';
$metode = 'offline';
$status = 'pending';

// Simpan ke database
$simpan = $konek->prepare("INSERT INTO pemesanan 
  (kode_booking, user_id, tiket_id, jenis, tanggal_kunjungan, jumlah_tiket, total_harga, metode_pembayaran, status)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$simpan->bind_param("siissdsss", $kode_booking, $user_id, $id_tiket, $jenis, $tanggal_kunjungan, $jumlah_tiket, $total_harga, $metode, $status);

if ($simpan->execute()) {
  echo "<script>
          alert('Booking berhasil! Kode Booking Anda: $kode_booking');
          window.location='riwayat.php';
        </script>";
} else {
  echo "<script>alert('Gagal menyimpan data booking.'); history.back();</script>";
}
?>
