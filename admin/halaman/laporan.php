<?php
include '../../database/konek.php';
include '../boot.php';

// Ambil filter tanggal dan tipe laporan
 $filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'harian';
 $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
 $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Format query berdasarkan filter
 $date_format = '';
 $group_by = '';
 $label_format = '';

switch ($filter_type) {
    case 'harian':
        $date_format = '%Y-%m-%d';
        $group_by = 'DATE_FORMAT(created_at, "%Y-%m-%d")';
        $label_format = 'd/m/Y';
        break;
    case 'bulanan':
        $date_format = '%Y-%m';
        $group_by = 'DATE_FORMAT(created_at, "%Y-%m")';
        $label_format = 'F Y';
        break;
    case 'tahunan':
        $date_format = '%Y';
        $group_by = 'DATE_FORMAT(created_at, "%Y")';
        $label_format = 'Y';
        break;
}

// Query untuk laporan penjualan - PERBAIKAN DI SINI
 $query_laporan = "SELECT DATE_FORMAT(created_at, '$date_format') as periode, 
                         COUNT(*) as total_transaksi, 
                         SUM(total_harga) as total_pendapatan,
                         COUNT(CASE WHEN status = 'selesai' THEN 1 END) as transaksi_selesai,
                         COUNT(CASE WHEN status = 'pending' THEN 1 END) as transaksi_pending,
                         COUNT(CASE WHEN status = 'batal' THEN 1 END) as transaksi_batal
                  FROM pemesanan 
                  WHERE DATE(created_at) BETWEEN ? AND ? 
                  GROUP BY $group_by
                  ORDER BY periode DESC";

 $prepare_laporan = $konek->prepare($query_laporan);
 $prepare_laporan->bind_param("ss", $start_date, $end_date);
 $prepare_laporan->execute();
 $result_laporan = $prepare_laporan->get_result();

// Query untuk laporan tiket terlaris
 $query_tiket = "SELECT t.nama_paket, 
                       COUNT(p.id) as jumlah_pemesanan,
                       SUM(p.jumlah_tiket) as total_tiket_terjual,
                       SUM(p.total_harga) as total_pendapatan
                FROM tiket t
                JOIN pemesanan p ON t.id = p.tiket_id
                WHERE DATE(p.created_at) BETWEEN ? AND ? AND p.status = 'selesai'
                GROUP BY t.id
                ORDER BY total_pendapatan DESC
                LIMIT 10";

 $prepare_tiket = $konek->prepare($query_tiket);
 $prepare_tiket->bind_param("ss", $start_date, $end_date);
 $prepare_tiket->execute();
 $result_tiket = $prepare_tiket->get_result();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Laporan Penjualan</h1>
</div>

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

<!-- Grafik Penjualan -->
<div class="card mb-4">
    <div class="card-header">
        <h5>Grafik Penjualan</h5>
    </div>
    <div class="card-body">
        <canvas id="salesChart" height="100"></canvas>
    </div>
</div>

<!-- Tabel Laporan Penjualan -->
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
                        <?php while($row = $result_laporan->fetch_assoc()): ?>
                        <tr>
                            <td><?php 
                                if ($filter_type == 'harian') {
                                    echo date($label_format, strtotime($row['periode']));
                                } elseif ($filter_type == 'bulanan') {
                                    $date = DateTime::createFromFormat('Y-m', $row['periode']);
                                    echo $date->format($label_format);
                                } else {
                                    echo $row['periode'];
                                }
                            ?></td>
                            <td><?php echo $row['total_transaksi']; ?></td>
                            <td><?php echo $row['transaksi_selesai']; ?></td>
                            <td><?php echo $row['transaksi_pending']; ?></td>
                            <td><?php echo $row['transaksi_batal']; ?></td>
                            <td>Rp <?php echo number_format($row['total_pendapatan'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data laporan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row">
    <!-- Tabel Tiket Terlaris -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5>10 Tiket Terlaris</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Paket</th>
                                <th>Jumlah</th>
                                <th>Pendapatan</th>
                            </tr>
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
                                <tr>
                                    <td colspan="3" class="text-center">Tidak ada data tiket terlaris.</td>
                                </tr>
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
    // Data untuk grafik
    const labels = [];
    const dataPendapatan = [];
    const dataTransaksi = [];
    
    <?php 
    // Reset result pointer
    $result_laporan->data_seek(0);
    while($row = $result_laporan->fetch_assoc()): 
    ?>
        labels.push('<?php 
            if ($filter_type == 'harian') {
                echo date($label_format, strtotime($row['periode']));
            } elseif ($filter_type == 'bulanan') {
                $date = DateTime::createFromFormat('Y-m', $row['periode']);
                echo $date->format($label_format);
            } else {
                echo $row['periode'];
            }
        ?>');
        dataPendapatan.push(<?php echo $row['total_pendapatan']; ?>);
        dataTransaksi.push(<?php echo $row['total_transaksi']; ?>);
    <?php endwhile; ?>
    
    // Buat grafik
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Pendapatan (Rp)',
                    data: dataPendapatan,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Jumlah Transaksi',
                    data: dataTransaksi,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
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
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Pendapatan (Rp)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Jumlah Transaksi'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
</script>