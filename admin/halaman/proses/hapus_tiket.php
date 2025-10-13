<?php
session_start();
include "../../../database/konek.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID tidak valid.";
    header("Location: data_siswa.php");
    exit;
}

$id = (int)$_GET['id'];

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

header("Location: data_siswa.php");
exit;
