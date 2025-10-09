<?php
include '../../../database/konek.php';

// Ambil data galeri untuk mendapatkan nama file gambar
 $query_galeri = $konek->prepare("SELECT gambar FROM galleries WHERE id = ?");
 $query_galeri->bind_param("i", $_GET['id']);
 $query_galeri->execute();
 $result_galeri = $query_galeri->get_result();
 $galeri = $result_galeri->fetch_assoc();

if ($galeri) {
    // Hapus file gambar dari server
    $file_path = '../../../assets/images/' . $galeri['gambar'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    // Hapus data dari database
    $query_hapus = $konek->prepare("DELETE FROM galleries WHERE id = ?");
    $query_hapus->bind_param("i", $_GET['id']);
    $query_hapus->execute();
    
    $_SESSION['success_message'] = "Foto berhasil dihapus.";
}

header("Location: ../index.php?page=kelola_galeri");
?>