<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    $_SESSION['flash_message'] = 'Silakan login terlebih dahulu.';
    header('Location:../login/login.php');
    exit;
}

?>