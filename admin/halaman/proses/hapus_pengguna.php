<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek akses admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Akses tidak valid.";
    header("Location: ../index.php?page=kelola_user");
    exit();
}

include '../../../database/konek.php';

// Ambil ID user yang akan dihapus
$id = $_GET['id'] ?? 0;

// Cek jika admin mencoba hapus dirinya sendiri
$current_admin_id = $_SESSION['user_id'] ?? null;
if ($id == $current_admin_id) {
    $_SESSION['error_message'] = "Tidak bisa menghapus akun sendiri.";
    header("Location: ../index.php?page=kelola_user");
    exit();
}

try {
    $konek->begin_transaction();

    // Hapus user
    $stmt = $konek->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $konek->commit();

    $_SESSION['success_message'] = "User dan semua data terkait berhasil dihapus.";
} catch (Exception $e) {
    $konek->rollback();
    $_SESSION['error_message'] = "Gagal menghapus user: " . $e->getMessage();
}

header("Location: ../index.php?page=kelola_user");
exit();
?>
