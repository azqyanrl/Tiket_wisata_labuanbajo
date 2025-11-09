<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include '../../database/konek.php';
header('Content-Type: application/json; charset=utf-8');

$kode_booking = trim($_GET['kode_booking'] ?? '');

if (strlen($kode_booking) < 3) {
    echo json_encode(['success' => false, 'message' => 'Masukkan minimal 3 huruf kode booking']);
    exit;
}

$query = $konek->prepare("
    SELECT p.kode_booking, p.status, u.nama_lengkap, t.nama_paket
    FROM pemesanan p
    JOIN users u ON p.user_id = u.id
    JOIN tiket t ON p.tiket_id = t.id
    WHERE p.kode_booking LIKE ?
      AND p.status = 'pending'
    ORDER BY p.id DESC
");

$like = '%' . $kode_booking . '%';
$query->bind_param('s', $like);
$query->execute();
$result = $query->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    'success' => !empty($data),
    'total_found' => count($data),
    'data' => $data,
    'message' => empty($data) ? 'Tidak ada kode booking pending yang cocok.' : null
]);
?>
