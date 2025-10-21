<?php
include '../../database/konek.php';
header('Content-Type: application/json');

$tiket_id = intval($_GET['tiket_id'] ?? 0);
$tanggal = $_GET['tanggal'] ?? '';

if ($tiket_id <= 0 || !$tanggal) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid.']);
    exit;
}

$qstok = $konek->prepare("
    SELECT t.stok_total - IFNULL(SUM(p.jumlah_tiket), 0) AS stok_tersisa
    FROM tiket t
    LEFT JOIN pemesanan p ON t.id = p.tiket_id 
        AND p.tanggal_kunjungan = ? 
        AND p.status IN ('pending', 'dibayar', 'selesai')
    WHERE t.id = ?
    GROUP BY t.id
");
$qstok->bind_param("si", $tanggal, $tiket_id);
$qstok->execute();
$res = $qstok->get_result()->fetch_assoc();
$qstok->close();

$stok_tersisa = $res ? intval($res['stok_tersisa']) : 0;

if ($stok_tersisa > 0) {
    echo json_encode(['success' => true, 'stok_tersisa' => $stok_tersisa]);
} else {
    echo json_encode(['success' => false, 'message' => 'Tiket habis untuk tanggal tersebut.']);
}
?>
