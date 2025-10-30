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
// Ambil lokasi admin dari session
 $lokasi_admin = $_SESSION['lokasi'];

// Filter berdasarkan tanggal
 $filter_type = $_GET['filter_type'] ?? 'today';
 $date_from = $_GET['date_from'] ?? date('Y-m-d');
 $date_to = $_GET['date_to'] ?? date('Y-m-d');

// Validasi input tanggal
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
    $_SESSION['error_message'] = 'Format tanggal tidak valid';
    header('Location: ?page=laporan_posko');
    exit;
}

// Menentukan query berdasarkan filter
switch($filter_type) {
    case 'today':
        $date_condition = "DATE(p.created_at) = CURDATE()";
        break;
    case 'yesterday':
        $date_condition = "DATE(p.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
        break;
    case 'week':
        $date_condition = "WEEK(p.created_at) = WEEK(CURDATE()) AND YEAR(p.created_at) = YEAR(CURDATE())";
        break;
    case 'month':
        $date_condition = "MONTH(p.created_at) = MONTH(CURDATE()) AND YEAR(p.created_at) = YEAR(CURDATE())";
        break;
    case 'custom':
        $date_condition = "DATE(p.created_at) BETWEEN ? AND ?";
        break;
    default:
        $date_condition = "DATE(p.created_at) = CURDATE()";
}

try {
    // --- QUERY 1: UNTUK STATISTIK KARTU (SEMUA STATUS) ---
    $stats_sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN p.status = 'dibayar' THEN 1 ELSE 0 END) as verified,
                    SUM(CASE WHEN p.status = 'selesai' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) as pending
                  FROM pemesanan p
                  JOIN tiket t ON p.tiket_id = t.id
                  WHERE t.lokasi = ? AND $date_condition";
    
    $stats_stmt = $konek->prepare($stats_sql); // Menggunakan $konek
    if ($filter_type === 'custom') {
        $stats_stmt->bind_param('sss', $lokasi_admin, $date_from, $date_to);
    } else {
        $stats_stmt->bind_param('s', $lokasi_admin);
    }
    $stats_stmt->execute();
    $stats = $stats_stmt->get_result()->fetch_assoc();

    // --- QUERY 2: UNTUK TABEL LAPORAN (HANYA YANG SELESAI) ---
    $sql = "SELECT p.*, t.nama_paket, u.nama_lengkap
            FROM pemesanan p
            JOIN tiket t ON p.tiket_id = t.id
            JOIN users u ON p.user_id = u.id
            WHERE t.lokasi = ? AND p.status = 'selesai' AND $date_condition
            ORDER BY p.created_at DESC";

    $stmt = $konek->prepare($sql); // Menggunakan $konek
    if ($filter_type === 'custom') {
        $stmt->bind_param('sss', $lokasi_admin, $date_from, $date_to);
    } else {
        $stmt->bind_param('s', $lokasi_admin);
    }
    
    $stmt->execute();
    $res = $stmt->get_result();
    
    // Hitung total pendapatan dan total tiket SELESAI
    $total_pendapatan = 0;
    $total_tiket_selesai = 0;
    while ($row = $res->fetch_assoc()) {
        $total_pendapatan += $row['total_harga'];
        $total_tiket_selesai += $row['jumlah_tiket'];
    }
    $res->data_seek(0);

} catch (Exception $e) {
    $_SESSION['error_message'] = 'Terjadi kesalahan database: ' . $e->getMessage();
    header('Location: ?page=laporan_posko');
    exit;
}
?>

<style>
/* CSS Khusus untuk mencetak */
@media print {
    /* Sembunyikan elemen yang tidak perlu */
    .no-print,
    .btn,
    .navbar,
    .sidebar { /* Asumsikan ada class sidebar di navbar.php */
        display: none !important;
    }
    
    /* Pastikan konten mengambil seluruh halaman */
    body {
        margin: 0 !important;
        padding: 0 !important;
    }
    .container {
        max-width: 100% !important;
        width: 100% !important;
        padding: 0 !important;
    }

    /* Pastikan teks berwarna hitam dan latar putih */
    .card, .table, .badge {
        color: black !important;
        background: white !important;
        border: 1px solid #ddd !important;
    }
    
    /* Hilangkan shadow */
    .shadow-sm {
        box-shadow: none !important;
    }
}
</style>

<div class="container mt-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
        <h1 class="h2">Laporan Penjualan (Selesai)</h1>
    </div>

    <!-- Filter Tanggal -->
    <div class="card shadow-sm mb-4 no-print">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">Filter Tanggal</h6>
        </div>
        <div class="card-body">
            <form method="get" action="?page=laporan_posko">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Filter</label>
                        <select name="filter_type" class="form-select" onchange="toggleDateRange()">
                            <option value="today" <?= $filter_type == 'today' ? 'selected' : '' ?>>Hari Ini</option>
                            <option value="yesterday" <?= $filter_type == 'yesterday' ? 'selected' : '' ?>>Kemarin</option>
                            <option value="week" <?= $filter_type == 'week' ? 'selected' : '' ?>>Minggu Ini</option>
                            <option value="month" <?= $filter_type == 'month' ? 'selected' : '' ?>>Bulan Ini</option>
                            <option value="custom" <?= $filter_type == 'custom' ? 'selected' : '' ?>>Rentang Tanggal</option>
                        </select>
                    </div>
                    <div class="col-md-3" id="dateFromContainer" style="display: <?= $filter_type == 'custom' ? 'block' : 'none' ?>;">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($date_from) ?>">
                    </div>
                    <div class="col-md-3" id="dateToContainer" style="display: <?= $filter_type == 'custom' ? 'block' : 'none' ?>;">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($date_to) ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Terapkan Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-4 border-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-uppercase fw-bold text-primary fs-6">Total Pendapatan</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?= 'Rp ' . number_format($total_pendapatan, 0, ',', '.') ?></div>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-cash-stack fs-1 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-4 border-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-uppercase fw-bold text-success fs-6">Tiket Selesai</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?= $total_tiket_selesai ?></div>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-check-circle fs-1 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-4 border-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-uppercase fw-bold text-warning fs-6">Terverifikasi</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?= $stats['verified'] + $stats['completed'] ?></div>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-clock-history fs-1 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-4 border-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-uppercase fw-bold text-danger fs-6">Menunggu</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?= $stats['pending'] ?></div>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-hourglass-split fs-1 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Laporan -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">Detail Transaksi Selesai</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Kode Booking</th>
                            <th>Pelanggan</th>
                            <th>Paket</th>
                            <th>Tanggal Kunjungan</th>
                            <th>Jumlah</th>
                            <th>Total</th>
                            <th>Metode</th>
                            <th>Status</th>
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
                                <td><?= htmlspecialchars($row['jumlah_tiket']) ?></td>
                                <td><?= 'Rp ' . number_format($row['total_harga'], 0, ',', '.') ?></td>
                                <td><?= htmlspecialchars($row['metode_pembayaran']) ?></td>
                                <td><span class="badge bg-success"><?= ucfirst($row['status']) ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Tidak ada transaksi yang selesai pada periode ini</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tombol Cetak -->
    <div class="no-print text-end mb-4">
        <button class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer"></i> Cetak Laporan
        </button>
    </div>
</div>

<script>
function toggleDateRange() {
    const filterType = document.querySelector('select[name="filter_type"]').value;
    const dateFromContainer = document.getElementById('dateFromContainer');
    const dateToContainer = document.getElementById('dateToContainer');
    
    if (filterType === 'custom') {
        dateFromContainer.style.display = 'block';
        dateToContainer.style.display = 'block';
    } else {
        dateFromContainer.style.display = 'none';
        dateToContainer.style.display = 'none';
    }
}
</script>
