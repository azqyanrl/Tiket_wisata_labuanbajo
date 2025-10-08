<?php
 $query = "SELECT DATE(created_at) as tanggal, COUNT(*) as total_transaksi, SUM(total_harga) as total_pendapatan FROM pemesanan WHERE status = 'selesai' ";
 $params = []; $types = "";
if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $query .= "AND DATE(created_at) BETWEEN ? AND ? "; $params[] = $_GET['start_date']; $params[] = $_GET['end_date']; $types .= "ss";
}
 $query .= "GROUP BY DATE(created_at) ORDER BY tanggal DESC";
 $stmt = $konek->prepare($query);
if (!empty($params)) { $stmt->bind_param($types, ...$params); }
 $stmt->execute(); $result = $stmt->get_result();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"><h1 class="h2">Laporan Penjualan</h1></div>
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4"><label for="start_date" class="form-label">Dari Tanggal</label><input type="date" name="start_date" class="form-control" value="<?= $_GET['start_date'] ?? '' ?>"></div>
            <div class="col-md-4"><label for="end_date" class="form-label">Sampai Tanggal</label><input type="date" name="end_date" class="form-control" value="<?= $_GET['end_date'] ?? '' ?>"></div>
            <div class="col-md-4 d-flex align-items-end"><button type="submit" class="btn btn-primary me-2">Filter</button><button type="button" class="btn btn-secondary" onclick="window.print()">Cetak</button></div>
        </form>
    </div>
</div>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead class="table-light"><tr><th>Tanggal</th><th>Jumlah Transaksi</th><th>Total Pendapatan</th></tr></thead>
        <tbody>
            <?php if ($result->num_rows > 0) { 
                while($row = $result->fetch_assoc()) { 
                    echo "<tr><td>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td><td>{$row['total_transaksi']}</td><td>Rp " . number_format($row['total_pendapatan'], 0, ',', '.') . "</td></tr>"; 
                } 
            } else { 
                echo "<tr><td colspan='3' class='text-center'>Tidak ada data laporan.</td></tr>"; 
            } ?>
        </tbody>
    </table>
</div>