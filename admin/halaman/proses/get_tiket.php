<?php
// PENTING: pastikan file ini disimpan tanpa BOM dan tidak ada spasi sebelum <?php

// Matikan tampilkan error ke browser, tapi tetap log error ke file log PHP
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Mulai buffering untuk menangkap output tak diinginkan (warning, html, spasi, dll)
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek role admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    // bersihkan buffer dan log jika ada output tak diinginkan
    $buf = ob_get_clean();
    if (!empty($buf)) error_log("[get_tiket.php] stray output before JSON (access denied): " . $buf);

    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Akses ditolak! Anda tidak memiliki izin.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Sesuaikan path include jika struktur folder kamu beda.
// Biasanya file ini berada di: admin/tiket/proses/get_tiket.php
// dan konek.php berada di: admin/database/konek.php -> path '../../database/konek.php'
$include_path = __DIR__ . '/../../../database/konek.php';
if (!file_exists($include_path)) {
    $buf = ob_get_clean();
    if (!empty($buf)) error_log("[get_tiket.php] stray output before JSON (missing konek.php): " . $buf);

    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Internal error: database config not found.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// include koneksi (harus tidak meng-output apa-apa)
include $include_path;

// Hapus semua output yang mungkin dihasilkan include atau sebelumnya
$buf = ob_get_clean();
if (!empty($buf)) {
    // catat isi buffer ke error log supaya kita bisa debug kenapa ada output
    error_log("[get_tiket.php] stray output before JSON (after include): " . $buf);
}

// Pastikan header JSON
header('Content-Type: application/json; charset=utf-8');

// Validasi ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parameter ID tidak valid.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$id = (int) $_GET['id'];

try {
    // Prepared statement aman
    if (!($stmt = $konek->prepare("SELECT * FROM tiket WHERE id = ?"))) {
        throw new Exception("DB prepare error");
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();

        // kirim JSON (pastikan enkoding benar)
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Data tiket tidak ditemukan.'], JSON_UNESCAPED_UNICODE);
    }

    $stmt->close();
    $konek->close();
    exit;

} catch (Exception $e) {
    // log error, jangan tampilkan detil ke user
    error_log("[get_tiket.php] exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Terjadi kesalahan server.'], JSON_UNESCAPED_UNICODE);
    exit;
}
