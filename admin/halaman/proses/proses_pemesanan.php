<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('location: ../login/login.php');
    exit;
}

include '../../../database/konek.php';
include '../../../includes/boot.php';

// Pastikan ID dan Aksi ada sebelum memproses
if (isset($_GET['id']) && isset($_GET['action'])) {
    $pemesanan_id = intval($_GET['id']);
    $action = $_GET['action'];

    switch ($action) {
        case 'confirm':
            // Update status pemesanan menjadi 'dibayar'
            $query_pemesanan = $konek->prepare("UPDATE pemesanan SET status = 'dibayar' WHERE id = ?");
            $query_pemesanan->bind_param("i", $pemesanan_id);
            $query_pemesanan->execute();
            
            // Cek apakah sudah ada payment
            $query_cek_payment = $konek->prepare("SELECT id FROM payments WHERE pemesanan_id = ?");
            $query_cek_payment->bind_param("i", $pemesanan_id);
            $query_cek_payment->execute();
            $result_cek_payment = $query_cek_payment->get_result();

            if ($result_cek_payment->num_rows > 0) {
                // Jika sudah ada, update statusnya menjadi verified
                $query_payment = $konek->prepare("UPDATE payments SET status = 'verified' WHERE pemesanan_id = ?");
                $query_payment->bind_param("i", $pemesanan_id);
                $query_payment->execute();
            } else {
                // Ambil user_id dan tiket_id dari pemesanan
                $query_info = $konek->prepare("SELECT user_id, tiket_id FROM pemesanan WHERE id = ?");
                $query_info->bind_param("i", $pemesanan_id);
                $query_info->execute();
                $result_info = $query_info->get_result();
                $info = $result_info->fetch_assoc();

                $user_id = $info['user_id'];
                $tiket_id = $info['tiket_id'];

                // Ambil transaction_id yang cocok berdasarkan user_id & ticket_id
                $query_get_transaction = $konek->prepare("SELECT id FROM transactions WHERE user_id = ? AND ticket_id = ? ORDER BY id DESC LIMIT 1");
                $query_get_transaction->bind_param("ii", $user_id, $tiket_id);
                $query_get_transaction->execute();
                $result_get_transaction = $query_get_transaction->get_result();

                $transaction_id = null;
                if ($result_get_transaction->num_rows > 0) {
                    $transaction_data = $result_get_transaction->fetch_assoc();
                    $transaction_id = $transaction_data['id'];
                }

                // Buat entri baru di tabel payments
                $bukti_transfer = "Diverifikasi langsung oleh admin";

                if ($transaction_id !== null) {
                    // Jika transaction_id ditemukan
                    $query_payment = $konek->prepare("INSERT INTO payments (pemesanan_id, transaction_id, bukti_transfer, status) VALUES (?, ?, ?, 'verified')");
                    $query_payment->bind_param("iis", $pemesanan_id, $transaction_id, $bukti_transfer);
                } else {
                    // Jika transaction_id tidak ditemukan, buat versi tanpa kolom transaction_id
                    $query_payment = $konek->prepare("INSERT INTO payments (pemesanan_id, bukti_transfer, status) VALUES (?, ?, 'verified')");
                    $query_payment->bind_param("is", $pemesanan_id, $bukti_transfer);
                }

                $query_payment->execute();
            }
            
            $_SESSION['success_message'] = "Pembayaran berhasil diverifikasi dan pesanan dikonfirmasi.";
            break;

        case 'reject':
            $query_payment = $konek->prepare("UPDATE payments SET status = 'rejected' WHERE pemesanan_id = ?");
            $query_payment->bind_param("i", $pemesanan_id);
            $query_payment->execute();
            
            $query_pemesanan = $konek->prepare("UPDATE pemesanan SET status = 'batal' WHERE id = ?");
            $query_pemesanan->bind_param("i", $pemesanan_id);
            $query_pemesanan->execute();
            
            $_SESSION['success_message'] = "Bukti pembayaran ditolak dan pemesanan dibatalkan.";
            break;

        case 'complete':
            $query = $konek->prepare("UPDATE pemesanan SET status = 'selesai' WHERE id = ? AND status = 'dibayar'");
            $query->bind_param("i", $pemesanan_id);
            $query->execute();
            if ($query->affected_rows > 0) {
                $_SESSION['success_message'] = "Pesanan berhasil diselesaikan.";
            } else {
                $_SESSION['error_message'] = "Gagal menyelesaikan pesanan. Pastikan status pesanan adalah 'Dibayar'.";
            }
            break;

        case 'cancel':
            $query = $konek->prepare("UPDATE pemesanan SET status = 'batal' WHERE id = ? AND status = 'dibayar'");
            $query->bind_param("i", $pemesanan_id);
            $query->execute();
            if ($query->affected_rows > 0) {
                $_SESSION['success_message'] = "Pesanan berhasil dibatalkan.";
            } else {
                $_SESSION['error_message'] = "Gagal membatalkan pesanan. Pastikan status pesanan adalah 'Dibayar'.";
            }
            break;

        default:
            $_SESSION['error_message'] = "Aksi tidak valid.";
            break;
    }
} else {
    $_SESSION['error_message'] = "Parameter ID atau Aksi tidak ditemukan.";
}

// Redirect kembali ke halaman kelola pemesanan
header("Location: ../index.php?page=kelola_pemesanan"); 
exit();
?>
