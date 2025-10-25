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
    $judul = trim($_POST['judul'] ?? '');
    $kategori_id = intval($_POST['kategori_id'] ?? 0);

    if ($judul === '' || $kategori_id === 0) {
        $_SESSION['error_message'] = "Judul dan kategori wajib diisi!";
        header("Location: ../index.php?page=kelola_galeri");
        exit;
    }

    $upload_dir = __DIR__ . '/../../../assets/images/galery/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error_message'] = "Gambar tidak valid.";
        header("Location: ../index.php?page=kelola_galeri");
        exit;
    }

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        $_SESSION['error_message'] = "Format gambar tidak diizinkan.";
        header("Location: ../index.php?page=kelola_galeri");
        exit;
    }

    $nama_baru = uniqid('img_', true) . '.' . $ext;
    $target_file = $upload_dir . $nama_baru;

    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
        $stmt = $konek->prepare("INSERT INTO galleries (judul, gambar, kategori_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $judul, $nama_baru, $kategori_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Foto berhasil ditambahkan ✅";
        } else {
            $_SESSION['error_message'] = "Gagal menyimpan ke database.";
            unlink($target_file);
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Upload gagal.";
    }

    header("Location: ../index.php?page=kelola_galeri");
    exit;
}

$_SESSION['error_message'] = "Akses tidak sah.";
header("Location: ../index.php?page=kelola_galeri");
exit;
?>
