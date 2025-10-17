<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Akses ditolak! Anda harus login sebagai admin.";
    header('location: ../login/login.php');
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';
include '../../includes/alerts.php';

// Ambil filter tanggal dan tipe laporan
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'bulanan';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// --- QUERY RINGKASAN PERIODE ---
$query_summary = $konek->prepare("SELECT 
    COUNT(p.id) as total_transaksi,
    SUM(p.total_harga) as total_pendapatan,
    COUNT(CASE WHEN p.status = 'selesai' THEN 1 END) as transaksi_selesai
FROM pemesanan p 
WHERE DATE(p.created_at) BETWEEN ? AND ?");
$query_summary->bind_param("ss", $start_date, $end_date);
$query_summary->execute();
$summary = $query_summary->get_result()->fetch_assoc();

// --- QUERY TIKET TERLARIS ---
$query_top_ticket = $konek->prepare("SELECT t.nama_paket, SUM(p.jumlah_tiket) as total_terjual
    FROM pemesanan p
    JOIN tiket t ON p.tiket_id = t.id
    WHERE DATE(p.created_at) BETWEEN ? AND ? AND p.status = 'selesai'
    GROUP BY t.id
    ORDER BY total_terjual DESC
    LIMIT 1");
$query_top_ticket->bind_param("ss", $start_date, $end_date);
$query_top_ticket->execute();
$top_ticket = $query_top_ticket->get_result()->fetch_assoc();

// --- QUERY DETAIL LAPORAN BERDASARKAN FILTER ---
switch ($filter_type) {
    case 'harian':
        $label_format = 'd M Y';
        $sql_laporan = "
            SELECT DATE(p.created_at) AS periode,
                   COUNT(p.id) AS total_transaksi,
                   SUM(CASE WHEN p.status = 'selesai' THEN 1 ELSE 0 END) AS transaksi_selesai,
                   SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) AS transaksi_pending,
                   SUM(CASE WHEN p.status = 'batal' THEN 1 ELSE 0 END) AS transaksi_batal,
                   SUM(p.total_harga) AS total_pendapatan
            FROM pemesanan p
            WHERE DATE(p.created_at) BETWEEN ? AND ?
            GROUP BY DATE(p.created_at)
            ORDER BY DATE(p.created_at)";
        break;

    case 'tahunan':
        $label_format = 'Y';
        $sql_laporan = "
            SELECT YEAR(p.created_at) AS periode,
                   COUNT(p.id) AS total_transaksi,
                   SUM(CASE WHEN p.status = 'selesai' THEN 1 ELSE 0 END) AS transaksi_selesai,
                   SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) AS transaksi_pending,
                   SUM(CASE WHEN p.status = 'batal' THEN 1 ELSE 0 END) AS transaksi_batal,
                   SUM(p.total_harga) AS total_pendapatan
            FROM pemesanan p
            WHERE DATE(p.created_at) BETWEEN ? AND ?
            GROUP BY YEAR(p.created_at)
            ORDER BY YEAR(p.created_at)";
        break;

    default: // bulanan
        $label_format = 'M Y';
        $sql_laporan = "
            SELECT DATE_FORMAT(p.created_at, '%Y-%m') AS periode,
                   COUNT(p.id) AS total_transaksi,
                   SUM(CASE WHEN p.status = 'selesai' THEN 1 ELSE 0 END) AS transaksi_selesai,
                   SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) AS transaksi_pending,
                   SUM(CASE WHEN p.status = 'batal' THEN 1 ELSE 0 END) AS transaksi_batal,
                   SUM(p.total_harga) AS total_pendapatan
            FROM pemesanan p
            WHERE DATE(p.created_at) BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(p.created_at, '%Y-%m')
            ORDER BY DATE_FORMAT(p.created_at, '%Y-%m')";
        break;
}

$stmt_laporan = $konek->prepare($sql_laporan);
$stmt_laporan->bind_param("ss", $start_date, $end_date);
$stmt_laporan->execute();
$result_laporan = $stmt_laporan->get_result();

// --- QUERY 10 TIKET TERLARIS ---
$query_tiket = $konek->prepare("
    SELECT t.nama_paket,
           SUM(p.jumlah_tiket) AS total_tiket_terjual,
           SUM(p.total_harga) AS total_pendapatan
    FROM pemesanan p
    JOIN tiket t ON p.tiket_id = t.id
    WHERE DATE(p.created_at) BETWEEN ? AND ? AND p.status = 'selesai'
    GROUP BY t.id
    ORDER BY total_tiket_terjual DESC
    LIMIT 10");
$query_tiket->bind_param("ss", $start_date, $end_date);
$query_tiket->execute();
$result_tiket = $query_tiket->get_result();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Laporan Penjualan</h1>
</div>

<!-- Form Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="filter_type" class="form-label">Tipe Laporan</label>
                <select name="filter_type" id="filter_type" class="form-select">
                    <option value="harian" <?php echo $filter_type == 'harian' ? 'selected' : ''; ?>>Harian</option>
                    <option value="bulanan" <?php echo $filter_type == 'bulanan' ? 'selected' : ''; ?>>Bulanan</option>
                    <option value="tahunan" <?php echo $filter_type == 'tahunan' ? 'selected' : ''; ?>>Tahunan</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="start_date" class="form-label">Dari Tanggal</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $start_date ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $end_date ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <button type="button" class="btn btn-secondary" onclick="window.print()">Cetak</button>
            </div>
        </form>
    </div>
</div>

<!-- Kartu Ringkasan -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pendapatan Periode</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">Rp <?php echo number_format($summary['total_pendapatan'] ?? 0, 0, ',', '.'); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Transaksi Selesai</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $summary['transaksi_selesai'] ?? 0; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tiket Terlaris</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo htmlspecialchars($top_ticket['nama_paket'] ?? 'N/A'); ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Grafik Penjualan -->
<div class="card mb-4">
    <div class="card-header">
        <h5>Grafik Penjualan</h5>
    </div>
    <div class="card-body">
        <canvas id="salesChart" height="100"></canvas>
    </div>
</div>

<!-- Tabel Detail Laporan -->
<div class="card mb-4">
    <div class="card-header">
        <h5>Detail Laporan Penjualan</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Periode</th>
                        <th>Total Transaksi</th>
                        <th>Transaksi Selesai</th>
                        <th>Transaksi Pending</th>
                        <th>Transaksi Batal</th>
                        <th>Total Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_laporan->num_rows > 0): ?>
                        <?php $result_laporan->data_seek(0); while($row = $result_laporan->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php 
                                if ($filter_type == 'harian') {
                                    echo date($label_format, strtotime($row['periode']));
                                } elseif ($filter_type == 'bulanan') {
                                    $date = DateTime::createFromFormat('Y-m', $row['periode']);
                                    echo $date->format($label_format);
                                } else {
                                    echo $row['periode'];
                                }
                                ?>
                            </td>
                            <td><?php echo $row['total_transaksi']; ?></td>
                            <td><?php echo $row['transaksi_selesai']; ?></td>
                            <td><?php echo $row['transaksi_pending']; ?></td>
                            <td><?php echo $row['transaksi_batal']; ?></td>
                            <td>Rp <?php echo number_format($row['total_pendapatan'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">Tidak ada data laporan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tabel 10 Tiket Terlaris -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header"><h5>10 Tiket Terlaris</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr><th>Nama Paket</th><th>Jumlah</th><th>Pendapatan</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($result_tiket->num_rows > 0): ?>
                                <?php while($row = $result_tiket->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['nama_paket']; ?></td>
                                    <td><?php echo $row['total_tiket_terjual']; ?></td>
                                    <td>Rp <?php echo number_format($row['total_pendapatan'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center">Tidak ada data tiket terlaris.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = [];
    const dataPendapatan = [];
    const dataTransaksi = [];
    <?php 
    $result_laporan->data_seek(0);
    while($row = $result_laporan->fetch_assoc()): ?>
        labels.push('<?php 
            if ($filter_type == 'harian') {
                echo date($label_format, strtotime($row['periode']));
            } elseif ($filter_type == 'bulanan') {
                $date = DateTime::createFromFormat('Y-m', $row['periode']);
                echo $date->format($label_format);
            } else {
                echo $row['periode'];
            } ?>');
        dataPendapatan.push(<?php echo $row['total_pendapatan']; ?>);
        dataTransaksi.push(<?php echo $row['total_transaksi']; ?>);
    <?php endwhile; ?>

    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Pendapatan (Rp)',
                    data: dataPendapatan,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    yAxisID: 'y'
                },
                {
                    label: 'Jumlah Transaksi',
                    data: dataTransaksi,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    position: 'left',
                    title: { display: true, text: 'Pendapatan (Rp)' }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    title: { display: true, text: 'Jumlah Transaksi' },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });
</script>
