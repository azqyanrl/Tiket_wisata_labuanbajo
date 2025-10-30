<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'posko') {
    $_SESSION['error_message'] = 'Akses ditolak!';
    header('Location: login/login.php');
    exit;
}
include '../../database/konek.php';
include '../../includes/boot.php';
 $lokasi_admin = $_SESSION['lokasi'];

// Query untuk mendapatkan data pemesanan BERDASARKAN LOKASI ADMIN
 $sql = "SELECT p.*, t.nama_paket, u.nama_lengkap
        FROM pemesanan p
        JOIN tiket t ON p.tiket_id = t.id
        JOIN users u ON p.user_id = u.id
        WHERE t.lokasi = ?
        ORDER BY p.created_at DESC";
 $stmt = $konek->prepare($sql);
 $stmt->bind_param('s', $lokasi_admin);
 $stmt->execute();
 $res = $stmt->get_result();

// Query untuk statistik
 $stats_sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN p.status = 'dibayar' THEN 1 ELSE 0 END) as verified,
                SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) as pending
              FROM pemesanan p
              JOIN tiket t ON p.tiket_id = t.id
              WHERE t.lokasi = ?";
 $stats_stmt = $konek->prepare($stats_sql);
 $stats_stmt->bind_param('s', $lokasi_admin);
 $stats_stmt->execute();
 $stats_result = $stats_stmt->get_result();
 $stats = $stats_result->fetch_assoc();
?>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5>Total Tiket</h5>
                <h2><?= $stats['total'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5>Terverifikasi</h5>
                <h2><?= $stats['verified'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5>Menunggu</h5>
                <h2><?= $stats['pending'] ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Daftar Pemesanan Tiket</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Kode Booking</th>
                        <th>Pelanggan</th>
                        <th>Paket</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res->num_rows > 0): ?>
                        <?php while($row = $res->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['kode_booking']) ?></td>
                            <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($row['nama_paket']) ?></td>
                            <td><?= htmlspecialchars($row['tanggal_kunjungan']) ?></td>
                            <td><?= 'Rp ' . number_format($row['total_harga'], 0, ',', '.') ?></td>
                            <td><span class="badge bg-info"><?= ucfirst($row['status']) ?></span></td>
                            <td>
                                <a href="?page=verifikasi_tiket&kode=<?= urlencode($row['kode_booking']) ?>" class="btn btn-sm btn-primary">Verifikasi</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">Tidak ada data pemesanan</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>