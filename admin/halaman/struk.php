<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak!'); window.close();</script>";
    exit;
}

include '../../database/konek.php';

if (!isset($_GET['id'])) {
    die("ID pemesanan tidak ditemukan.");
}

$id = intval($_GET['id']);

// Ambil data struk
$query = $konek->prepare("
    SELECT 
        p.*, 
        u.nama_lengkap, 
        u.no_hp, 
        t.nama_paket,
        t.lokasi,
        t.durasi,
        t.itinerary
    FROM pemesanan p
    JOIN users u ON p.user_id = u.id
    JOIN tiket t ON p.tiket_id = t.id
    WHERE p.id = ?
");
$query->bind_param("i", $id);
$query->execute();
$data = $query->get_result()->fetch_assoc();

if (!$data) {
    die("Data tidak ditemukan.");
}

// Tentukan tanggal kunjungan
$tanggal_kunjungan = $data['tanggal_kunjungan'];

// Deteksi jam dari itinerary (misal 08:00)
$jam_mulai = null;
if (preg_match('/(\d{1,2}[:.]\d{2})/', $data['itinerary'], $match)) {
    $jam_mulai = $match[1];
}

// Hitung masa berlaku
if ($jam_mulai) {
    $tanggal_kadaluarsa = date('Y-m-d H:i', strtotime("$tanggal_kunjungan $jam_mulai +12 hour"));
} else {
    $tanggal_kadaluarsa = date('Y-m-d H:i', strtotime("$tanggal_kunjungan +1 day"));
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Struk Pemesanan</title>

<style>
    body {
        font-family: 'Courier New', monospace;
        width: 300px;
        margin: auto;
        font-size: 14px;
    }
    .struk-box {
        border: 1px dashed #000;
        padding: 15px;
        margin-top: 10px;
    }
    .line { border-bottom: 1px dashed #000; margin: 10px 0; }
    .center { text-align: center; }
    .no-print { text-align:center; margin-top:15px; }
    @media print {
        .no-print { display: none !important; }
    }
    a.button {
        display:inline-block; 
        padding:8px 14px; 
        border:1px solid #000; 
        text-decoration:none; 
        background:#eee;
    }
</style>

<script>
// Tampilkan tombol kembali setelah print
function tampilkanTombolKembali() {
    document.getElementById("btn-kembali").style.display = "block";
}

window.onload = function() {
    window.print();
}

// Event setelah print selesai
window.onafterprint = tampilkanTombolKembali;

// Fungsi tombol kembali
function kembali() {
    // Jika tab dibuka dari window lain, tutup tab
    if (window.opener) {
        window.close();
    } else {
        // Jika dibuka langsung, redirect ke halaman pemesanan
        window.location.href = 'index.php?page=kelola_pemesanan';
    }
}
</script>

</head>
<body>

<div class="struk-box">
    <h2 class="center">LABUAN BAJO TOUR</h2>
    <div class="center">Struk Pemesanan Resmi</div>

    <div class="line"></div>

    <b>Kode Booking:</b> <?= $data['kode_booking'] ?><br>
    <b>Nama:</b> <?= $data['nama_lengkap'] ?><br>
    <b>No HP:</b> <?= $data['no_hp'] ?><br>

    <div class="line"></div>

    <b>Paket:</b> <?= $data['nama_paket'] ?><br>
    <b>Lokasi:</b> <?= $data['lokasi'] ?><br>
    <b>Durasi:</b> <?= $data['durasi'] ?><br>
    <b>Tanggal Kunjungan:</b> <?= $tanggal_kunjungan ?><br>
    <b>Berlaku Sampai:</b> <?= $tanggal_kadaluarsa ?><br>

    <div class="line"></div>

    <b>Jumlah Tiket:</b> <?= $data['jumlah_tiket'] ?><br>
    <b>Total Bayar:</b> Rp <?= number_format($data['total_harga'], 0, ',', '.') ?><br>
    <b>Metode Pembayaran:</b> <?= $data['metode_pembayaran'] ?><br>
    <b>Status:</b> <?= strtoupper($data['status']) ?><br>

    <?php if (!empty($data['itinerary'])): ?>
        <div class="line"></div>
        <b>Itinerary:</b><br>
        <?= nl2br(htmlspecialchars($data['itinerary'])) ?>
    <?php endif; ?>

    <div class="line"></div>

    <div class="center">
        Terima kasih telah memesan 😊<br>
        <small>Dicetak: <?= date('d/m/Y H:i') ?></small>
    </div>
</div>

<!-- Tombol kembali -->
<div id="btn-kembali" class="no-print" style="display:none;">
    <a href="javascript:kembali()" class="button">
        ← Kembali
    </a>
</div>

</body>
</html>
