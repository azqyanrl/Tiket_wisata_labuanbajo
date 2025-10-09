<?php

include '../../../database/konek.php';

 $query_hapus = $konek->prepare("DELETE FROM tiket WHERE id = ?");
 $query_hapus->bind_param("i", $_GET['id']);
 $query_hapus->execute();

 $_SESSION['success_message'] = "Tiket berhasil dihapus.";
header("Location: ../index.php?page=kelola_tiket");
?>