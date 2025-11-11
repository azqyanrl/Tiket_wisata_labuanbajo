<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak!'); document.location.href='../login/login.php';</script>";
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';

$pemesanan_id = intval($_GET['id'] ?? 0);

// Ambil data pemesanan
$query = $konek->prepare("
    SELECT p.*, u.nama_lengkap, t.nama_paket, t.lokasi as nama_posko
    FROM pemesanan p
    JOIN users u ON p.user_id = u.id
    JOIN tiket t ON p.tiket_id = t.id
    WHERE p.id = ?
");
$query->bind_param("i", $pemesanan_id);
$query->execute();
$pemesanan = $query->get_result()->fetch_assoc();

if (!$pemesanan) {
    echo '<div class="alert alert-danger">Pemesanan tidak ditemukan.</div>';
    exit();
}

// Ambil riwayat verifikasi
$history_query = $konek->prepare("
    SELECT vh.*, u.nama_lengkap as admin_nama, u.lokasi as admin_posko
    FROM verifikasi_history vh
    JOIN users u ON vh.admin_id = u.id
    WHERE vh.pemesanan_id = ?
    ORDER BY vh.created_at DESC
");
$history_query->bind_param("i", $pemesanan_id);
$history_query->execute();
$history = $history_query->get_result();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Detail Pemesanan</h1>
    <a href="?page=kelola_pemesanan" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<!-- Informasi Pemesanan -->
<div class="card mb-4">
    <div class="card-header">
        <h5>Informasi Pemesanan</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Kode Booking:</strong> <?= htmlspecialchars($pemesanan['kode_booking'] ?? '') ?></p>
                <p><strong>Pelanggan:</strong> <?= htmlspecialchars($pemesanan['nama_lengkap'] ?? '') ?></p>
                <p><strong>Paket:</strong> <?= htmlspecialchars($pemesanan['nama_paket'] ?? '') ?></p>
                <p><strong>Posko:</strong> <span class="badge bg-secondary"><?= htmlspecialchars($pemesanan['nama_posko'] ?? '') ?></span></p>
            </div>
            <div class="col-md-6">
                <p><strong>Jumlah Tiket:</strong> <?= (int)($pemesanan['jumlah_tiket'] ?? 0) ?></p>
                <p><strong>Total Harga:</strong> Rp <?= number_format($pemesanan['total_harga'] ?? 0, 0, ',', '.') ?></p>
                <p><strong>Status:</strong> <span class="badge bg-info"><?= ucfirst(htmlspecialchars($pemesanan['status'] ?? '')) ?></span></p>
                <p><strong>Tanggal:</strong> <?= !empty($pemesanan['created_at']) ? date('d/m/Y H:i', strtotime($pemesanan['created_at'])) : '-' ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Riwayat Verifikasi -->
<div class="card">
    <div class="card-header">
        <h5>Riwayat Verifikasi</h5>
    </div>
    <div class="card-body">
        <?php if ($history && $history->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Admin</th>
                            <th>Posko Admin</th>
                            <th>Status</th>
                            <th>Metode</th>
                            <th>Catatan</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $history->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['admin_nama'] ?? '') ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($row['admin_posko'] ?? '') ?></span></td>
                            <td><span class="badge bg-info"><?= ucfirst(htmlspecialchars($row['status'] ?? '')) ?></span></td>
                            <td><?= htmlspecialchars($row['metode_pembayaran'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['catatan'] ?? '') ?></td>
                            <td><?= !empty($row['created_at']) ? date('d/m/Y H:i:s', strtotime($row['created_at'])) : '-' ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted">Belum ada riwayat verifikasi</p>
        <?php endif; ?>
    </div>
</div>
