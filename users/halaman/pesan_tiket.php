<?php
include '../../database/konek.php';
include "session_cek.php";

// Validasi user login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location='../login/login.php';</script>";
    exit;
}

 $user_id = $_SESSION['user_id'];
 $id_tiket = isset($_GET['id_paket_wisata']) ? intval($_GET['id_paket_wisata']) : 0;
 $tanggal_kunjungan = $_GET['tanggal_kunjungan_user'] ?? '';
 $jumlah_tiket = isset($_GET['jumlah_tiket_dipesan']) ? intval($_GET['jumlah_tiket_dipesan']) : 1;

// Validasi tanggal kunjungan
if (empty($tanggal_kunjungan) || strtotime($tanggal_kunjungan) < strtotime(date('Y-m-d'))) {
    echo "<script>alert('Tanggal kunjungan tidak valid.'); history.back();</script>";
    exit;
}

// Ambil data tiket untuk validasi dan harga
 $stmt = $konek->prepare("SELECT harga FROM tiket WHERE id=?");
 $stmt->bind_param("i", $id_tiket);
 $stmt->execute();
 $tiket = $stmt->get_result()->fetch_assoc();

if (!$tiket) {
    echo "<script>alert('Data tiket tidak ditemukan.'); window.location='destinasi.php';</script>";
    exit;
}

 $total_harga = $tiket['harga'] * $jumlah_tiket;
 $kode_booking = 'LBJ' . date('YmdHis') . rand(100, 999);
 $jenis = 'booking';
 $metode = 'offline';
 $status = 'pending';
 $batas_waktu = date('Y-m-d H:i:s', strtotime('+1 day'));

// Simpan ke database
 $simpan = $konek->prepare("INSERT INTO pemesanan (kode_booking, user_id, tiket_id, jenis, tanggal_kunjungan, batas_waktu, jumlah_tiket, total_harga, metode_pembayaran, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $simpan->bind_param("siisssidss", $kode_booking, $user_id, $id_tiket, $jenis, $tanggal_kunjungan, $batas_waktu, $jumlah_tiket, $total_harga, $metode, $status);

if ($simpan->execute()) {
    echo "<script>
            alert('Booking berhasil! Kode Booking Anda: $kode_booking. Silakan lakukan pembayaran.');
            window.location='riwayat.php';
          </script>";
} else {
    echo "<script>alert('Gagal menyimpan data booking.'); history.back();</script>";
}
?>