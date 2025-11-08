
<?php include '../includes/boot.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Admin | Wisata Labuan Bajo</title>
    <style>
        .hero-wisata {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('../assets/images/bg/padar3.jpg') no-repeat center center;
            background-size: cover;
            height: 100vh;
        }
    </style>
</head>
<body>

    <header class="hero-wisata d-flex align-items-center text-white">
        <div class="container text-center">
            <!-- Ikon untuk memperkuat tema wisata -->
            <i class="bi bi-compass fs-1 mb-3"></i>
            <h1 class="display-3 fw-bold">Portal Administrator</h1>
            <h2 class="display-6">Wisata Labuan Bajo</h2>
            <p class="lead mt-3">Kelola keindahan dan potensi pariwisata dunia dari satu pusat kendali.</p>
            
            <!-- Tombol Utama Menuju Login -->
            <a href="login/login.php" class="btn btn-success btn-lg mt-4">
                <i class="bi bi-lock-fill me-2"></i> Masuk ke Dashboard
            </a>
        </div>
    </header>

    <main>
        <section class="container my-5 py-5">
            <div class="row text-center g-4">
                <!-- Kolom 1: Manajemen Destinasi -->
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <i class="bi bi-map-fill text-primary fs-1"></i>
                            <h5 class="card-title mt-3">Destinasi</h5>
                            <p class="card-text">Kelola informasi spot wisata, Pulau Komodo, Pink Beach, dan lainnya.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <i class="bi bi-calendar-check-fill text-success fs-1"></i>
                            <h5 class="card-title mt-3">Pemesanan</h5>
                            <p class="card-text">Pantau dan kelola seluruh data pemesanan trip dan paket wisata.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <i class="bi bi-graph-up-arrow text-info fs-1"></i>
                            <h5 class="card-title mt-3">Analitik</h5>
                            <p class="card-text">Lihat statistik pengunjung, tren popularitas, dan laporan pendapatan.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <i class="bi bi-people-fill text-warning fs-1"></i>
                            <h5 class="card-title mt-3">Mitra</h5>
                            <p class="card-text">Atur data hotel, tour guide, dan penyedia jasa wisata lainnya.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <footer class="bg-dark text-white text-center py-3">
        <div class="container">
            <p class="mb-0">&copy; 2025 Dinas Pariwisata Labuan Bajo. All Rights Reserved.</p>
        </div>
    </footer>


   
   </body>
</html>