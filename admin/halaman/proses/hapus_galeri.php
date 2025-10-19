<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../../../database/konek.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_galeri = $_GET['id'];

    $query_gambar = $konek->prepare("SELECT gambar FROM galleries WHERE id = ?");
    $query_gambar->bind_param("i", $id_galeri);
    $query_gambar->execute();
    $result = $query_gambar->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $path = __DIR__ . '/../../assets/images/galery/' . $data['gambar'];
        if (file_exists($path)) unlink($path);

        $stmt = $konek->prepare("DELETE FROM galleries WHERE id = ?");
        $stmt->bind_param("i", $id_galeri);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Foto berhasil dihapus.";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus data dari database.";
        }

        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Foto tidak ditemukan.";
    }
} else {
    $_SESSION['error_message'] = "ID foto tidak valid.";
}

header("Location: ../index.php?page=kelola_galeri");
exit;
?>
