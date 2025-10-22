<?php
// proses/handle_booking.php
session_start();
include '../../database/konek.php';
include '../../includes/stok_otomatis.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../login/login.php';</script>";
    exit;
}

// Ambil data dari POST
 $tiket_id = intval($_POST['id_paket_wisata'] ?? 0);
 $jumlah_tiket = intval($_POST['jumlah_tiket_dipesan'] ?? 0);
 $tanggal_kunjungan = $_POST['tanggal_kunjungan_user'] ?? '';

if ($tiket_id <= 0 || $jumlah_tiket <= 0 || empty($tanggal_kunjungan)) {
    echo "<script>alert('Data booking tidak valid!'); window.history.back();</script>";
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_kunjungan)) {
    echo "<script>alert('Format tanggal tidak valid (YYYY-MM-DD).'); window.history.back();</script>";
    exit;
}

// Ambil data tiket (harga & stok_default)
 $q = $konek->prepare("SELECT nama_paket, harga, stok_default FROM tiket WHERE id = ?");
 $q->bind_param("i", $tiket_id);
 $q->execute();
 $data_tiket = $q->get_result()->fetch_assoc();
 $q->close();

if (!$data_tiket) {
    echo "<script>alert('Tiket tidak ditemukan!'); window.history.back();</script>";
    exit;
}

 $harga = floatval($data_tiket['harga']);
 $total_harga = $harga * $jumlah_tiket;

// Cek stok tersisa untuk tanggal tersebut
 $stok_tersisa = getStokTersisa($konek, $tiket_id, $tanggal_kunjungan);
if ($stok_tersisa < $jumlah_tiket) {
    echo "<script>alert('Maaf, stok tiket untuk tanggal tersebut tidak mencukupi! Tersisa: $stok_tersisa'); window.history.back();</script>";
    exit;
}

// Data booking
 $user_id = intval($_SESSION['user_id']);
 $status = 'pending';
 $kode_booking = 'LBJ' . date('YmdHis') . rand(100, 999);

// Mulai transaksi
 $konek->begin_transaction();

try {
    // Simpan ke tabel pemesanan
    $insert = $konek->prepare("
        INSERT INTO pemesanan 
        (kode_booking, user_id, tiket_id, tanggal_kunjungan, jumlah_tiket, total_harga, status, metode_pembayaran, jenis, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'offline', 'booking', NOW())
    ");
    $insert->bind_param("siisids", $kode_booking, $user_id, $tiket_id, $tanggal_kunjungan, $jumlah_tiket, $total_harga, $status);
    $insert->execute();
    $insert->close();
    
    // Kurangi stok
    $stmt = $konek->prepare("
        INSERT INTO stok_harian (tiket_id, tanggal, stok) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE stok = stok - ?
    ");
    $stmt->bind_param("isii", $tiket_id, $tanggal_kunjungan, -$jumlah_tiket, $jumlah_tiket);
    $stmt->execute();
    $stmt->close();
    
    // Commit transaksi
    $konek->commit();
    
    echo "<script>alert('Booking berhasil! Kode: $kode_booking'); window.location='riwayat.php';</script>";
    
} catch (Exception $e) {
    // Rollback jika ada error
    $konek->rollback();
    echo "<script>alert('Gagal melakukan booking. Error: " . $e->getMessage() . "'); window.history.back();</script>";
}
?>