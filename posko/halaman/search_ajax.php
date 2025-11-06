<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'posko') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}

include '../../database/konek.php';

 $kode = trim($_GET['kode_booking'] ?? '');
 $lokasi_admin = $_SESSION['lokasi'];

if (strlen($kode) < 3) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Kode terlalu pendek']);
    exit;
}

 $stmt = $konek->prepare("
    SELECT p.kode_booking, u.nama_lengkap, t.nama_paket, p.status 
    FROM pemesanan p 
    JOIN tiket t ON p.tiket_id = t.id 
    JOIN users u ON p.user_id = u.id 
    WHERE p.kode_booking LIKE ? AND t.lokasi = ?
    ORDER BY p.kode_booking
    LIMIT 10
");
 $search_param = "%$kode%";
 $stmt->bind_param('ss', $search_param, $lokasi_admin);
 $stmt->execute();
 $result = $stmt->get_result();

 $data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $data]);