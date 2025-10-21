<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Akses ditolak! Anda harus login sebagai admin.";
    header('location: ../../login/login_admin.php');
    exit;
}

include '../../../database/konek.php';
include '../../../includes/boot.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $editing = isset($_POST['id']) && !empty($_POST['id']);
    $tiket_id = $_POST['id'] ?? null;

    $nama_paket = $_POST['nama_paket'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $durasi = $_POST['durasi'];
    $kategori = $_POST['kategori'];
    $status = $_POST['status'];
    $stok = $_POST['stok'];

    // Ambil gambar lama
    $gambar_lama = '';
    if ($editing) {
        $cek = $konek->prepare("SELECT gambar FROM tiket WHERE id=?");
        $cek->bind_param("i", $tiket_id);
        $cek->execute();
        $res = $cek->get_result();
        if ($res->num_rows > 0) {
            $gambar_lama = $res->fetch_assoc()['gambar'];
        }
    }

    // Upload gambar
    $gambar = $gambar_lama;
    if (!empty($_FILES['gambar']['name'])) {
        $upload_dir = __DIR__ . '/../../../assets/images/tiket/';
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowed)) {
            $new_name = uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_dir . $new_name)) {
                $gambar = $new_name;
                if ($editing && !empty($gambar_lama) && file_exists($upload_dir . $gambar_lama)) {
                    unlink($upload_dir . $gambar_lama);
                }
            }
        }
    }

    try {
        if ($editing) {
            $stmt = $konek->prepare("UPDATE tiket SET nama_paket=?, deskripsi=?, harga=?, durasi=?, kategori=?, status=?, gambar=?, stok=? WHERE id=?");
            $stmt->bind_param("ssdssssii", $nama_paket, $deskripsi, $harga, $durasi, $kategori, $status, $gambar, $stok, $tiket_id);
            $stmt->execute();
        } else {
            $stmt = $konek->prepare("INSERT INTO tiket (nama_paket, deskripsi, harga, durasi, kategori, status, gambar, stok) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdssssi", $nama_paket, $deskripsi, $harga, $durasi, $kategori, $status, $gambar, $stok);
            $stmt->execute();
        }

        $_SESSION['success_message'] = "Tiket berhasil disimpan!";
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Kesalahan database: " . $e->getMessage();
    }

    header("Location: ../index.php?page=kelola_tiket");
    exit;
}
