<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// include koneksi
require_once __DIR__ . '/../database/konek.php'; 

function generate_csrf() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function flash($key = null, $value = null) {
    if ($key === null) return;
    if ($value === null) {
        $v = $_SESSION[$key] ?? null;
        unset($_SESSION[$key]);
        return $v;
    }
    $_SESSION[$key] = $value;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        flash('error', 'Silakan login terlebih dahulu.');
        header('Location: ../users/login/login.php');
        exit;
    }
}

// helper untuk meng-escape output (XSS-safe)
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>