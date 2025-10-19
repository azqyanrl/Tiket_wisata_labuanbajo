<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../../../database/konek.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $kategori = trim($_POST['kategori']);

    // Direktori upload
    $upload_dir = __DIR__ . '/../../../assets/images/galery/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Cek file upload
    if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error_message'] = "Gagal menerima file upload.";
        header("Location: ../index.php?page=kelola_galeri");
        exit;
    }

    // Validasi tipe file
    $gambar_asli = basename($_FILES['gambar']['name']);
    $imageFileType = strtolower(pathinfo($gambar_asli, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($imageFileType, $allowed_types)) {
        $_SESSION['error_message'] = "Hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
        header("Location: ../index.php?page=kelola_galeri");
        exit;
    }

    // Validasi ukuran (maks 2MB)
    if ($_FILES['gambar']['size'] > 2097152) {
        $_SESSION['error_message'] = "Ukuran file terlalu besar (maksimal 2MB).";
        header("Location: ../index.php?page=kelola_galeri");
        exit;
    }

    // Buat nama unik dan simpan
    $nama_baru = uniqid('img_', true) . '.' . $imageFileType;
    $target_file = $upload_dir . $nama_baru;

    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
        $stmt = $konek->prepare("INSERT INTO galleries (judul, gambar, kategori) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $judul, $nama_baru, $kategori);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Foto berhasil ditambahkan 🎉";
        } else {
            $_SESSION['error_message'] = "Gagal menyimpan data ke database.";
            unlink($target_file);
        }

        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan saat mengupload file.";
    }

    header("Location: ../index.php?page=kelola_galeri");
    exit;
}

$_SESSION['error_message'] = "Akses tidak sah.";
header("Location: ../index.php?page=kelola_galeri");
exit;
?>
