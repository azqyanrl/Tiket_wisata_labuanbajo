<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Cek akses admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Akses ditolak! Anda harus login sebagai admin.";
    header('location: ../../login/login_admin.php');
    exit;
}

include '../../../database/konek.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID tidak valid.";
    header("Location: ../index.php?page=kelola_tiket");
    exit;
}

$id = (int)$_GET['id'];

// ✅ Cek apakah ada pemesanan yang terkait dengan tiket ini
$cek_pemesanan = $konek->prepare("SELECT COUNT(*) as total FROM pemesanan WHERE tiket_id = ?");
$cek_pemesanan->bind_param("i", $id);
$cek_pemesanan->execute();
$result_cek = $cek_pemesanan->get_result();
$data_cek = $result_cek->fetch_assoc();
$cek_pemesanan->close();

if ($data_cek['total'] > 0) {
    $_SESSION['error_message'] = "Tidak dapat menghapus tiket yang sudah memiliki pemesanan.";
    header("Location: ../index.php?page=kelola_tiket");
    exit;
}

// ✅ Ambil nama gambar untuk dihapus
$query_gambar = $konek->prepare("SELECT gambar FROM tiket WHERE id = ?");
$query_gambar->bind_param("i", $id);
$query_gambar->execute();
$result_gambar = $query_gambar->get_result();
$gambar = $result_gambar->fetch_assoc()['gambar'] ?? '';
$query_gambar->close();

$konek->begin_transaction();

try {
    // 🔹 Hapus data terkait di tiket_terjual_harian
    $stmt1 = $konek->prepare("DELETE FROM tiket_terjual_harian WHERE tiket_id = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();
    $stmt1->close();

    // 🔹 Hapus tiket
    $stmt2 = $konek->prepare("DELETE FROM tiket WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $stmt2->close();

    $konek->commit();

    // 🔹 Hapus file gambar jika ada
    if (!empty($gambar)) {
        $file_path = __DIR__ . "/../../assets/images/tiket/" . $gambar;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    $_SESSION['success_message'] = "Tiket dan data terkait berhasil dihapus.";
} catch (Exception $e) {
    $konek->rollback();
    $_SESSION['error_message'] = "Gagal menghapus tiket: " . $e->getMessage();
}

header("Location: ../index.php?page=kelola_tiket");
exit;
?>
