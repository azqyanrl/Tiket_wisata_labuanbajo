<?php
include '../../database/konek.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("<script>alert('Silakan login terlebih dahulu!'); window.location='../login/login.php';</script>");
}

$tiket_id = intval($_GET['id_paket_wisata'] ?? 0);
$jumlah_tiket = intval($_GET['jumlah_tiket_dipesan'] ?? 0);
$tanggal_kunjungan = $_GET['tanggal_kunjungan_user'] ?? '';

if ($tiket_id <= 0 || $jumlah_tiket <= 0 || empty($tanggal_kunjungan)) {
    die("<script>alert('Data booking tidak valid!'); window.history.back();</script>");
}

$query = $konek->prepare("SELECT nama_paket, harga, stok_total FROM tiket WHERE id = ?");
$query->bind_param("i", $tiket_id);
$query->execute();
$data_tiket = $query->get_result()->fetch_assoc();
$query->close();

if (!$data_tiket) {
    die("<script>alert('Tiket tidak ditemukan!'); window.history.back();</script>");
}

$harga = $data_tiket['harga'];
$total_harga = $harga * $jumlah_tiket;

// Cek stok tersisa
$qstok = $konek->prepare("
    SELECT t.stok_total - IFNULL(SUM(p.jumlah_tiket), 0) AS stok_tersisa
    FROM tiket t
    LEFT JOIN pemesanan p ON t.id = p.tiket_id 
        AND p.tanggal_kunjungan = ? 
        AND p.status IN ('pending', 'dibayar', 'selesai')
    WHERE t.id = ?
    GROUP BY t.id
");
$qstok->bind_param("si", $tanggal_kunjungan, $tiket_id);
$qstok->execute();
$stok_result = $qstok->get_result()->fetch_assoc();
$qstok->close();

$stok_tersisa = $stok_result ? intval($stok_result['stok_tersisa']) : $data_tiket['stok_total'];

if ($stok_tersisa < $jumlah_tiket) {
    die("<script>alert('Maaf, stok tiket untuk tanggal tersebut tidak mencukupi! Tersisa: $stok_tersisa'); window.history.back();</script>");
}

$user_id = $_SESSION['user_id'];
$status = 'pending';
$kode_booking = 'LBJ' . date('YmdHis') . rand(100, 999);

$insert = $konek->prepare("
    INSERT INTO pemesanan (kode_booking, user_id, tiket_id, tanggal_kunjungan, jumlah_tiket, total_harga, status, metode_pembayaran, jenis, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'offline', 'booking', NOW())
");
$insert->bind_param("siisdis", $kode_booking, $user_id, $tiket_id, $tanggal_kunjungan, $jumlah_tiket, $total_harga, $status);

if ($insert->execute()) {
    echo "<script>alert('Booking berhasil!'); window.location='riwayat.php';</script>";
} else {
    echo "<script>alert('Gagal melakukan booking.'); window.history.back();</script>";
}
$insert->close();
?>
