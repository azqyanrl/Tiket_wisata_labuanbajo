<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Akses ditolak!";
    header('location: ../login/login.php');
    exit;
}

include '../../../database/konek.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $judul = trim($_POST['judul'] ?? '');
    $kategori_id = intval($_POST['kategori_id'] ?? 0);

    if ($judul === '' || $kategori_id === 0) {
        $_SESSION['error_message'] = "Judul dan kategori wajib diisi!";
        header("Location: ../index.php?page=kelola_galeri");
        exit;
    }

    $stmt = $konek->prepare("SELECT gambar FROM galleries WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data_lama = $result->fetch_assoc();
    $stmt->close();

    if (!$data_lama) {
        $_SESSION['error_message'] = "Data tidak ditemukan.";
        header("Location: ../index.php?page=kelola_galeri");
        exit;
    }

    $gambar_baru = $data_lama['gambar'];
    $upload_dir = __DIR__ . '/../../../assets/images/galery/';

    if (!empty($_FILES['gambar']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $_SESSION['error_message'] = "Format gambar tidak valid.";
            header("Location: ../index.php?page=kelola_galeri");
            exit;
        }

        if ($_FILES['gambar']['size'] > 2097152) {
            $_SESSION['error_message'] = "Ukuran file terlalu besar (maks 2MB).";
            header("Location: ../index.php?page=kelola_galeri");
            exit;
        }

        $nama_baru = uniqid('img_', true) . '.' . $ext;
        $target_file = $upload_dir . $nama_baru;

        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
            $old_file = $upload_dir . $data_lama['gambar'];
            if (file_exists($old_file)) unlink($old_file);
            $gambar_baru = $nama_baru;
        } else {
            $_SESSION['error_message'] = "Upload gagal.";
            header("Location: ../index.php?page=kelola_galeri");
            exit;
        }
    }

    $update = $konek->prepare("UPDATE galleries SET judul=?, kategori_id=?, gambar=? WHERE id=?");
    $update->bind_param("sisi", $judul, $kategori_id, $gambar_baru, $id);
    if ($update->execute()) {
        $_SESSION['success_message'] = "Foto berhasil diperbarui ✅";
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui data.";
    }
    $update->close();

    header("Location: ../index.php?page=kelola_galeri");
    exit;
}

$_SESSION['error_message'] = "Akses tidak sah.";
header("Location: ../index.php?page=kelola_galeri");
exit;
?>
