<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Validasi role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak! Anda tidak memiliki izin.']);
    exit;
}

include '../../../database/konek.php';

// ✅ Validasi ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parameter ID tidak valid.']);
    exit;
}

$id = (int) $_GET['id'];

// ✅ Query aman dengan prepared statement
$query = $konek->prepare("SELECT * FROM tiket WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();

if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    // Escape untuk mencegah XSS di client side
    foreach ($data as $key => $val) {
        if (is_string($val)) {
            $data[$key] = htmlspecialchars_decode($val, ENT_QUOTES);
        }
    }
    echo json_encode($data);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Data tiket tidak ditemukan.']);
}

$query->close();
$konek->close();
?>
