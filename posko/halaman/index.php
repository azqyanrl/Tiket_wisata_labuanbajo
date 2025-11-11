<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🛡️ Cek login admin posko
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'posko') {
    $_SESSION['error_message'] = 'Akses ditolak!';
    header('Location: login/login.php');
    exit;
}
include '../../database/konek.php';
include '../../includes/boot.php';
$page = $_GET['page'] ?? 'posko_dashboard';
$lokasi_admin = $_SESSION['lokasi'] ?? 'Posko';

// 🔖 Judul otomatis berdasarkan halaman
$page_titles = [
    'posko_dashboard' => 'Dashboard',
    'cari_tiket' => 'Cari & Verifikasi Tiket',
    'verifikasi_tiket' => 'Verifikasi Tiket',
    'laporan_posko' => 'Laporan Posko',
    'profil_posko' => 'Profil Posko',
];
$judul_halaman = $page_titles[$page] ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($judul_halaman) ?> - <?= htmlspecialchars($lokasi_admin) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
        }
        @media print {
            .no-print, .btn, .dropdown, .border-bottom {
                display: none !important;
            }
            body, .card, .table {
                background: white !important;
                color: black !important;
                box-shadow: none !important;
            }
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row flex-nowrap">

        <!-- Sidebar -->
        <nav class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 bg-light border-end sidebar no-print">
            <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-dark min-vh-100">
                <a href="#" class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-dark text-decoration-none">
                    <span class="fs-5 fw-bold">Posko <?= htmlspecialchars($lokasi_admin) ?></span>
                </a>
                <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start" id="menu">

                    <li class="nav-item w-100">
                        <a href="?page=posko_dashboard"
                           class="nav-link text-dark <?= $page == 'posko_dashboard' ? 'active bg-primary text-white' : '' ?>">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    </li>

                    <li class="nav-item w-100">
                        <a href="?page=cari_tiket"
                           class="nav-link text-dark <?= $page == 'cari_tiket' ? 'active bg-primary text-white' : '' ?>">
                            <i class="bi bi-search me-2"></i> Cari & Verifikasi Tiket
                        </a>
                    </li>

                    <li class="nav-item w-100">
                        <a href="?page=laporan_posko"
                           class="nav-link text-dark <?= $page == 'laporan_posko' ? 'active bg-primary text-white' : '' ?>">
                            <i class="bi bi-file-earmark-text me-2"></i> Laporan
                        </a>
                    </li>

                    <li><hr class="w-100"></li>

                    <li class="nav-item w-100">
                        <a href="?page=profil_posko"
                           class="nav-link text-dark <?= $page == 'profil_posko' ? 'active bg-primary text-white' : '' ?>">
                            <i class="bi bi-person-circle me-2"></i> Profil
                        </a>
                    </li>

                    <li class="nav-item w-100">
                        <a href="login/logout.php" class="nav-link text-dark">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Konten Utama -->
        <main class="col py-3 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-4 border-bottom pb-2">
                <h1 class="h4 fw-bold text-primary">
                    <i class="bi bi-house-door-fill"></i> <?= htmlspecialchars($judul_halaman) ?> - <?= htmlspecialchars($lokasi_admin) ?>
                </h1>
            </div>

            <?php
            switch ($page) {
                case 'cari_tiket':
                    include 'cari_tiket.php';
                    break;

                case 'laporan_posko':
                    include 'laporan_posko.php';
                    break;

                case 'verifikasi_tiket':
                    include 'verifikasi_tiket.php';
                    break;

                case 'profil_posko':
                    include 'profil_posko.php';
                    break;

                default:
                    include 'posko_dashboard.php';
                    break;
            }
            ?>
        </main>
    </div>
</div>

</body>
</html>
