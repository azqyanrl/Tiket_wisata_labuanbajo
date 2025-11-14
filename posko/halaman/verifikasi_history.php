<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'posko') {
    $_SESSION['error_message'] = "Akses ditolak! Anda harus login sebagai posko.";
    header("Location: ../login/login.php");
    exit;
}

include '../../database/konek.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pemesanan_id = intval($_POST['pemesanan_id']);
    $status_baru = $_POST['status'] ?? '';
    $catatan = $_POST['catatan'] ?? '';
    $metode = $_POST['metode_pembayaran'] ?? 'offline';
    $admin_id = $_SESSION['user_id'];

    // Validasi input
    if (!$pemesanan_id || empty($status_baru)) {
        $_SESSION['error_message'] = "Data tidak lengkap.";
        header("Location: verifikasi_histori.php");
        exit;
    }

    // Update status di tabel pemesanan
    $update = $conn->prepare("UPDATE pemesanan SET status = ?, updated_at = NOW() WHERE id = ?");
    $update->bind_param("si", $status_baru, $pemesanan_id);
    $update->execute();

    // Catat ke tabel history
    $history = $conn->prepare("INSERT INTO verifikasi_history (pemesanan_id, admin_id, metode_pembayaran, status, catatan) VALUES (?, ?, ?, ?, ?)");
    $history->bind_param("iisss", $pemesanan_id, $admin_id, $metode, $status_baru, $catatan);
    $history->execute();

    // Notifikasi sukses
    $_SESSION['success_message'] = "Status pemesanan berhasil diperbarui dan dicatat ke history.";
    header("Location: verifikasi_histori.php");
    exit;
}
?>
