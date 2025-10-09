<?php

include '../../../database/konek.php';

// Cegah admin menghapus akunnya sendiri
if($_GET['id'] == $_SESSION['user_id']) {
    die("Tidak bisa menghapus akun sendiri.");
}

 $query_hapus = $konek->prepare("DELETE FROM users WHERE id = ?");
 $query_hapus->bind_param("i", $_GET['id']);
 $query_hapus->execute();

 $_SESSION['success_message'] = "User berhasil dihapus.";
header("Location: ../index.php?page=kelola_user");
?>