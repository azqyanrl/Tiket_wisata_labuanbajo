<?php
include '../../database/konek.php';
include '../boot.php';

session_start();

if (!isset($_SESSION['username'])) {
    echo "<script>document.location.href='../login/login.php';</script>";
    exit;
}

// Tambahkan pengecekan role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak! Hanya admin yang diizinkan.'); document.location.href='../login/login.php';</script>";
    exit;
}

// Cek koneksi
if ($konek->connect_error) {
    die("Koneksi gagal: " . $konek->connect_error);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard - Tiket Labuan Bajo</title>
</head>

<body style="height:100%; margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color:#f8f9fa;">
    <div class="d-flex" style="height:100vh;">
        <!-- Sidebar -->
        <nav id="sidebar" class="bg-dark text-white flex-shrink-0" style="min-height:100vh; width:250px;">
            <div class="p-3 border-bottom border-secondary text-center">
                <i class="bi bi-speedometer2 fs-1 text-primary"></i>
                <h3 class="mt-2">Admin Panel</h3>
            </div>
            <div class="nav flex-column px-2">
                <a href="dashboard.php" target="wadah" class="nav-link text-white bg-primary mb-1 rounded">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a href="tiket/index.php" target="wadah" class="nav-link text-white mb-1 rounded">
                    <i class="bi bi-ticket-perforated me-2"></i> Tiket
                </a>
                <a href="transactions/index.php" target="wadah" class="nav-link text-white mb-1 rounded">
                    <i class="bi bi-cart3 me-2"></i> Transaksi
                </a>
                <a href="users/index.php" target="wadah" class="nav-link text-white mb-1 rounded">
                    <i class="bi bi-people me-2"></i> Users
                </a>
                <a href="gallery/index.php" target="wadah" class="nav-link text-white mb-1 rounded">
                    <i class="bi bi-images me-2"></i> Galeri
                </a>
                <a href="reports/index.php" target="wadah" class="nav-link text-white mb-1 rounded">
                    <i class="bi bi-file-earmark-bar-graph me-2"></i> Laporan
                </a>
                <a href="settings/index.php" target="wadah" class="nav-link text-white mb-1 rounded">
                    <i class="bi bi-gear me-2"></i> Pengaturan
                </a>
                <a href="logout.php" class="nav-link text-danger mb-1 rounded">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </div>
        </nav>

        <!-- Content Area -->
        <div class="flex-grow-1">
            <iframe src="dashboard.php" name="wadah" style="width:100%; height:100vh; border:none;"></iframe>
        </div>
    </div>
</body>

</html>