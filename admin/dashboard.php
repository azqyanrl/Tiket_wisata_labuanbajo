<?php
session_start();
include "boot.php";

if (!isset($_SESSION['username'])) {
    echo "<script>document.location.href='login/login.php';</script>";
    exit;
}
?>