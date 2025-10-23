<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek akses admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Akses ditolak!";
    header('location: ../../login/login.php');
    exit;
}

include '../../database/konek.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID tidak valid.";
    header("Location: ../index.php?page=kelola_kategori");
    exit;
}

 $id = (int)$_GET['id'];

// Cek apakah ada tiket yang menggunakan kategori ini
 $cek_tiket = $konek->prepare("SELECT COUNT(*) as total FROM tiket WHERE kategori_id = ?");
 $cek_tiket->bind_param("i", $id);
 $cek_tiket->execute();
 $result = $cek_tiket->get_result();
 $data = $result->fetch_assoc();

if ($data['total'] > 0) {
    $_SESSION['error_message'] = "Tidak dapat menghapus kategori karena masih ada tiket yang menggunakannya.";
} else {
    $stmt = $konek->prepare("DELETE FROM kategori WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['success_message'] = "Kategori berhasil dihapus.";
}

header("Location: ../index.php?page=kelola_kategori");
exit;
?>