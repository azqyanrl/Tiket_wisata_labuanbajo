<?php
// proses/handle_booking.php
session_start();
include '../../database/konek.php';
include '../../includes/stok_otomatis.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Silakan login terlebih dahulu!";
    header('location: ../login/login.php');
    exit;
}

// Ambil data dari POST
 $tiket_id = intval($_POST['id_paket_wisata'] ?? 0);
 $jumlah_tiket = intval($_POST['jumlah_tiket_dipesan'] ?? 0);
 $tanggal_kunjungan = $_POST['tanggal_kunjungan_user'] ?? '';

if ($tiket_id <= 0 || $jumlah_tiket <= 0 || empty($tanggal_kunjungan)) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_kunjungan)) {
    $_SESSION['error_message'] = "Format tanggal tidak valid (YYYY-MM-DD).";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Ambil data tiket (harga & stok_default)
 $q = $konek->prepare("SELECT nama_paket, harga, stok_default FROM tiket WHERE id = ?");
 $q->bind_param("i", $tiket_id);
 $q->execute();
 $data_tiket = $q->get_result()->fetch_assoc();
 $q->close();

if (!$data_tiket) {
    $_SESSION['error_message'] = "Tiket tidak ditemukan!";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

 $harga = floatval($data_tiket['harga']);
 $total_harga = $harga * $jumlah_tiket;

// Cek stok tersisa untuk tanggal tersebut
 $stok_tersisa = getStokTersisa($konek, $tiket_id, $tanggal_kunjungan);
if ($stok_tersisa < $jumlah_tiket) {
    $_SESSION['error_message'] = "Maaf, stok tiket untuk tanggal tersebut tidak mencukupi! Tersisa: $stok_tersisa";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
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
    
    // --- PERBAIKAN: Update tiket terjual harian dengan tabel dan kolom yang benar ---
    $stmt = $konek->prepare("
        INSERT INTO tiket_terjual_harian (tiket_id, tanggal_kunjungan, jumlah_terjual) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE jumlah_terjual = jumlah_terjual + VALUES(jumlah_terjual)
    ");
    $stmt->bind_param("isi", $tiket_id, $tanggal_kunjungan, $jumlah_tiket);
    $stmt->execute();
    $stmt->close();
    
    // Commit transaksi
    $konek->commit();
    
    // --- PERUBAHAN: Simpan sukses message ke session dan redirect ---
    $_SESSION['success_message'] = "Booking berhasil! Kode pemesanan Anda: $kode_booking. Silakan lakukan pembayaran ke admin.";
    header('Location: riwayat.php');
    exit;
    
} catch (Exception $e) {
    // Rollback jika ada error
    $konek->rollback();
    
    // --- PERUBAHAN: Simpan error message ke session dan redirect ---
    $_SESSION['error_message'] = "Gagal melakukan booking. Silakan coba lagi.";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
?>