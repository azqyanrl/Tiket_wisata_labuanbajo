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

// ✅ Pastikan ID dan Aksi ada sebelum memproses
if (isset($_GET['id']) && isset($_GET['action'])) {
    $pemesanan_id = intval($_GET['id']);
    $action = $_GET['action'];

    // ✅ Ambil ID admin dari sesi login
    $admin_id = $_SESSION['id'] ?? 0; // fallback ke 0 agar tidak null

    switch ($action) {

        // ✅ KONFIRMASI PEMBAYARAN
        case 'confirm':
            $query_pemesanan = $konek->prepare("UPDATE pemesanan SET status = 'dibayar' WHERE id = ?");
            $query_pemesanan->bind_param("i", $pemesanan_id);
            $query_pemesanan->execute();

            // Cek apakah sudah ada payment
            $query_cek_payment = $konek->prepare("SELECT id FROM payments WHERE pemesanan_id = ?");
            $query_cek_payment->bind_param("i", $pemesanan_id);
            $query_cek_payment->execute();
            $result_cek_payment = $query_cek_payment->get_result();

            if ($result_cek_payment->num_rows > 0) {
                $query_payment = $konek->prepare("UPDATE payments SET status = 'verified' WHERE pemesanan_id = ?");
                $query_payment->bind_param("i", $pemesanan_id);
                $query_payment->execute();
            } else {
                $query_info = $konek->prepare("SELECT user_id, tiket_id, jumlah_tiket, created_at FROM pemesanan WHERE id = ?");
                $query_info->bind_param("i", $pemesanan_id);
                $query_info->execute();
                $info = $query_info->get_result()->fetch_assoc();

                $user_id = $info['user_id'];
                $tiket_id = $info['tiket_id'];
                $jumlah_tiket = $info['jumlah_tiket'];

                $update_stok = $konek->prepare("UPDATE stok_tiket SET tiket_tersisa = tiket_tersisa - ? WHERE id_paket_wisata = ?");
                $update_stok->bind_param("ii", $jumlah_tiket, $tiket_id);
                $update_stok->execute();

                $query_get_transaction = $konek->prepare("SELECT id FROM transactions WHERE user_id = ? AND ticket_id = ? ORDER BY id DESC LIMIT 1");
                $query_get_transaction->bind_param("ii", $user_id, $tiket_id);
                $query_get_transaction->execute();
                $transaction = $query_get_transaction->get_result()->fetch_assoc();
                $transaction_id = $transaction['id'] ?? null;

                $bukti_transfer = "Diverifikasi langsung oleh admin pusat";
                if ($transaction_id) {
                    $query_payment = $konek->prepare("INSERT INTO payments (pemesanan_id, transaction_id, bukti_transfer, status) VALUES (?, ?, ?, 'verified')");
                    $query_payment->bind_param("iis", $pemesanan_id, $transaction_id, $bukti_transfer);
                } else {
                    $query_payment = $konek->prepare("INSERT INTO payments (pemesanan_id, bukti_transfer, status) VALUES (?, ?, 'verified')");
                    $query_payment->bind_param("is", $pemesanan_id, $bukti_transfer);
                }
                $query_payment->execute();
            }

            // ✅ Catat ke tabel verifikasi_history
            $catatan = 'Verifikasi pembayaran oleh admin pusat';
            $metode = 'manual';
            $status = 'dibayar';
            $insert_history = $konek->prepare("
                INSERT INTO verifikasi_history (pemesanan_id, admin_id, metode_pembayaran, status, catatan, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $insert_history->bind_param("iisss", $pemesanan_id, $admin_id, $metode, $status, $catatan);
            $insert_history->execute();

            $_SESSION['success_message'] = "Pembayaran diverifikasi dan pesanan dikonfirmasi.";
            break;

        // ❌ TOLAK PEMBAYARAN
        case 'reject':
            $query_payment = $konek->prepare("UPDATE payments SET status = 'rejected' WHERE pemesanan_id = ?");
            $query_payment->bind_param("i", $pemesanan_id);
            $query_payment->execute();

            $query_pemesanan = $konek->prepare("UPDATE pemesanan SET status = 'batal' WHERE id = ?");
            $query_pemesanan->bind_param("i", $pemesanan_id);
            $query_pemesanan->execute();

            // ✅ Catat ke verifikasi_history
            $catatan = 'Pembayaran ditolak oleh admin pusat';
            $metode = 'manual';
            $status = 'batal';
            $insert_history = $konek->prepare("
                INSERT INTO verifikasi_history (pemesanan_id, admin_id, metode_pembayaran, status, catatan, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $insert_history->bind_param("iisss", $pemesanan_id, $admin_id, $metode, $status, $catatan);
            $insert_history->execute();

            $_SESSION['success_message'] = "Pembayaran ditolak dan pesanan dibatalkan.";
            break;

        // ✅ SELESAIKAN PEMESANAN
        case 'complete':
            $query = $konek->prepare("UPDATE pemesanan SET status = 'selesai' WHERE id = ? AND status = 'dibayar'");
            $query->bind_param("i", $pemesanan_id);
            $query->execute();

            if ($query->affected_rows > 0) {
                $catatan = 'Pesanan diselesaikan oleh admin pusat';
                $metode = 'manual';
                $status = 'selesai';
                $insert_history = $konek->prepare("
                    INSERT INTO verifikasi_history (pemesanan_id, admin_id, metode_pembayaran, status, catatan, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $insert_history->bind_param("iisss", $pemesanan_id, $admin_id, $metode, $status, $catatan);
                $insert_history->execute();

                $_SESSION['success_message'] = "Pesanan berhasil diselesaikan.";
            } else {
                $_SESSION['error_message'] = "Gagal menyelesaikan pesanan. Pastikan status pesanan adalah 'Dibayar'.";
            }
            break;

        // 🚫 BATALKAN PEMESANAN (khusus pending)
        case 'cancel':
            $query = $konek->prepare("UPDATE pemesanan SET status = 'batal' WHERE id = ? AND status = 'pending'");
            $query->bind_param("i", $pemesanan_id);
            $query->execute();

            if ($query->affected_rows > 0) {
                $catatan = 'Pesanan dibatalkan oleh admin pusat.';
                $metode = 'manual';
                $status = 'batal';
                $insert_history = $konek->prepare("
                    INSERT INTO verifikasi_history (pemesanan_id, admin_id, metode_pembayaran, status, catatan, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $insert_history->bind_param("iisss", $pemesanan_id, $admin_id, $metode, $status, $catatan);
                $insert_history->execute();

                $_SESSION['success_message'] = "Pesanan berhasil dibatalkan.";
            } else {
                $_SESSION['error_message'] = "Gagal membatalkan pesanan. Pastikan status masih pending.";
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
