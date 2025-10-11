<?php

include '../../database/konek.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $judul = $_POST['judul']; 
    $kategori = $_POST['kategori']; 
    
    // Proses upload gambar
    $gambar = basename($_FILES['gambar']['name']); 
    $upload_dir = __DIR__ . '/../../assets/images/'; // Path lebih aman
    $target_file = $upload_dir . $gambar;
    
    // Validasi tipe file
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowed_types)) {
        $_SESSION['error_message'] = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
        header("Location: ../galeri.php"); // Redirect kembali
        exit();
    }
    
    // Coba upload file
    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
        // Jika upload berhasil, simpan ke database
        $query_insert = $konek->prepare("INSERT INTO galleries (judul, gambar, kategori) VALUES (?, ?, ?)");
        $query_insert->bind_param("sss", $judul, $gambar, $kategori); 
        if ($query_insert->execute()) {
            $_SESSION['success_message'] = "Foto berhasil ditambahkan.";
        } else {
            $_SESSION['error_message'] = "Gagal menyimpan data ke database.";
        }
    } else {
        $_SESSION['error_message'] = "Maaf, terjadi kesalahan saat mengupload file gambar.";
    }
    
    // Setelah semua proses selesai, redirect ke halaman galeri
    header("Location: ../galeri.php");
    exit();
}


header("Location: ../galeri.php");
exit();
?>