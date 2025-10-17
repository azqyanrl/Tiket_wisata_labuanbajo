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

    // Ambil SEMUA data dari form
    $nama_paket = $_POST['nama_paket'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $durasi = $_POST['durasi'];
    $kategori = $_POST['kategori'];
    $status = $_POST['status'];
    $stok = $_POST['stok'];
    // TANGKAP DATA DARI FIELD BARU
    $fasilitas = $_POST['fasilitas'];
    $itinerary = $_POST['itinerary'];
    $syarat = $_POST['syarat'];

    // Ambil nama gambar lama jika sedang edit
    $gambar_lama = '';
    if ($editing) {
        $query_tiket = $konek->prepare("SELECT gambar FROM tiket WHERE id = ?");
        $query_tiket->bind_param("i", $tiket_id);
        $query_tiket->execute();
        $result = $query_tiket->get_result();
        if ($data = $result->fetch_assoc()) {
            $gambar_lama = $data['gambar'];
        }
    }

    $gambar = $gambar_lama;
    if (!empty($_FILES['gambar']['name'])) {
        $upload_dir = __DIR__ . '/../../assets/images/tiket';

        $imageFileType = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowed_types)) {
            $_SESSION['error_message'] = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
            header("Location: ../index.php?page=kelola_tiket");
            exit();
        }

        if ($_FILES['gambar']['size'] > 2097152) {
            $_SESSION['error_message'] = "Maaf, ukuran file gambar terlalu besar. Maksimal 2MB.";
            header("Location: ../index.php?page=kelola_tiket");
            exit();
        }

        $new_filename = uniqid() . '_' . basename($_FILES['gambar']['name']);
        $target_file = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
            $gambar = $new_filename;
            if ($editing && !empty($gambar_lama)) {
                $old_file = $upload_dir . $gambar_lama;
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }
        } else {
            $_SESSION['error_message'] = "Gagal mengupload gambar. Periksa folder dan permission.";
            header("Location: ../index.php?page=kelola_tiket");
            exit();
        }
    }

    // Proses simpan ke database
    try {
        if ($editing) {
            // PERBAIKAN: Query UPDATE dengan field baru
            $query_update = $konek->prepare("UPDATE tiket SET nama_paket=?, deskripsi=?, harga=?, durasi=?, kategori=?, status=?, gambar=?, stok=?, fasilitas=?, itinerary=?, syarat=? WHERE id=?");
            // PERBAIKAN: bind_param dengan variabel baru
            $query_update->bind_param("ssdssssisssi", $nama_paket, $deskripsi, $harga, $durasi, $kategori, $status, $gambar, $stok, $fasilitas, $itinerary, $syarat, $tiket_id);

            $query_update->execute();
        } else {
            // PERBAIKAN: Query INSERT dengan field baru
            $query_insert = $konek->prepare("INSERT INTO tiket (nama_paket, deskripsi, harga, durasi, kategori, status, gambar, stok, fasilitas, itinerary, syarat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            // PERBAIKAN: bind_param dengan variabel baru
            $query_insert->bind_param("ssdssssisss",$nama_paket,$deskripsi,$harga,$durasi,$kategori,$status,$gambar,$stok,$fasilitas,$itinerary,$syarat);

            $query_insert->execute();
        }

        $_SESSION['success_message'] = "Tiket berhasil disimpan.";
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Terjadi kesalahan database: " . $e->getMessage();
    }

    header("Location: ../index.php?page=kelola_tiket");
    exit();
}
