<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../../../database/konek.php';
include '../../../includes/boot.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_galeri = $_GET['id'];
    
    // Ambil data gambar untuk dihapus dari folder
    $query_gambar = $konek->prepare("SELECT gambar FROM galleries WHERE id = ?");
    $query_gambar->bind_param("i", $id_galeri);
    $query_gambar->execute();
    $result_gambar = $query_gambar->get_result();
    
    if ($result_gambar->num_rows > 0) {
        $data_gambar = $result_gambar->fetch_assoc();
        $gambar_path = __DIR__ . '/../../assets/images/' . $data_gambar['gambar'];
        
        // Hapus file gambar jika ada
        if (file_exists($gambar_path)) {
            unlink($gambar_path);
        }
        
        // Hapus dari database
        $stmt = $konek->prepare("DELETE FROM galleries WHERE id = ?");
        $stmt->bind_param("i", $id_galeri);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Foto berhasil dihapus.";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus foto dari database.";
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Foto tidak ditemukan.";
    }
} else {
    $_SESSION['error_message'] = "ID foto tidak valid.";
}

header("Location: ../galeri.php");
exit();
?>