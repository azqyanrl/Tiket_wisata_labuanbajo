<?php
// Filter berdasarkan tanggal
 $filter_type = $_GET['filter_type'] ?? 'today';
 $date_from = $_GET['date_from'] ?? date('Y-m-d');
 $date_to = $_GET['date_to'] ?? date('Y-m-d');

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
        $date_condition = "DATE(p.created_at) BETWEEN '$date_from' AND '$date_to'";
        break;
    default:
        $date_condition = "DATE(p.created_at) = CURDATE()";
}

// Query untuk mendapatkan data laporan
 $sql = "SELECT p.*, t.nama_paket, u.nama_lengkap
        FROM pemesanan p
        JOIN tiket t ON p.tiket_id = t.id
        JOIN users u ON p.user_id = u.id
        WHERE t.lokasi = ? AND $date_condition
        ORDER BY p.created_at DESC";
 $stmt = $konek->prepare($sql);
 $stmt->bind_param('s', $lokasi);
 $stmt->execute();
 $res = $stmt->get_result();

// Query untuk statistik
 $total_pendapatan = 0;
 $total_tiket = 0;
 $status_counts = [
    'pending' => 0,
    'dibayar' => 0,
    'selesai' => 0,
    'batal' => 0
];

while ($row = $res->fetch_assoc()) {
    $total_pendapatan += $row['total_harga'];
    $total_tiket += $row['jumlah_tiket'];
    $status_counts[$row['status']]++;
}

// Reset result pointer untuk menampilkan data lagi
 $res->data_seek(0);
?>

<div class="page-title">
    <h1>Laporan Penjualan</h1>
</div>

<!-- Filter Tanggal -->
<div class="card shadow-sm mb-4 no-print">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filter Tanggal</h6>
    </div>
    <div class="card-body">
        <form method="get">
            <input type="hidden" name="page" value="laporan_posko">
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
        <div class="card shadow h-100" style="border-left: 4px solid #0d6efd;">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Pendapatan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= 'Rp ' . number_format($total_pendapatan, 0, ',', '.') ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-cash-stack fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow h-100" style="border-left: 4px solid #198754;">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Tiket</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_tiket ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-ticket-perforated fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow h-100" style="border-left: 4px solid #ffc107;">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Terverifikasi</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $status_counts['dibayar'] + $status_counts['selesai'] ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow h-100" style="border-left: 4px solid #dc3545;">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Menunggu</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $status_counts['pending'] ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-hourglass-split fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Laporan -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detail Transaksi</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Kode Booking</th>
                        <th>Pelanggan</th>
                        <th>Paket</th>
                        <th>Tanggal</th>
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
                            <td>
                                <?php 
                                $statusClass = '';
                                switch($row['status']) {
                                    case 'pending': $statusClass = 'bg-warning'; break;
                                    case 'dibayar': $statusClass = 'bg-success'; break;
                                    case 'selesai': $statusClass = 'bg-info'; break;
                                    case 'batal': $statusClass = 'bg-danger'; break;
                                }
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= ucfirst($row['status']) ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data transaksi</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="no-print text-end mb-4">
    <button class="btn btn-outline-secondary" onclick="window.print()">
        <i class="bi bi-printer"></i> Cetak Laporan
    </button>
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