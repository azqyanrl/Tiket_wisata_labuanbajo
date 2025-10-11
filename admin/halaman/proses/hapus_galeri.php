<?php
session_start();
include '../../../database/konek.php'; // Path ini mungkin perlu disesuaikan, lebih baik gunakan $_SERVER['DOCUMENT_ROOT']
include $_SERVER['DOCUMENT_ROOT'] . '/Tiket_wisata_labuanbajo/database/konek.php'; // Lebih baik pakai ini

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_galeri = $_GET['id'];
    $stmt = $konek->prepare("DELETE FROM galleries WHERE id = ?");
    $stmt->bind_param("i", $id_galeri);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Foto berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus foto.";
    }
    $stmt->close();
} else {
    $_SESSION['error_message'] = "ID foto tidak valid.";
}

// --- PERUBAHAN PATH DI SINI ---
header("Location: ../galeri.php");
exit();