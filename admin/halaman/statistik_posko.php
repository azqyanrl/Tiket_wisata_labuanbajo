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
include '../../includes/alerts.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Statistik Per Posko</h1>
</div>

<!-- Filter Berdasarkan Tanggal -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="">
                    <input type="hidden" name="page" value="statistik_posko">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                            <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal"
                                value="<?php echo isset($_GET['tanggal_awal']) ? htmlspecialchars($_GET['tanggal_awal']) : date('Y-m-01'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir"
                                value="<?php echo isset($_GET['tanggal_akhir']) ? htmlspecialchars($_GET['tanggal_akhir']) : date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="?page=statistik_posko" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Statistik Cards Per Posko -->
<div class="row mb-4">
    <?php
    // Query untuk mendapatkan statistik per posko
    $tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : date('Y-m-01');
    $tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-d');
    
    $stats_posko = $konek->prepare("
        SELECT 
            t.lokasi,
            COUNT(p.id) as total_pemesanan,
            SUM(CASE WHEN p.status = 'selesai' THEN 1 ELSE 0 END) as total_selesai,
            SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) as total_pending,
            SUM(CASE WHEN p.status = 'selesai' THEN p.total_harga ELSE 0 END) as total_pendapatan
        FROM pemesanan p
        JOIN tiket t ON p.tiket_id = t.id
        WHERE DATE(p.created_at) BETWEEN ? AND ?
        GROUP BY t.lokasi
        ORDER BY total_pendapatan DESC
    ");
    $stats_posko->bind_param("ss", $tanggal_awal, $tanggal_akhir);
    $stats_posko->execute();
    $result_stats = $stats_posko->get_result();
    
    while($stat = $result_stats->fetch_assoc()):
    ?>
    <div class="col-md-3">
        <div class="card border-start border-4 border-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="text-uppercase fw-bold text-primary fs-6"><?= htmlspecialchars($stat['lokasi']) ?></div>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Total</small>
                                <div class="h6 mb-0"><?= $stat['total_pemesanan'] ?></div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Selesai</small>
                                <div class="h6 mb-0 text-success"><?= $stat['total_selesai'] ?></div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">Pendapatan</small>
                            <div class="fw-bold">Rp <?= number_format($stat['total_pendapatan'], 0, ',', '.') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<!-- Grafik Per Posko -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Grafik Pendapatan Per Posko</h5>
            </div>
            <div class="card-body">
                <canvas id="poskoChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Detail Per Posko -->
<div class="card">
    <div class="card-header">
        <h5>Detail Pemesanan Per Posko</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Posko</th>
                        <th>Total Pemesanan</th>
                        <th>Selesai</th>
                        <th>Menunggu</th>
                        <th>Pendapatan</th>
                        <th>Admin Teraktif</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $detail_query = $konek->prepare("
                        SELECT 
                            t.lokasi,
                            COUNT(p.id) as total_pemesanan,
                            SUM(CASE WHEN p.status = 'selesai' THEN 1 ELSE 0 END) as total_selesai,
                            SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) as total_pending,
                            SUM(CASE WHEN p.status = 'selesai' THEN p.total_harga ELSE 0 END) as total_pendapatan
                        FROM pemesanan p
                        JOIN tiket t ON p.tiket_id = t.id
                        WHERE DATE(p.created_at) BETWEEN ? AND ?
                        GROUP BY t.lokasi
                        ORDER BY total_pendapatan DESC
                    ");
                    $detail_query->bind_param("ss", $tanggal_awal, $tanggal_akhir);
                    $detail_query->execute();
                    $detail_result = $detail_query->get_result();
                    
                    while($detail = $detail_result->fetch_assoc()):
                        // Cari admin teraktif untuk posko ini
                        $admin_query = $konek->prepare("
                            SELECT u.nama_lengkap, COUNT(vh.id) as total_verifikasi
                            FROM verifikasi_history vh
                            JOIN users u ON vh.admin_id = u.id
                            JOIN pemesanan p ON vh.pemesanan_id = p.id
                            JOIN tiket t ON p.tiket_id = t.id
                            WHERE t.lokasi = ? AND DATE(vh.created_at) BETWEEN ? AND ?
                            GROUP BY u.id
                            ORDER BY total_verifikasi DESC
                            LIMIT 1
                        ");
                        $admin_query->bind_param("sss", $detail['lokasi'], $tanggal_awal, $tanggal_akhir);
                        $admin_query->execute();
                        $admin_result = $admin_query->get_result();
                        $admin_data = $admin_result->fetch_assoc();
                    ?>
                    <tr>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($detail['lokasi']) ?></span></td>
                        <td><?= $detail['total_pemesanan'] ?></td>
                        <td><?= $detail['total_selesai'] ?></td>
                        <td><?= $detail['total_pending'] ?></td>
                        <td>Rp <?= number_format($detail['total_pendapatan'], 0, ',', '.') ?></td>
                        <td><?= $admin_data ? htmlspecialchars($admin_data['nama_lengkap']) : '-' ?></td>
                        <td>
                            <a href="?page=detail_posko&posko=<?= urlencode($detail['lokasi']) ?>&awal=<?= $tanggal_awal ?>&akhir=<?= $tanggal_akhir ?>" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data untuk grafik
    <?php
    $chart_query = $konek->prepare("
        SELECT 
            t.lokasi,
            SUM(CASE WHEN p.status = 'selesai' THEN p.total_harga ELSE 0 END) as total_pendapatan
        FROM pemesanan p
        JOIN tiket t ON p.tiket_id = t.id
        WHERE DATE(p.created_at) BETWEEN ? AND ?
        GROUP BY t.lokasi
        ORDER BY total_pendapatan DESC
    ");
    $chart_query->bind_param("ss", $tanggal_awal, $tanggal_akhir);
    $chart_query->execute();
    $chart_result = $chart_query->get_result();
    
    $labels = [];
    $data = [];
    
    while($row = $chart_result->fetch_assoc()) {
        $labels[] = "'".$row['lokasi']."'";
        $data[] = $row['total_pendapatan'];
    }
    ?>
    
    const ctx = document.getElementById('poskoChart').getContext('2d');
    const poskoChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [<?= implode(',', $labels) ?>],
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: [<?= implode(',', $data) ?>],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(153, 102, 255, 0.5)',
                    'rgba(255, 159, 64, 0.5)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
});
</script>