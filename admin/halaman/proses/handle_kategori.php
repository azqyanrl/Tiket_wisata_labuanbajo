<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek akses admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Akses ditolak!";
    header('location: ../../../login/login.php');
    exit;
}

// Path ke database dari folder /halaman/proses/ (naik 3 level)
include '../../../database/konek.php';

try {
    $editing = isset($_POST['id']) && !empty($_POST['id']);
    
    if ($editing) {
        $stmt = $konek->prepare("UPDATE kategori SET nama = ?, deskripsi = ? WHERE id = ?");
        $stmt->bind_param("ssi", $_POST['nama'], $_POST['deskripsi'], $_POST['id']);
        $stmt->execute();
        $_SESSION['success_message'] = "Kategori berhasil diperbarui.";
    } else {
        $stmt = $konek->prepare("INSERT INTO kategori (nama, deskripsi) VALUES (?, ?)");
        $stmt->bind_param("ss", $_POST['nama'], $_POST['deskripsi']);
        $stmt->execute();
        $_SESSION['success_message'] = "Kategori berhasil ditambahkan.";
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    if ($e->getCode() == 1062) { // Error code for duplicate entry
        $_SESSION['error_message'] = "Nama kategori sudah digunakan!";
    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
    }
}

header("Location: ../index.php?page=kelola_kategori");
exit;
?>