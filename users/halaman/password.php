<?php
require_once __DIR__ . '/../../includes/common.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php'); exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf($token)) {
    flash('error', 'Token CSRF tidak valid');
    header('Location: profile.php'); exit;
}

$userId = (int) $_SESSION['user_id'];
$old = $_POST['old_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($new !== $confirm) {
    flash('error', 'Konfirmasi password tidak cocok');
    header('Location: profile.php'); exit;
}
if (strlen($new) < 8) {
    flash('error', 'Password baru minimal 8 karakter');
    header('Location: profile.php'); exit;
}

$stmt = $konek->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row || !password_verify($old, $row['password'])) {
    flash('error', 'Password lama salah');
    header('Location: profile.php'); exit;
}

$newHash = password_hash($new, PASSWORD_DEFAULT);
$stmt = $konek->prepare('UPDATE users SET password = ? WHERE id = ?');
$stmt->bind_param('si', $newHash, $userId);
$stmt->execute();
$stmt->close();

// regenerate session id after sensitive change
session_regenerate_id(true);
flash('success', 'Password berhasil diubah');
header('Location: profile.php'); exit;
?>