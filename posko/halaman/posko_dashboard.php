<?php
try {
    // Query untuk mendapatkan semua pemesanan
    $sql = "SELECT p.*, t.nama_paket, t.lokasi, u.nama_lengkap
            FROM pemesanan p
            JOIN tiket t ON p.tiket_id = t.id
            JOIN users u ON p.user_id = u.id
            WHERE t.lokasi = ?
            ORDER BY p.created_at DESC";
    $stmt = $konek->prepare($sql);
    $stmt->bind_param('s', $lokasi);
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
    $stats_stmt->bind_param('s', $lokasi);
    $stats_stmt->execute();
    $stats_result = $stats_stmt->get_result();
    $stats = $stats_result->fetch_assoc();
    
} catch (Exception $e) {
    $_SESSION['error_message'] = 'Terjadi kesalahan database: ' . $e->getMessage();
    header('Location: index.php');
    exit;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-primary mb-0">
        <i class="bi bi-speedometer2 me-2"></i>Dashboard Posko
    </h2>
</div>

<div class="row g-4 mb-4">
    <!-- Total Tiket -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body bg-primary text-white rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-light mb-1">Total Tiket</h6>
                        <h2 class="fw-bold"><?= $stats['total'] ?></h2>
                    </div>
                    <i class="bi bi-ticket-perforated fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Terverifikasi -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body bg-success text-white rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-light mb-1">Terverifikasi</h6>
                        <h2 class="fw-bold"><?= $stats['verified'] ?></h2>
                    </div>
                    <i class="bi bi-check-circle fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Menunggu -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body bg-warning text-dark rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Menunggu</h6>
                        <h2 class="fw-bold"><?= $stats['pending'] ?></h2>
                    </div>
                    <i class="bi bi-hourglass-split fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Pemesanan -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold text-primary">
            <i class="bi bi-list-check me-2"></i>Daftar Pemesanan Tiket
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-primary text-center">
                    <tr>
                        <th>Kode Booking</th>
                        <th>Pelanggan</th>
                        <th>Paket</th>
                        <th>Tanggal</th>
                        <th>Jumlah</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res->num_rows > 0): ?>
                        <?php while($row = $res->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-semibold text-center"><?= htmlspecialchars($row['kode_booking']) ?></td>
                            <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($row['nama_paket']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($row['tanggal_kunjungan']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($row['jumlah_tiket']) ?></td>
                            <td class="text-end fw-semibold"><?= 'Rp ' . number_format($row['total_harga'], 0, ',', '.') ?></td>
                            <td class="text-center">
                                <?php 
                                $badgeClass = match($row['status']) {
                                    'pending' => 'bg-warning text-dark',
                                    'dibayar' => 'bg-success',
                                    'selesai' => 'bg-info',
                                    'batal' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?> px-3 py-2">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="?page=verifikasi_tiket&kode=<?= urlencode($row['kode_booking']) ?>" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-check-circle"></i> Verifikasi
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data pemesanan</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>