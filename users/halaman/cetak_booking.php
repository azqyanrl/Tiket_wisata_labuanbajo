<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../../database/konek.php';
include '../../includes/boot.php';
include "session_cek.php";

// Validasi user login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location='../login/login.php';</script>";
    exit;
}

$kode_booking_user = isset($_GET['kode_booking']) ? trim($_GET['kode_booking']) : '';
if (empty($kode_booking_user)) {
    echo "<div class='container my-5'><div class='alert alert-danger text-center'>Kode booking tidak ditemukan.</div></div>";
    exit;
}

// Ambil data booking
$query_booking = $konek->prepare("
    SELECT 
        p.kode_booking, p.tanggal_kunjungan, p.jumlah_tiket, p.total_harga, p.status, p.metode_pembayaran, p.created_at,
        t.nama_paket, t.durasi,
        u.nama_lengkap, u.email
    FROM pemesanan p
    JOIN tiket t ON p.tiket_id = t.id
    JOIN users u ON p.user_id = u.id
    WHERE p.kode_booking = ? AND p.user_id = ?
    LIMIT 1
");
$query_booking->bind_param("si", $kode_booking_user, $_SESSION['user_id']);
$query_booking->execute();
$hasil_booking = $query_booking->get_result();
$data_booking = $hasil_booking->fetch_assoc();
$query_booking->close();

if (!$data_booking) {
    echo "<div class='container my-5'><div class='alert alert-warning text-center'>Data booking tidak ditemukan atau Anda tidak memiliki akses.</div></div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../includes/cetak_booking.css">
    <title>Document</title>
</head>
<body>
    
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Card Utama -->
            <div class="card shadow-lg border-0 overflow-hidden" id="printArea">
                <!-- Card Header -->
                <div class="card-header bg-primary text-white text-center py-4 position-relative">
                    <div class="position-absolute top-0 start-0 p-3">
                        <h1 class="h3 mb-0"><i class="bi bi-geo-alt-fill"></i> LB</h1>
                    </div>
                    <h2 class="card-title h2 mb-2">Bukti Booking Tiket</h2>
                    <p class="card-text text-white-50 mb-0">Wisata Labuan Bajo - Nusa Tenggara Timur</p>
                </div>

                <!-- Card Body -->
                <div class="card-body p-4">
                    <!-- Informasi Utama -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="bi bi-upc-scan text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-0">Kode Booking</h6>
                                    <h5 class="mb-0 fw-bold"><?= htmlspecialchars($data_booking['kode_booking']); ?></h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-0">Status</h6>
                                    <h5 class="mb-0">
                                        <span class="badge bg-<?= $data_booking['status'] == 'confirmed' ? 'success' : 'warning'; ?> text-uppercase">
                                            <?= htmlspecialchars($data_booking['status']); ?>
                                        </span>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Detail Informasi -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-primary fw-bold mb-3"><i class="bi bi-person-fill me-2"></i>Informasi Pemesan</h6>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Nama Lengkap</small>
                                    <span class="fw-semibold"><?= htmlspecialchars($data_booking['nama_lengkap']); ?></span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Email</small>
                                    <span class="fw-semibold"><?= htmlspecialchars($data_booking['email']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-primary fw-bold mb-3"><i class="bi bi-geo-alt-fill me-2"></i>Detail Paket</h6>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Paket Wisata</small>
                                    <span class="fw-semibold"><?= htmlspecialchars($data_booking['nama_paket']); ?></span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Durasi</small>
                                    <span class="fw-semibold"><?= htmlspecialchars($data_booking['durasi']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-primary fw-bold mb-3"><i class="bi bi-calendar3 me-2"></i>Jadwal Kunjungan</h6>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Tanggal</small>
                                    <span class="fw-semibold"><?= date('d F Y', strtotime($data_booking['tanggal_kunjungan'])); ?></span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Jumlah Tiket</small>
                                    <span class="fw-semibold"><?= intval($data_booking['jumlah_tiket']); ?> Tiket</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-primary fw-bold mb-3"><i class="bi bi-currency-dollar me-2"></i>Informasi Pembayaran</h6>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Total Harga</small>
                                    <h5 class="text-success mb-0">Rp <?= number_format($data_booking['total_harga'], 0, ',', '.'); ?></h5>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Metode</small>
                                    <span class="fw-semibold text-capitalize"><?= htmlspecialchars($data_booking['metode_pembayaran']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                   <!-- Catatan Penting -->
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                        <div>
                            <strong>Catatan Penting:</strong> 
                            <ul class="mb-0 mt-2">
                                <li>Silakan tunjukkan bukti booking ini kepada admin saat melakukan pembayaran/verifikasi.</li>
                                <li>Tiket berlaku untuk masuk pada tanggal <strong><?= date('d F Y', strtotime($data_booking['tanggal_kunjungan'])); ?></strong> selama jam operasional atau sesuai ketentuan itinerary di tiket.</li>
                            </ul>
                        </div>
                    </div>

                <!-- Card Footer -->
                <div class="card-footer bg-light py-3 text-center">
                    <small class="text-muted">
                        <i class="bi bi-telephone-fill me-1"></i> (0385) 123456 |
                        <i class="bi bi-envelope-fill me-1"></i> info@labuanbajotourism.com
                    </small>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="text-center mt-4 no-print">
                <button class="btn btn-primary btn-lg px-5" onclick="window.print()">
                    <i class="bi bi-printer-fill me-2"></i> Cetak Bukti Booking
                </button>
                <a href="riwayat.php" class="btn btn-outline-primary btn-lg px-5 ms-2">
                    <i class="bi bi-arrow-left-circle me-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>