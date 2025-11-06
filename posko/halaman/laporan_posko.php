<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'posko') {
    $_SESSION['error_message'] = 'Akses ditolak!';
    header('Location: login/login.php');
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php'; 

$lokasi_admin = $_SESSION['lokasi'];

// Filter tanggal
$filter_type = $_GET['filter_type'] ?? 'today';
$date_from = $_GET['date_from'] ?? date('Y-m-d');
$date_to = $_GET['date_to'] ?? date('Y-m-d');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
    $_SESSION['error_message'] = 'Format tanggal tidak valid';
    header('Location: ?page=laporan_posko');
    exit;
}

// Tentukan filter tanggal
switch($filter_type) {
    case 'today': $date_condition = "DATE(p.created_at) = CURDATE()"; break;
    case 'yesterday': $date_condition = "DATE(p.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)"; break;
    case 'week': $date_condition = "WEEK(p.created_at) = WEEK(CURDATE()) AND YEAR(p.created_at) = YEAR(CURDATE())"; break;
    case 'month': $date_condition = "MONTH(p.created_at) = MONTH(CURDATE()) AND YEAR(p.created_at) = YEAR(CURDATE())"; break;
    case 'custom': $date_condition = "DATE(p.created_at) BETWEEN ? AND ?"; break;
    default: $date_condition = "DATE(p.created_at) = CURDATE()";
}

try {
    // === Statistik Ringkas ===
    $stats_sql = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN p.status = 'dibayar' THEN 1 ELSE 0 END) as verified,
            SUM(CASE WHEN p.status = 'selesai' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) as pending
        FROM pemesanan p
        JOIN tiket t ON p.tiket_id = t.id
        WHERE t.lokasi = ? AND $date_condition
    ";
    $stats_stmt = $konek->prepare($stats_sql);
    if ($filter_type === 'custom') {
        $stats_stmt->bind_param('sss', $lokasi_admin, $date_from, $date_to);
    } else {
        $stats_stmt->bind_param('s', $lokasi_admin);
    }
    $stats_stmt->execute();
    $stats = $stats_stmt->get_result()->fetch_assoc();

    // === Data laporan (semua status) ===
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
        WHERE t.lokasi = ? AND $date_condition
        ORDER BY p.created_at DESC
    ";
    $stmt = $konek->prepare($sql);
    if ($filter_type === 'custom') {
        $stmt->bind_param('sss', $lokasi_admin, $date_from, $date_to);
    } else {
        $stmt->bind_param('s', $lokasi_admin);
    }
    $stmt->execute();
    $res = $stmt->get_result();

    // Hitung total pendapatan dan tiket selesai
    $total_pendapatan = 0;
    $total_tiket_selesai = 0;
    while ($r = $res->fetch_assoc()) {
        if ($r['status'] === 'selesai' || $r['status'] === 'dibayar') {
            $total_pendapatan += $r['total_harga'];
            $total_tiket_selesai += $r['jumlah_tiket'];
        }
    }
    $res->data_seek(0);

} catch (Exception $e) {
    $_SESSION['error_message'] = 'Kesalahan: ' . $e->getMessage();
    header('Location: ?page=laporan_posko');
    exit;
}
?>

<style>
@media print {
    .no-print, .navbar, .sidebar { display: none !important; }
    body { margin: 0; padding: 0; }
    .container { width: 100%; }
}
</style>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
        <h1 class="h3">Laporan Posko <?= htmlspecialchars($lokasi_admin) ?></h1>
    </div>

    <!-- Filter -->
    <div class="card shadow-sm mb-4 no-print">
        <div class="card-header bg-white fw-bold">Filter Tanggal</div>
        <div class="card-body">
            <form method="get" action="?page=laporan_posko">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Filter</label>
                        <select name="filter_type" class="form-select" onchange="toggleDateRange()">
                            <option value="today" <?= $filter_type=='today'?'selected':'' ?>>Hari Ini</option>
                            <option value="yesterday" <?= $filter_type=='yesterday'?'selected':'' ?>>Kemarin</option>
                            <option value="week" <?= $filter_type=='week'?'selected':'' ?>>Minggu Ini</option>
                            <option value="month" <?= $filter_type=='month'?'selected':'' ?>>Bulan Ini</option>
                            <option value="custom" <?= $filter_type=='custom'?'selected':'' ?>>Rentang Tanggal</option>
                        </select>
                    </div>
                    <div class="col-md-3" id="dateFromContainer" style="display:<?= $filter_type=='custom'?'block':'none' ?>">
                        <label class="form-label">Dari</label>
                        <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
                    </div>
                    <div class="col-md-3" id="dateToContainer" style="display:<?= $filter_type=='custom'?'block':'none' ?>">
                        <label class="form-label">Sampai</label>
                        <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Terapkan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistik -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-start border-4 border-primary shadow h-100">
                <div class="card-body">
                    <h6>Total Pendapatan</h6>
                    <h5>Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-4 border-success shadow h-100">
                <div class="card-body">
                    <h6>Tiket Selesai</h6>
                    <h5><?= $total_tiket_selesai ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-4 border-info shadow h-100">
                <div class="card-body">
                    <h6>Terverifikasi</h6>
                    <h5><?= $stats['verified'] + $stats['completed'] ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-4 border-warning shadow h-100">
                <div class="card-body">
                    <h6>Menunggu</h6>
                    <h5><?= $stats['pending'] ?></h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Laporan -->
    <div class="card shadow-sm">
        <div class="card-header bg-white fw-bold">Detail Transaksi</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Pelanggan</th>
                            <th>Paket</th>
                            <th>Tanggal Kunjungan</th>
                            <th>Jumlah</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Verifikasi Oleh</th>
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
                                <td><?= (int)$row['jumlah_tiket'] ?></td>
                                <td>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                                <td>
                                    <?php
                                    $color = match($row['status']) {
                                        'pending' => 'warning',
                                        'dibayar' => 'info',
                                        'selesai' => 'success',
                                        'batal' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $color ?>"><?= ucfirst($row['status']) ?></span>
                                </td>
                                <td><?= $row['verifikator'] ? htmlspecialchars($row['verifikator']) : '<span class="text-muted">Belum Diverifikasi</span>' ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center text-muted">Tidak ada transaksi pada periode ini</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Cetak -->
    <div class="no-print text-end mt-3">
        <button class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer"></i> Cetak Laporan
        </button>
    </div>
</div>

<script>
function toggleDateRange() {
    const type = document.querySelector('select[name="filter_type"]').value;
    document.getElementById('dateFromContainer').style.display = type === 'custom' ? 'block' : 'none';
    document.getElementById('dateToContainer').style.display = type === 'custom' ? 'block' : 'none';
}
</script>
