<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set header agar response berupa JSON
header('Content-Type: application/json; charset=utf-8');

// Lokasi koneksi dan helper stok otomatis
$root = dirname(__DIR__, 1); // naik satu folder dari posisi cek_stok.php
$konek_path = $root . '/database/konek.php';
$stok_helper_path = $root . '/includes/stok_otomatis.php';

// Cek file koneksi & helper
if (!file_exists($konek_path)) {
    echo json_encode(['success' => false, 'message' => "File koneksi tidak ditemukan ($konek_path)"]);
    exit;
}
if (!file_exists($stok_helper_path)) {
    echo json_encode(['success' => false, 'message' => "File helper stok tidak ditemukan ($stok_helper_path)"]);
    exit;
}

include $konek_path;
include $stok_helper_path;

// Ambil parameter
$tiket_id = isset($_GET['tiket_id']) ? intval($_GET['tiket_id']) : 0;
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

if ($tiket_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID tiket tidak valid.']);
    exit;
}

// Panggil fungsi stok otomatis
try {
    $stok_tersisa = getStokTersisa($konek, $tiket_id, $tanggal);

    if ($stok_tersisa > 0) {
        echo json_encode(['success' => true, 'stok_tersisa' => $stok_tersisa]);
    } else {
        echo json_encode(['success' => false, 'stok_tersisa' => 0, 'message' => 'Tiket habis untuk tanggal tersebut.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Kesalahan: ' . $e->getMessage()]);
}
?>
