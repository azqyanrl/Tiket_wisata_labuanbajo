<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login posko
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'posko') {
    echo "<script>alert('Akses ditolak!'); window.close();</script>";
    exit;
}

include '../../database/konek.php';

if (!isset($_GET['id'])) {
    echo "ID pemesanan tidak ditemukan.";
    exit;
}

$id = intval($_GET['id']);

// Ambil data struk
$query = $konek->prepare("
    SELECT 
        p.*, 
        u.nama_lengkap, 
        u.no_hp, 
        u.email,
        t.nama_paket,
        t.harga,
        t.lokasi,
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
    echo "Data tidak ditemukan.";
    exit;
}

// Tambahan: tanggal kadaluarsa tiket = H+1
$tanggal_kadaluarsa = date('d/m/Y', strtotime($data['tanggal_kunjungan'] . ' +1 day'));
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Struk Pemesanan | LabuanBajoTrip</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body { font-size: 14px; padding: 20px; }
    .struk-box { max-width: 480px; margin: auto; border: 1px solid #ddd; padding: 20px; border-radius: 8px; }
    .line { border-bottom: 1px dashed #999; margin: 10px 0; }

    /* Tombol kembali hilang saat print */
    @media print {
        #btn-kembali {
            display: none !important;
        }
    }
</style>

<script>
window.onload = function() {
    window.print();
}

// Setelah print selesai, tampilkan tombol kembali
window.onafterprint = function() {
    document.getElementById("btn-kembali").style.display = "block";
};

// Fungsi tombol kembali
function kembali() {
    if (window.opener) {
        // Jika dibuka dari halaman utama, tutup tab
        window.close();
    } else {
        // Jika dibuka langsung, redirect ke halaman pemesanan posko
        window.location.href = 'index.php?page=posko_dashboard';
    }
}
</script>
</head>

<body>

<div class="struk-box">
    <h4 class="text-center mb-0">🎟 STRUK PEMBAYARAN TIKET</h4>
    <p class="text-center text-muted">Posko: <?= htmlspecialchars($data['lokasi']) ?></p>
    <div class="line"></div>

    <h6><strong>Informasi Pemesanan</strong></h6>
    <p><strong>Kode Booking:</strong> <?= $data['kode_booking'] ?></p>
    <p><strong>Tanggal Kunjungan:</strong> <?= date('d/m/Y', strtotime($data['tanggal_kunjungan'])) ?></p>
    <p><strong>Masa Berlaku Tiket:</strong> <?= $tanggal_kadaluarsa ?> (H+1)</p>
    <p><strong>Status:</strong> <?= ucfirst($data['status']) ?></p>

    <div class="line"></div>

    <h6><strong>Data Pelanggan</strong></h6>
    <p><strong>Nama:</strong> <?= htmlspecialchars($data['nama_lengkap']) ?></p>
    <p><strong>No HP:</strong> <?= htmlspecialchars($data['no_hp']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($data['email']) ?></p>

    <div class="line"></div>

    <h6><strong>Detail Tiket</strong></h6>
    <p><strong>Paket:</strong> <?= htmlspecialchars($data['nama_paket']) ?></p>
    <p><strong>Harga per Tiket:</strong> Rp <?= number_format($data['harga'],0,',','.') ?></p>
    <p><strong>Jumlah Tiket:</strong> <?= $data['jumlah_tiket'] ?></p>
    <p><strong>Total Bayar:</strong> <b>Rp <?= number_format($data['total_harga'],0,',','.') ?></b></p>

    <?php if (!empty($data['itinerary'])): ?>
    <div class="line"></div>
    <h6><strong>Itinerary</strong></h6>
    <p><?= nl2br(htmlspecialchars($data['itinerary'])) ?></p>
    <?php endif; ?>

    <div class="line"></div>

    <p class="text-center text-muted mb-0">Terima kasih telah berkunjung!</p>
    <p class="text-center text-muted">Dicetak: <?= date('d/m/Y H:i') ?></p>
</div>

<!-- Tombol kembali -->
<div id="btn-kembali" style="display:none; text-align:center; margin-top:20px;">
    <a href="javascript:kembali()" class="btn btn-secondary">
        ← Kembali
    </a>
</div>

</body>
</html>
