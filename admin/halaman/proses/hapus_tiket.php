<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../../../database/konek.php';
include '../../../includes/boot.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID tidak valid.";
    header("Location: ../kelola_tiket.php");
    exit;
}

 $id = (int)$_GET['id'];

// Cek apakah ada pemesanan yang terkait dengan tiket ini
 $cek_pemesanan = $konek->prepare("SELECT COUNT(*) as total FROM pemesanan WHERE tiket_id = ?");
 $cek_pemesanan->bind_param("i", $id);
 $cek_pemesanan->execute();
 $result_cek = $cek_pemesanan->get_result();
 $data_cek = $result_cek->fetch_assoc();

if ($data_cek['total'] > 0) {
    $_SESSION['error_message'] = "Tidak dapat menghapus tiket yang sudah memiliki pemesanan.";
    header("Location: ../kelola_tiket.php");
    exit;
}

 $konek->begin_transaction();

try {
    // Hapus data terkait dulu
    $stmt1 = $konek->prepare("DELETE FROM stok_harian WHERE tiket_id = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();
    $stmt1->close();

    // Baru hapus tiket
    $stmt2 = $konek->prepare("DELETE FROM tiket WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $stmt2->close();

    $konek->commit();
    $_SESSION['success_message'] = "Tiket dan data terkait berhasil dihapus.";
} catch (Exception $e) {
    $konek->rollback();
    $_SESSION['error_message'] = "Gagal menghapus tiket: " . $e->getMessage();
}

header("Location: ../kelola_tiket.php");
exit;
?>