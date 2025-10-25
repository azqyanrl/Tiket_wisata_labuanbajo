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

function esc($konek, $v) {
    return mysqli_real_escape_string($konek, trim($v));
}

// Ambil input filter
$filter_type = isset($_GET['filter_type']) ? esc($konek, $_GET['filter_type']) : 'hari';
$filter_date = isset($_GET['filter_date']) ? esc($konek, $_GET['filter_date']) : '';
$from_date = isset($_GET['from_date']) ? esc($konek, $_GET['from_date']) : '';
$to_date = isset($_GET['to_date']) ? esc($konek, $_GET['to_date']) : '';
$filter_month = isset($_GET['filter_month']) ? esc($konek, $_GET['filter_month']) : '';
$filter_year = isset($_GET['filter_year']) ? esc($konek, $_GET['filter_year']) : '';

$query = "
    SELECT 
        t.id AS tiket_id,
        t.nama_paket,
        t.stok_default,
        th.tanggal_kunjungan,
        SUM(th.jumlah_terjual) AS total_terjual
    FROM tiket t
    LEFT JOIN tiket_terjual_harian th ON t.id = th.tiket_id
";

$where = [];

// 🔹 Filter berdasarkan jenis
if ($filter_type === 'hari' && !empty($filter_date)) {
    $where[] = "DATE(th.tanggal_kunjungan) = '$filter_date'";
} elseif ($filter_type === 'periode' && !empty($from_date) && !empty($to_date)) {
    $where[] = "th.tanggal_kunjungan BETWEEN '$from_date' AND '$to_date'";
} elseif ($filter_type === 'bulan' && !empty($filter_month) && !empty($filter_year)) {
    $where[] = "MONTH(th.tanggal_kunjungan) = '$filter_month' AND YEAR(th.tanggal_kunjungan) = '$filter_year'";
} elseif ($filter_type === 'tahun' && !empty($filter_year)) {
    $where[] = "YEAR(th.tanggal_kunjungan) = '$filter_year'";
}

if (count($where) > 0) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " GROUP BY t.id, th.tanggal_kunjungan ORDER BY th.tanggal_kunjungan DESC";

$result = mysqli_query($konek, $query);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <h3 class="fw-bold">📊 Laporan Stok Tiket</h3>
        <button class="btn btn-primary" onclick="printReport()">
            <i class="bi bi-printer"></i> Cetak Laporan
        </button>
    </div>

```
<!-- FILTER -->
<div class="card card-body border-0 shadow-sm mb-4 no-print">
    <form method="GET" action="index.php" class="row g-3 align-items-end">
        <input type="hidden" name="page" value="laporan_stok">

        <div class="col-md-3">
            <label class="form-label">Filter Berdasarkan</label>
            <select name="filter_type" id="filter_type" class="form-select" onchange="toggleFilterFields()">
                <option value="hari" <?= $filter_type=='hari'?'selected':''; ?>>Per Hari</option>
                <option value="periode" <?= $filter_type=='periode'?'selected':''; ?>>Per Periode</option>
                <option value="bulan" <?= $filter_type=='bulan'?'selected':''; ?>>Per Bulan</option>
                <option value="tahun" <?= $filter_type=='tahun'?'selected':''; ?>>Per Tahun</option>
            </select>
        </div>

        <div class="col-md-3 filter-hari">
            <label class="form-label">Tanggal</label>
            <input type="date" name="filter_date" class="form-control" value="<?= htmlspecialchars($filter_date) ?>">
        </div>

        <div class="col-md-3 filter-periode" style="display:none;">
            <label class="form-label">Dari</label>
            <input type="date" name="from_date" class="form-control" value="<?= htmlspecialchars($from_date) ?>">
        </div>
        <div class="col-md-3 filter-periode" style="display:none;">
            <label class="form-label">Sampai</label>
            <input type="date" name="to_date" class="form-control" value="<?= htmlspecialchars($to_date) ?>">
        </div>

        <div class="col-md-3 filter-bulan" style="display:none;">
            <label class="form-label">Bulan</label>
            <select name="filter_month" class="form-select">
                <option value="">-- Pilih Bulan --</option>
                <?php for($i=1;$i<=12;$i++): ?>
                    <option value="<?= $i ?>" <?= ($filter_month==$i)?'selected':''; ?>>
                        <?= date('F', mktime(0,0,0,$i,1)); ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="col-md-3 filter-bulan" style="display:none;">
            <label class="form-label">Tahun</label>
            <select name="filter_year" class="form-select">
                <option value="">-- Pilih Tahun --</option>
                <?php for($y=2025;$y<=2035;$y++): ?>
                    <option value="<?= $y ?>" <?= ($filter_year==$y)?'selected':''; ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="col-md-3 filter-tahun" style="display:none;">
            <label class="form-label">Tahun</label>
            <select name="filter_year" class="form-select">
                <option value="">-- Pilih Tahun --</option>
                <?php for($y=2025;$y<=2035;$y++): ?>
                    <option value="<?= $y ?>" <?= ($filter_year==$y)?'selected':''; ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-success w-100"><i class="bi bi-funnel"></i> Filter</button>
        </div>
        <div class="col-md-2">
            <a href="index.php?page=laporan_stok" class="btn btn-secondary w-100"><i class="bi bi-arrow-repeat"></i> Reset</a>
        </div>
    </form>
</div>

<!-- TABEL -->
<div id="laporanArea" class="card card-body border-0 shadow-sm">
    <div class="text-center mb-4">
        <h4 class="fw-bold">LAPORAN STOK TIKET LABUAN BAJO</h4>
        <p class="text-muted mb-0">
            Periode:
            <?php
                if ($filter_type === 'hari') echo "Tanggal $filter_date";
                elseif ($filter_type === 'periode') echo "$from_date s.d $to_date";
                elseif ($filter_type === 'bulan') echo "Bulan ".date('F', mktime(0,0,0,$filter_month,1))." $filter_year";
                elseif ($filter_type === 'tahun') echo "Tahun $filter_year";
            ?>
        </p>
        <hr>
    </div>

    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark text-center">
            <tr>
                <th>No</th>
                <th>Nama Paket</th>
                <th>Tanggal Kunjungan</th>
                <th>Tiket Terjual</th>
                <th>Tiket Tersisa</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php $no=1; while($row=mysqli_fetch_assoc($result)): ?>
                    <?php
                        $stok_awal = $row['stok_default'] ?? 0;
                        $terjual = $row['total_terjual'] ?? 0;
                        $tersisa = max(0, $stok_awal - $terjual);
                    ?>
                    <tr class="text-center">
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['nama_paket']); ?></td>
                        <td><?= htmlspecialchars($row['tanggal_kunjungan'] ?? '-'); ?></td>
                        <td><?= $terjual; ?></td>
                        <td><?= $tersisa; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center text-muted">Tidak ada data ditemukan</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p class="mt-3 text-end text-muted">Dicetak pada: <?= date('d M Y, H:i'); ?></p>
</div>
```

</div>

<script>
function toggleFilterFields() {
    const type = document.getElementById('filter_type').value;
    document.querySelectorAll('.filter-hari').forEach(el => el.style.display = (type === 'hari') ? '' : 'none');
    document.querySelectorAll('.filter-periode').forEach(el => el.style.display = (type === 'periode') ? '' : 'none');
    document.querySelectorAll('.filter-bulan').forEach(el => el.style.display = (type === 'bulan') ? '' : 'none');
    document.querySelectorAll('.filter-tahun').forEach(el => el.style.display = (type === 'tahun') ? '' : 'none');
}
toggleFilterFields();

function printReport() {
    window.print();
}
</script>

<style>
@media print {
    .no-print, .sidebar, header, nav, .navbar, .offcanvas, .footer { 
        display: none !important; 
    }
    body { background: white; font-size: 14px; margin: 20px; }
    .card { border: none !important; box-shadow: none !important; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #000 !important; padding: 8px; }
    thead th { 
        background-color: #343a40 !important; 
        color: white !important; 
        -webkit-print-color-adjust: exact !important; 
    }
    main, .content, .container {
        width: 100% !important;
        margin-left: 0 !important;
    }
}
</style>
