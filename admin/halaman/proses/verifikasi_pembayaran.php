<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') { header('location: ../../login/login.php'); exit; }
include '../../../database/konek.php';
include '../../../includes/boot.php';

if (isset($_GET['id']) && isset($_GET['action'])) {
    $pemesanan_id = intval($_GET['id']); $action = $_GET['action'];
    if ($action === 'confirm') {
        $stmt1 = $konek->prepare("UPDATE pemesanan SET status = 'dibayar' WHERE id = ?"); $stmt1->bind_param("i", $pemesanan_id); $stmt1->execute();
        $stmt2 = $konek->prepare("UPDATE payments SET status = 'verified' WHERE pemesanan_id = ?"); $stmt2->bind_param("i", $pemesanan_id); $stmt2->execute();
        $_SESSION['success_message'] = "Pembayaran berhasil diverifikasi dan dikonfirmasi.";
    } elseif ($action === 'reject') {
        $stmt = $konek->prepare("UPDATE payments SET status = 'rejected' WHERE pemesanan_id = ?"); $stmt->bind_param("i", $pemesanan_id); $stmt->execute();
        $_SESSION['success_message'] = "Bukti pembayaran ditolak.";
    }
}
header("Location: ../index.php?page=kelola_pemesanan"); exit();
?>