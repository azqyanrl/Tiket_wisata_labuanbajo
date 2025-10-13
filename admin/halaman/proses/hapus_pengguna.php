<?php
session_start();
include '../../../database/konek.php';

// Validasi session dan parameter
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    die("Akses tidak valid.");
}

// Cegah admin menghapus akunnya sendiri
if ($_GET['id'] == $_SESSION['user_id']) {
    $_SESSION['error_message'] = "Tidak bisa menghapus akun sendiri.";
    header("Location: ../index.php?page=kelola_user");
    exit();
}

$user_id = $_GET['id'];

// Gunakan transaksi untuk menjaga konsistensi data
$konek->begin_transaction();

try {
    // 1. Hapus data di tabel ratings yang terkait dengan user
    $query_hapus_ratings = $konek->prepare("DELETE FROM ratings WHERE user_id = ?");
    $query_hapus_ratings->bind_param("i", $user_id);
    $query_hapus_ratings->execute();

    // 2. Hapus data di tabel payments (karena memiliki constraint ke pemesanan)
    // Pertama, dapatkan semua pemesanan_id untuk user ini
    $query_pemesanan_ids = $konek->prepare("SELECT id FROM pemesanan WHERE user_id = ?");
    $query_pemesanan_ids->bind_param("i", $user_id);
    $query_pemesanan_ids->execute();
    $result_pemesanan = $query_pemesanan_ids->get_result();

    // Hapus payments untuk setiap pemesanan
    while ($row = $result_pemesanan->fetch_assoc()) {
        $query_hapus_payments = $konek->prepare("DELETE FROM payments WHERE pemesanan_id = ?");
        $query_hapus_payments->bind_param("i", $row['id']);
        $query_hapus_payments->execute();
    }

    // 3. Hapus data di tabel transactions yang terkait dengan user
    $query_hapus_transactions = $konek->prepare("DELETE FROM transactions WHERE user_id = ?");
    $query_hapus_transactions->bind_param("i", $user_id);
    $query_hapus_transactions->execute();

    // 4. Hapus data di tabel pemesanan
    $query_hapus_pemesanan = $konek->prepare("DELETE FROM pemesanan WHERE user_id = ?");
    $query_hapus_pemesanan->bind_param("i", $user_id);
    $query_hapus_pemesanan->execute();

    // 5. Hapus user dari tabel users
    $query_hapus_user = $konek->prepare("DELETE FROM users WHERE id = ?");
    $query_hapus_user->bind_param("i", $user_id);
    $query_hapus_user->execute();

    // Commit semua perubahan jika berhasil
    $konek->commit();

    $_SESSION['success_message'] = "User dan semua data terkait berhasil dihapus.";
} catch (Exception $e) {
    // Rollback jika terjadi error
    $konek->rollback();
    $_SESSION['error_message'] = "Gagal menghapus user: " . $e->getMessage();
}

// Redirect ke halaman kelola user
header("Location: ../index.php?page=kelola_user");
exit();
