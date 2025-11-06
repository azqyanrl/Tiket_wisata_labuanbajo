<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    $_SESSION['error_message'] = "Akses ditolak! Anda harus login sebagai user.";
    header('location: ../login/login.php');
    exit;
}
?>