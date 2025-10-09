<?php
include '../../database/konek.php';

// Fungsi untuk menginisialisasi stok harian
function inisialisasiStokHarian($koneksi) {
    // Ambil semua tiket yang aktif
    $query_tiket = $koneksi->query("SELECT id, stok FROM tiket WHERE status = 'aktif'");
    
    while ($tiket = $query_tiket->fetch_assoc()) {
        $tiket_id = $tiket['id'];
        $stok_default = $tiket['stok'];
        $tanggal = date('Y-m-d');
        
        // Cek apakah stok untuk hari ini sudah ada
        $query_cek = $koneksi->prepare("SELECT id FROM stok_harian WHERE tiket_id = ? AND tanggal = ?");
        $query_cek->bind_param("is", $tiket_id, $tanggal);
        $query_cek->execute();
        $result_cek = $query_cek->get_result();
        
        // Jika belum ada, buat entri baru
        if ($result_cek->num_rows == 0) {
            $query_insert = $koneksi->prepare("INSERT INTO stok_harian (tiket_id, tanggal, stok_tersisa) VALUES (?, ?, ?)");
            $query_insert->bind_param("isi", $tiket_id, $tanggal, $stok_default);
            $query_insert->execute();
        }
    }
}

// Fungsi untuk mengurangi stok saat pemesanan
function kurangiStok($koneksi, $tiket_id, $jumlah, $tanggal) {
    // Cek stok tersisa untuk tanggal tersebut
    $query_stok = $koneksi->prepare("SELECT stok_tersisa FROM stok_harian WHERE tiket_id = ? AND tanggal = ?");
    $query_stok->bind_param("is", $tiket_id, $tanggal);
    $query_stok->execute();
    $result_stok = $query_stok->get_result();
    
    if ($result_stok->num_rows > 0) {
        $stok = $result_stok->fetch_assoc();
        $stok_tersisa = $stok['stok_tersisa'] - $jumlah;
        
        // Update stok tersisa
        $query_update = $koneksi->prepare("UPDATE stok_harian SET stok_tersisa = ? WHERE tiket_id = ? AND tanggal = ?");
        $query_update->bind_param("iis", $stok_tersisa, $tiket_id, $tanggal);
        $query_update->execute();
        
        return $stok_tersisa >= 0; // Return true jika stok mencukupi
    } else {
        // Jika tidak ada entri stok untuk tanggal tersebut, buat baru
        $query_tiket = $koneksi->prepare("SELECT stok FROM tiket WHERE id = ?");
        $query_tiket->bind_param("i", $tiket_id);
        $query_tiket->execute();
        $result_tiket = $query_tiket->get_result();
        $tiket = $result_tiket->fetch_assoc();
        
        $stok_tersisa = $tiket['stok'] - $jumlah;
        
        $query_insert = $koneksi->prepare("INSERT INTO stok_harian (tiket_id, tanggal, stok_tersisa) VALUES (?, ?, ?)");
        $query_insert->bind_param("isi", $tiket_id, $tanggal, $stok_tersisa);
        $query_insert->execute();
        
        return $stok_tersisa >= 0; // Return true jika stok mencukupi
    }
}

// Fungsi untuk mendapatkan stok tersedia untuk tanggal tertentu
function getStokTersedia($koneksi, $tiket_id, $tanggal) {
    $query_stok = $koneksi->prepare("SELECT stok_tersisa FROM stok_harian WHERE tiket_id = ? AND tanggal = ?");
    $query_stok->bind_param("is", $tiket_id, $tanggal);
    $query_stok->execute();
    $result_stok = $query_stok->get_result();
    
    if ($result_stok->num_rows > 0) {
        $stok = $result_stok->fetch_assoc();
        return $stok['stok_tersisa'];
    } else {
        // Jika tidak ada entri stok untuk tanggal tersebut, ambil stok default
        $query_tiket = $koneksi->prepare("SELECT stok FROM tiket WHERE id = ?");
        $query_tiket->bind_param("i", $tiket_id);
        $query_tiket->execute();
        $result_tiket = $query_tiket->get_result();
        $tiket = $result_tiket->fetch_assoc();
        
        // Buat entri stok baru untuk tanggal tersebut
        $query_insert = $koneksi->prepare("INSERT INTO stok_harian (tiket_id, tanggal, stok_tersisa) VALUES (?, ?, ?)");
        $query_insert->bind_param("isi", $tiket_id, $tanggal, $tiket['stok']);
        $query_insert->execute();
        
        return $tiket['stok'];
    }
}

// Panggil fungsi inisialisasi stok harian setiap kali file ini di-load
inisialisasiStokHarian($koneksi);
?>