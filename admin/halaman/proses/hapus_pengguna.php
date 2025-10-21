<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek akses admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    // GANTI INI:
    $_SESSION['error_message'] = "Akses tidak valid.";
    header("Location: ../index.php?page=kelola_user");
    exit();
}


if ($_GET['id'] == $current_admin_id) {
    // GANTI INI:
    $_SESSION['error_message'] = "Tidak bisa menghapus akun sendiri.";
    header("Location: ../index.php?page=kelola_user");
    exit();
}

include '../../../database/konek.php';

try {
    // ... (logika penghapusan) ...
    $konek->commit();

    // GANTI INI:
    $_SESSION['success_message'] = "User dan semua data terkait berhasil dihapus.";
} catch (Exception $e) {
    $konek->rollback();
    // GANTI INI:
    $_SESSION['error_message'] = "Gagal menghapus user: " . $e->getMessage();
}

header("Location: ../index.php?page=kelola_user");
exit();
?>
