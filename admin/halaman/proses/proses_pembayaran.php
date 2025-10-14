<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('location: ../../login/login.php');
    exit;
}

include '../../../database/konek.php';
include '../../../includes/boot.php';

// Proses pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_pembayaran'])) {
    $pemesanan_id = $_POST['pemesanan_id'];
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $kode_booking = $_POST['kode_booking'];

    // 1. Update status pemesanan menjadi 'dibayar'
    $update_pemesanan = $konek->prepare("UPDATE pemesanan SET status = 'dibayar', metode_pembayaran = ? WHERE id = ?");
    $update_pemesanan->bind_param("si", $metode_pembayaran, $pemesanan_id);
    $update_pemesanan->execute();

    // 2. Ambil data pemesanan untuk membuat transaksi
    $query_get_pemesanan = $konek->prepare("SELECT user_id, tiket_id, jumlah_tiket, total_harga FROM pemesanan WHERE id = ?");
    $query_get_pemesanan->bind_param("i", $pemesanan_id);
    $query_get_pemesanan->execute();
    $pemesanan_data_for_transaction = $query_get_pemesanan->get_result()->fetch_assoc();

    if ($pemesanan_data_for_transaction) {
        // 3. Buat entri baru di tabel transactions
        $query_insert_transaction = $konek->prepare("INSERT INTO transactions (user_id, ticket_id, jumlah_tiket, total_harga, status) VALUES (?, ?, ?, ?, 'paid')");
        $query_insert_transaction->bind_param("iiid", $pemesanan_data_for_transaction['user_id'], $pemesanan_data_for_transaction['tiket_id'], $pemesanan_data_for_transaction['jumlah_tiket'], $pemesanan_data_for_transaction['total_harga']);
        $query_insert_transaction->execute();

        // 4. Ambil ID transaksi yang baru dibuat
        $transaction_id = $konek->insert_id;

        // 5. Cek apakah sudah ada data di tabel payments untuk pemesanan ini
        $query_cek_payment = $konek->prepare("SELECT id FROM payments WHERE pemesanan_id = ?");
        $query_cek_payment->bind_param("i", $pemesanan_id);
        $query_cek_payment->execute();
        $result_cek_payment = $query_cek_payment->get_result();

        if ($result_cek_payment->num_rows > 0) {
            // Jika sudah ada, update statusnya dan transaction_id
            $query_payment = $konek->prepare("UPDATE payments SET transaction_id = ?, status = 'verified' WHERE pemesanan_id = ?");
            $query_payment->bind_param("ii", $transaction_id, $pemesanan_id);
            $query_payment->execute();
        } else {
            // Jika belum ada, buat entri baru di payments
            $query_payment = $konek->prepare("INSERT INTO payments (pemesanan_id, transaction_id, bukti_transfer, status) VALUES (?, ?, ?, 'verified')");
            $bukti_transfer = "Pembayaran langsung oleh admin - " . $metode_pembayaran;
            $query_payment->bind_param("iis", $pemesanan_id, $transaction_id, $bukti_transfer);
            $query_payment->execute();
        }

        $_SESSION['success_message'] = "Pembayaran berhasil dicatat untuk kode booking: " . htmlspecialchars($kode_booking);
        header("Location: ../index.php?page=kelola_pemesanan");
        exit();
    } else {
        $_SESSION['error_message'] = "Gagal memproses pembayaran. Data pemesanan tidak ditemukan.";
        header("Location: ../index.php?page=input_pembayaran");
        exit();
    }
}

// Jika bukan POST request, redirect ke halaman input pembayaran
header("Location: ../index.php?page=input_pembayaran");
exit();
?>