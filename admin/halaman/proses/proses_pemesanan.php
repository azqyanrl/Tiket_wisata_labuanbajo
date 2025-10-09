<?php

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('location: ../../login/login.php');
    exit;
}

include '../../../database/konek.php';

// ... sisa kode tidak berubah

if (isset($_GET['id']) && isset($_GET['action'])) {
    $pemesanan_id = intval($_GET['id']); 
    $action = $_GET['action'];
    
    if ($action === 'confirm') {
        // 1. Update status pemesanan menjadi 'dibayar'
        $query_pemesanan = $konek->prepare("UPDATE pemesanan SET status = 'dibayar' WHERE id = ?");
        $query_pemesanan->bind_param("i", $pemesanan_id);
        $query_pemesanan->execute();
        
        // 2. Cek apakah sudah ada data di tabel payments untuk pemesanan ini
        $query_cek_payment = $konek->prepare("SELECT id FROM payments WHERE pemesanan_id = ?");
        $query_cek_payment->bind_param("i", $pemesanan_id);
        $query_cek_payment->execute();
        $result_cek_payment = $query_cek_payment->get_result();

        if ($result_cek_payment->num_rows > 0) {
            // Jika sudah ada, update statusnya menjadi 'verified'
            $query_payment = $konek->prepare("UPDATE payments SET status = 'verified' WHERE pemesanan_id = ?");
            $query_payment->bind_param("i", $pemesanan_id);
            $query_payment->execute();
        } else {
            // Jika belum ada, buat entri baru di payments
            // KITA TIDAK MEMBUAT ENTRI BARU DI TRANSACTIONS
            // KITA HANYA MEMBUAT ENTRI DI PAYMENTS DAN MENGGUNAKAN transaction_id YANG SUDAH ADA ATAU NULL
            $query_payment = $konek->prepare("INSERT INTO payments (pemesanan_id, transaction_id, bukti_transfer, status) VALUES (?, ?, ?, 'verified')");
            
            // Cari transaction_id yang terkait dengan pemesanan ini dari tabel transactions
            $query_get_transaction = $konek->prepare("SELECT id FROM transactions WHERE pemesanan_id = ?");
            $query_get_transaction->bind_param("i", $pemesanan_id);
            $query_get_transaction->execute();
            $result_get_transaction = $query_get_transaction->get_result();
            
            if ($result_get_transaction->num_rows > 0) {
                $transaction_data = $result_get_transaction->fetch_assoc();
                $transaction_id = $transaction_data['id'];
            } else {
                // Jika tidak ada di tabel transactions, kita bisa set NULL atau membuat entri baru
                // Untuk sederhananya, kita set NULL dulu
                $transaction_id = null;
            }
            
            $bukti_transfer = "Diverifikasi langsung oleh admin";
            $query_payment->bind_param("iis", $pemesanan_id, $transaction_id, $bukti_transfer);
            $query_payment->execute();
        }
        
        $_SESSION['success_message'] = "Pembayaran berhasil diverifikasi dan dikonfirmasi.";
        
    } elseif ($action === 'reject') {
        // 1. Update status payment menjadi 'rejected'
        $query_payment = $konek->prepare("UPDATE payments SET status = 'rejected' WHERE pemesanan_id = ?");
        $query_payment->bind_param("i", $pemesanan_id);
        $query_payment->execute();
        
        // 2. Update status pemesanan menjadi 'batal'
        $query_pemesanan = $konek->prepare("UPDATE pemesanan SET status = 'batal' WHERE id = ?");
        $query_pemesanan->bind_param("i", $pemesanan_id);
        $query_pemesanan->execute();
        
        $_SESSION['success_message'] = "Bukti pembayaran ditolak dan pemesanan dibatalkan.";
    }
}
header("Location: ../index.php?page=kelola_pemesanan"); 
exit();
?>