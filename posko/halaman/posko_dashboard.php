<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login admin posko
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'posko') {
    $_SESSION['error_message'] = 'Akses ditolak!';
    header('Location: login/login.php');
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';

$lokasi_admin = $_SESSION['lokasi'];

// === QUERY UTAMA: Tampilkan semua pemesanan berdasarkan lokasi admin ===
// Termasuk data verifikasi dari admin pusat (LEFT JOIN ke verifikasi_history dan users)
$sql = "
    SELECT 
        p.*, 
        t.nama_paket, 
        u.nama_lengkap,
        vh.admin_id,
        ua.nama_lengkap AS verifikator
    FROM pemesanan p
    JOIN tiket t ON p.tiket_id = t.id
    JOIN users u ON p.user_id = u.id
    LEFT JOIN verifikasi_history vh ON vh.pemesanan_id = p.id
        AND vh.id = (SELECT MAX(id) FROM verifikasi_history WHERE pemesanan_id = p.id)
    LEFT JOIN users ua ON vh.admin_id = ua.id
    WHERE t.lokasi = ?
    ORDER BY p.created_at DESC
";
$stmt = $konek->prepare($sql);
$stmt->bind_param('s', $lokasi_admin);
$stmt->execute();
$res = $stmt->get_result();

// === QUERY STATISTIK ===
$stats_sql = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN p.status = 'dibayar' THEN 1 ELSE 0 END) as verified,
        SUM(CASE WHEN p.status = 'selesai' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) as pending
    FROM pemesanan p
    JOIN tiket t ON p.tiket_id = t.id
    WHERE t.lokasi = ?
";
$stats_stmt = $konek->prepare($stats_sql);
$stats_stmt->bind_param('s', $lokasi_admin);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();
?>

<!-- Statistik Kartu -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary shadow-sm">
            <div class="card-body">
                <h5>Total Tiket</h5>
                <h2><?= $stats['total'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success shadow-sm">
            <div class="card-body">
                <h5>Terverifikasi</h5>
                <h2><?= $stats['verified'] + $stats['completed'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info shadow-sm">
            <div class="card-body">
                <h5>Selesai</h5>
                <h2><?= $stats['completed'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning shadow-sm">
            <div class="card-body">
                <h5>Menunggu</h5>
                <h2><?= $stats['pending'] ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Pemesanan -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="m-0 fw-bold text-primary">Daftar Pemesanan Tiket</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Kode Booking</th>
                        <th>Pelanggan</th>
                        <th>Paket</th>
                        <th>Tanggal Kunjungan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Verifikasi Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res->num_rows > 0): ?>
                        <?php while ($row = $res->fetch_assoc()): ?>
                            <?php
                            $badgeClass = match($row['status']) {
                                'pending' => 'bg-warning text-dark',
                                'dibayar' => 'bg-info text-dark',
                                'selesai' => 'bg-success',
                                'batal' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['kode_booking']) ?></td>
                                <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                <td><?= htmlspecialchars($row['nama_paket']) ?></td>
                                <td><?= htmlspecialchars($row['tanggal_kunjungan']) ?></td>
                                <td><?= 'Rp ' . number_format($row['total_harga'], 0, ',', '.') ?></td>
                                <td><span class="badge <?= $badgeClass ?>"><?= ucfirst($row['status']) ?></span></td>
                                <td>
                                    <?= $row['verifikator'] 
                                        ? htmlspecialchars($row['verifikator']) 
                                        : '<span class="text-muted">Belum diverifikasi</span>' ?>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'pending'): ?>
                                        <a href="?page=verifikasi_tiket&kode=<?= urlencode($row['kode_booking']) ?>" 
                                           class="btn btn-sm btn-primary">
                                           <i class="bi bi-check-circle"></i> Verifikasi
                                        </a>
                                    <?php elseif ($row['status'] === 'dibayar'): ?>
                                        <span class="badge bg-success">Sudah Diverifikasi</span>
                                    <?php elseif ($row['status'] === 'selesai'): ?>
                                        <span class="badge bg-info">Selesai</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted">Tidak ada data pemesanan</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
