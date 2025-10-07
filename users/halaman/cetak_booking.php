<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../../database/konek.php';
include "session_cek.php";
// Tidak perlu include navbar/footer agar hasil cetak bersih

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
    WHERE p.kode_booking = ? AND p.user_id = ? -- Tambahkan pengecekan user_id
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

<!-- Bootstrap CSS untuk tampilan cetak -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container my-5" id="printArea">
    <div class="card p-4 shadow-lg border-0">
        <h4 class="text-center mb-4 fw-bold">Bukti Booking Tiket</h4>
        
        <div class="row mb-2">
            <div class="col-md-6"><strong>Kode Booking</strong></div>
            <div class="col-md-6"><?= htmlspecialchars($data_booking['kode_booking']); ?></div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6"><strong>Nama Pemesan</strong></div>
            <div class="col-md-6"><?= htmlspecialchars($data_booking['nama_lengkap']); ?></div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6"><strong>Email</strong></div>
            <div class="col-md-6"><?= htmlspecialchars($data_booking['email']); ?></div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6"><strong>Nama Paket Wisata</strong></div>
            <div class="col-md-6"><?= htmlspecialchars($data_booking['nama_paket']); ?></div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6"><strong>Durasi Wisata</strong></div>
            <div class="col-md-6"><?= htmlspecialchars($data_booking['durasi']); ?></div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6"><strong>Tanggal Kunjungan</strong></div>
            <div class="col-md-6"><?= date('d-m-Y', strtotime($data_booking['tanggal_kunjungan'])); ?></div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6"><strong>Jumlah Tiket</strong></div>
            <div class="col-md-6"><?= intval($data_booking['jumlah_tiket']); ?></div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6"><strong>Total Harga</strong></div>
            <div class="col-md-6">Rp <?= number_format($data_booking['total_harga'], 0, ',', '.'); ?></div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6"><strong>Metode Pembayaran</strong></div>
            <div class="col-md-6 text-capitalize"><?= htmlspecialchars($data_booking['metode_pembayaran']); ?></div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6"><strong>Status</strong></div>
            <div class="col-md-6 text-capitalize"><?= ucfirst(htmlspecialchars($data_booking['status'])); ?></div>
        </div>

        <hr class="my-3">
        <p class="text-center text-muted mb-4">Silakan tunjukkan bukti booking ini kepada admin saat melakukan pembayaran.</p>
    </div>
</div>

<div class="text-center no-print">
    <button class="btn btn-primary" onclick="window.print()">Cetak Bukti Booking</button>
    <button type="button" class="btn btn-primary" onclick="window.location.href='riwayat.php'">Kembali</button>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #printArea, #printArea * {
            visibility: visible;
        }
        #printArea {
            position: absolute;
            left: 0;
            top: 0;
        }
    }
    .no-print {
        display: block;
    }
    @media print {
       .no-print {
          display: none !important;
       }
    }
</style>