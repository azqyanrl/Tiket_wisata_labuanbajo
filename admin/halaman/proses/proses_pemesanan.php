<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak!'); document.location.href='../login/login.php';</script>";
    exit;
}

include '../../../database/konek.php';
include '../../../includes/boot.php';

// Pastikan ada ID dan action
if (!isset($_GET['id']) || !isset($_GET['action'])) {
    $_SESSION['error_message'] = "Parameter ID atau aksi tidak ditemukan.";
    header("Location: ?page=kelola_pemesanan");
    exit;
}

$pemesanan_id = intval($_GET['id']);
$action = $_GET['action'];
$admin_id = $_SESSION['id'] ?? 0; // fallback

switch ($action) {
    // KONFIRMASI PEMBAYARAN
    case 'confirm':
        $query = $konek->prepare("UPDATE pemesanan SET status = 'dibayar' WHERE id = ?");
        $query->bind_param("i", $pemesanan_id);
        $query->execute();

        // Catat payment
        $bukti_transfer = "Diverifikasi langsung oleh admin pusat";

        // Cek transaksi terakhir
        $q_trx = $konek->prepare("SELECT id, user_id, tiket_id, jumlah_tiket FROM pemesanan WHERE id = ?");
        $q_trx->bind_param("i", $pemesanan_id);
        $q_trx->execute();
        $info = $q_trx->get_result()->fetch_assoc();

        $user_id = $info['user_id'];
        $tiket_id = $info['tiket_id'];
        $jumlah_tiket = $info['jumlah_tiket'];

        // Update stok tiket
        $update_stok = $konek->prepare("UPDATE stok_tiket SET tiket_tersisa = tiket_tersisa - ? WHERE id_paket_wisata = ?");
        $update_stok->bind_param("ii", $jumlah_tiket, $tiket_id);
        $update_stok->execute();

        // Masukkan payment
        $q_payment = $konek->prepare("INSERT INTO payments (pemesanan_id, bukti_transfer, status) VALUES (?, ?, 'verified')");
        $q_payment->bind_param("is", $pemesanan_id, $bukti_transfer);
        $q_payment->execute();

        // Catat riwayat verifikasi
        $catatan = "Verifikasi pembayaran oleh admin pusat";
        $status = "dibayar";
        $metode = "manual";
        $insert_history = $konek->prepare("
            INSERT INTO verifikasi_history (pemesanan_id, admin_id, metode_pembayaran, status, catatan, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $insert_history->bind_param("iisss", $pemesanan_id, $admin_id, $metode, $status, $catatan);
        $insert_history->execute();

        $_SESSION['success_message'] = "Pembayaran diverifikasi.";
        break;

    // TOLAK PEMBAYARAN
    case 'reject':
        $q1 = $konek->prepare("UPDATE payments SET status = 'rejected' WHERE pemesanan_id = ?");
        $q1->bind_param("i", $pemesanan_id);
        $q1->execute();

        $q2 = $konek->prepare("UPDATE pemesanan SET status = 'batal' WHERE id = ?");
        $q2->bind_param("i", $pemesanan_id);
        $q2->execute();

        $catatan = "Pembayaran ditolak oleh admin pusat";
        $status = "batal";
        $metode = "manual";
        $insert_history = $konek->prepare("
            INSERT INTO verifikasi_history (pemesanan_id, admin_id, metode_pembayaran, status, catatan, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $insert_history->bind_param("iisss", $pemesanan_id, $admin_id, $metode, $status, $catatan);
        $insert_history->execute();

        $_SESSION['success_message'] = "Pembayaran ditolak dan pesanan dibatalkan.";
        break;

    // SELESAIKAN PEMESANAN
    case 'complete':
        // Hanya update kalau status sudah dibayar
        $q = $konek->prepare("UPDATE pemesanan SET status = 'selesai' WHERE id = ? AND status = 'dibayar'");
        $q->bind_param("i", $pemesanan_id);
        $q->execute();

        if ($q->affected_rows > 0) {
            $catatan = "Pesanan diselesaikan oleh admin pusat";
            $status = "selesai";
            $metode = "manual";
            $insert_history = $konek->prepare("
                INSERT INTO verifikasi_history (pemesanan_id, admin_id, metode_pembayaran, status, catatan, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $insert_history->bind_param("iisss", $pemesanan_id, $admin_id, $metode, $status, $catatan);
            $insert_history->execute();

            $_SESSION['success_message'] = "Pesanan berhasil diselesaikan.";
        } else {
            $_SESSION['error_message'] = "Gagal menyelesaikan pesanan. Pastikan status sudah 'dibayar'.";
        }
        break;

    // BATALKAN PEMESANAN (pending)
    case 'cancel':
        $q = $konek->prepare("UPDATE pemesanan SET status = 'batal' WHERE id = ? AND status = 'pending'");
        $q->bind_param("i", $pemesanan_id);
        $q->execute();

        if ($q->affected_rows > 0) {
            $catatan = "Pesanan dibatalkan oleh admin pusat";
            $status = "batal";
            $metode = "manual";
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

header("Location: ?page=kelola_pemesanan");
exit;
?>
