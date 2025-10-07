<?php
// ====== INCLUDE WAJIB (JANGAN DIUBAH) ======
include '../../database/konek.php';
include "session_cek.php";

// ====== CEK LOGIN USER ======
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location='../login/login.php';</script>";
    exit;
}

$id_pengguna = intval($_SESSION['user_id']);
$id_paket_wisata = isset($_GET['id_paket_wisata']) ? intval($_GET['id_paket_wisata']) : 0;
$tanggal_kunjungan_user = $_GET['tanggal_kunjungan_user'] ?? '';
$jumlah_tiket_dipesan = isset($_GET['jumlah_tiket_dipesan']) ? intval($_GET['jumlah_tiket_dipesan']) : 1;

// ====== VALIDASI INPUT ======
if ($id_paket_wisata <= 0) {
    echo "<script>alert('Data paket wisata tidak valid.'); history.back();</script>";
    exit;
}

if (empty($tanggal_kunjungan_user) || strtotime($tanggal_kunjungan_user) < strtotime(date('Y-m-d'))) {
    echo "<script>alert('Tanggal kunjungan tidak boleh sebelum hari ini.'); history.back();</script>";
    exit;
}

if ($jumlah_tiket_dipesan <= 0) {
    $jumlah_tiket_dipesan = 1;
}

// ====== AMBIL DATA PAKET WISATA ======
$query_paket = $konek->prepare("SELECT id, nama_paket, harga FROM tiket WHERE id = ? LIMIT 1");
$query_paket->bind_param("i", $id_paket_wisata);
$query_paket->execute();
$hasil_paket = $query_paket->get_result();
$data_paket = $hasil_paket->fetch_assoc();
$query_paket->close();

if (!$data_paket) {
    echo "<script>alert('Paket wisata tidak ditemukan.'); window.location='destinasi.php';</script>";
    exit;
}

// ====== HITUNG TOTAL HARGA ======
$harga_per_tiket = floatval($data_paket['harga']);
$total_harga_booking = $harga_per_tiket * $jumlah_tiket_dipesan;

// ====== GENERATE KODE BOOKING UNIK ======
$kode_booking = 'LBJ' . date('YmdHis') . rand(100, 999);

// ====== DATA TAMBAHAN ======
$jenis_pemesanan = 'booking';
$metode_pembayaran = 'offline';
$status_pemesanan = 'pending';

// ====== ATUR BATAS WAKTU PEMBAYARAN ======
// Format 'Y-m-d H:i:s' agar cocok untuk kolom DATETIME
$tanggal_batas_pembayaran = date('Y-m-d H:i:s', strtotime('+3 days'));

// Jika kolom batas_waktu bukan tipe DATETIME, biar tidak error
if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $tanggal_batas_pembayaran)) {
    $tanggal_batas_pembayaran = NULL;
}

// ====== SIMPAN DATA BOOKING KE DATABASE ======
$query_simpan_booking = $konek->prepare("
  INSERT INTO pemesanan (
      kode_booking, user_id, tiket_id, jenis, tanggal_kunjungan,
      batas_waktu, jumlah_tiket, total_harga, metode_pembayaran, status, created_at
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

$query_simpan_booking->bind_param(
    "siissidsss",
    $kode_booking,
    $id_pengguna,
    $id_paket_wisata,
    $jenis_pemesanan,
    $tanggal_kunjungan_user,
    $tanggal_batas_pembayaran,
    $jumlah_tiket_dipesan,
    $total_harga_booking,
    $metode_pembayaran,
    $status_pemesanan
);

// ====== EKSEKUSI & CEK HASIL ======
if ($query_simpan_booking->execute()) {
    echo "<script>
            alert('Booking berhasil! Kode Booking Anda: $kode_booking');
            window.location='riwayat.php';
          </script>";
} else {
    echo "<script>alert('Terjadi kesalahan saat menyimpan booking.'); history.back();</script>";
}

$query_simpan_booking->close();
?>
