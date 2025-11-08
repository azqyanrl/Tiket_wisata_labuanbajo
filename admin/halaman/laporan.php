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

function esc($conn, $v) {
    return mysqli_real_escape_string($conn, trim($v));
}
function is_valid_date($d) {
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt && $dt->format('Y-m-d') === $d;
}
function rupiah($n) {
    return 'Rp ' . number_format($n, 0, ',', '.');
}

// --- Ambil parameter filter dari GET ---
$filter_type = $_GET['filter_type'] ?? '';
$date = $_GET['date'] ?? '';
$month = isset($_GET['month']) ? (int)$_GET['month'] : 0;
$year = isset($_GET['year']) ? (int)$_GET['year'] : 0;
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$where = "p.status = 'selesai'";

if ($filter_type === 'daily' && is_valid_date($date)) {
    $d = esc($konek, $date);
    $where .= " AND p.tanggal_kunjungan = '$d'";
} elseif ($filter_type === 'monthly' && $month >= 1 && $month <= 12 && $year > 0) {
    $m = str_pad($month, 2, '0', STR_PAD_LEFT);
    $y = (int)$year;
    $where .= " AND MONTH(p.tanggal_kunjungan) = $m AND YEAR(p.tanggal_kunjungan) = $y";
} elseif ($filter_type === 'yearly' && $year > 0) {
    $y = (int)$year;
    $where .= " AND YEAR(p.tanggal_kunjungan) = $y";
} elseif ($filter_type === 'range' && is_valid_date($start_date) && is_valid_date($end_date)) {
    $s = esc($konek, $start_date);
    $e = esc($konek, $end_date);
    if ($s > $e) [$s, $e] = [$e, $s];
    $where .= " AND p.tanggal_kunjungan BETWEEN '$s' AND '$e'";
}

// --- Query utama ---
$sql = "SELECT p.*, u.nama_lengkap, t.nama_paket
        FROM pemesanan p
        LEFT JOIN users u ON p.user_id = u.id
        LEFT JOIN tiket t ON p.tiket_id = t.id
        WHERE $where
        ORDER BY p.tanggal_kunjungan DESC, p.created_at DESC";

$res = mysqli_query($konek, $sql);

$total_pendapatan = 0;
$total_transaksi = 0;
$rows = [];
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $rows[] = $r;
        $total_pendapatan += (float)$r['total_harga'];
        $total_transaksi++;
    }
}

// --- Query tiket terlaris (Top 5) ---
$sql_top = "SELECT p.tiket_id, t.nama_paket, SUM(p.jumlah_tiket) AS total_terjual, COUNT(*) AS transaksi
            FROM pemesanan p
            LEFT JOIN tiket t ON p.tiket_id = t.id
            WHERE p.status = 'selesai'
            GROUP BY p.tiket_id
            ORDER BY total_terjual DESC
            LIMIT 5";
$res_top = mysqli_query($konek, $sql_top);
$top_list = [];
if ($res_top) {
    while ($r = mysqli_fetch_assoc($res_top)) {
        $top_list[] = $r;
    }
}

include '../../includes/boot.php';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Transaksi - Admin</title>
<style>
@media print {
    /* Sembunyikan semua elemen navigasi, sidebar, header, footer */
    header, nav, .navbar, .sidebar, .offcanvas, .footer, .no-print {
        display: none !important;
        visibility: hidden !important;
    }

    /* Pastikan area konten utama memenuhi halaman */
    main, .content, .container, body {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        background: white !important;
    }

    /* Hapus shadow & border pada kartu */
    .card, .card-body {
        box-shadow: none !important;
        border: none !important;
    }

    /* Format tabel */
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid #000 !important;
        padding: 8px !important;
    }

    /* Warnai header tabel saat print */
    thead th {
        background-color: #212529 !important;
        color: white !important;
        -webkit-print-color-adjust: exact !important;
    }

    /* Hindari halaman kosong di print */
    html, body {
        height: auto !important;
        overflow: visible !important;
    }
}
</style>


</head>
<body class="bg-light">
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Laporan Transaksi</h3>
        <div class="no-print">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <div class="card mb-3 no-print">
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end">
                <input type="hidden" name="page" value="laporan">

                <div class="col-12 col-md-3">
                    <label class="form-label">Tipe Filter</label>
                    <select name="filter_type" id="filter_type" class="form-select">
                        <option value="">-- Tanpa Filter --</option>
                        <option value="daily" <?= $filter_type==='daily' ? 'selected' : '' ?>>Harian</option>
                        <option value="monthly" <?= $filter_type==='monthly' ? 'selected' : '' ?>>Bulanan</option>
                        <option value="yearly" <?= $filter_type==='yearly' ? 'selected' : '' ?>>Tahunan</option>
                        <option value="range" <?= $filter_type==='range' ? 'selected' : '' ?>>Rentang Tanggal</option>
                    </select>
                </div>

                <!-- Filter Harian -->
                <div class="col-12 col-md-2 filter-item" id="filter_daily" style="display: none;">
                    <label class="form-label">Pilih Tanggal</label>
                    <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date) ?>">
                </div>

                <!-- Filter Bulanan -->
                <div class="col-12 col-md-2 filter-item" id="filter_monthly" style="display: none;">
                    <label class="form-label">Bulan</label>
                    <select name="month" class="form-select">
                        <option value="">-- Pilih Bulan --</option>
                        <?php for ($m=1;$m<=12;$m++): ?>
                            <option value="<?= $m ?>" <?= ($filter_type==='monthly' && (int)$month===$m) ? 'selected' : '' ?>>
                                <?= str_pad($m,2,'0',STR_PAD_LEFT) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-12 col-md-2 filter-item" id="filter_monthly_year" style="display: none;">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-select">
                        <option value="">-- Pilih Tahun --</option>
                        <?php for ($yy = date('Y'); $yy <= date('Y') + 10; $yy++): ?>
                            <option value="<?= $yy ?>" <?= ((int)$year===$yy) ? 'selected' : '' ?>>
                                <?= $yy ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Filter Tahunan -->
                <div class="col-12 col-md-2 filter-item" id="filter_yearly" style="display: none;">
                    <label class="form-label">Tahun</label>
                    <select name="year_y" id="year_y" class="form-select">
                        <option value="">-- Pilih Tahun --</option>
                        <?php for ($yy = date('Y'); $yy <= date('Y') + 10; $yy++): ?>
                            <option value="<?= $yy ?>" <?= ((int)$year===$yy) ? 'selected' : '' ?>>
                                <?= $yy ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Filter Rentang -->
                <div class="col-12 col-md-2 filter-item" id="filter_range" style="display: none;">
                    <label class="form-label">Dari</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
                </div>
                <div class="col-12 col-md-2 filter-item" id="filter_range_to" style="display: none;">
                    <label class="form-label">Sampai</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-success">Tampilkan</button>
                </div>
                <div class="col-auto">
                    <a href="<?= strtok($_SERVER["REQUEST_URI"], '?') ?>?page=laporan" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6>Total Pendapatan</h6>
                    <h4 class="mb-0"><?= rupiah($total_pendapatan) ?></h4>
                    <p class="small-muted mb-0">Jumlah transaksi: <?= $total_transaksi ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6>Tiket Terlaris (Top <?= count($top_list) ?>)</h6>
                    <ul class="list-group list-group-flush">
                        <?php if (count($top_list) === 0): ?>
                            <li class="list-group-item">Belum ada data</li>
                        <?php else: ?>
                            <?php foreach ($top_list as $t): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($t['nama_paket'] ?: '—') ?></strong><br>
                                        <small class="text-muted">Transaksi: <?= $t['transaksi'] ?> — Tiket terjual: <?= $t['total_terjual'] ?></small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode Booking</th>
                            <th>Nama Pelanggan</th>
                            <th>Nama Paket</th>
                            <th>Tanggal Kunjungan</th>
                            <th>Jumlah Tiket</th>
                            <th>Total Harga</th>
                            <th>Metode Pembayaran</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($rows) === 0): ?>
                            <tr><td colspan="9" class="text-center py-4">Tidak ada data.</td></tr>
                        <?php else: ?>
                            <?php $no=1; foreach ($rows as $r): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($r['kode_booking']) ?></td>
                                    <td><?= htmlspecialchars($r['nama_lengkap'] ?: '-') ?></td>
                                    <td><?= htmlspecialchars($r['nama_paket'] ?: '-') ?></td>
                                    <td><?= htmlspecialchars($r['tanggal_kunjungan']) ?></td>
                                    <td><?= (int)$r['jumlah_tiket'] ?></td>
                                    <td><?= rupiah($r['total_harga']) ?></td>
                                    <td><?= htmlspecialchars($r['metode_pembayaran']) ?></td>
                                    <td><?= htmlspecialchars($r['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <?php if (count($rows) > 0): ?>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="6" class="text-end">Total</th>
                                <th><?= rupiah($total_pendapatan) ?></th>
                                <th colspan="2">Transaksi: <?= $total_transaksi ?></th>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleFilterUI() {
        const type = document.getElementById('filter_type').value;
        document.querySelectorAll('.filter-item').forEach(el => el.style.display = 'none');
        if (type === 'daily') document.getElementById('filter_daily').style.display = 'block';
        else if (type === 'monthly') {
            document.getElementById('filter_monthly').style.display = 'block';
            document.getElementById('filter_monthly_year').style.display = 'block';
        } else if (type === 'yearly') document.getElementById('filter_yearly').style.display = 'block';
        else if (type === 'range') {
            document.getElementById('filter_range').style.display = 'block';
            document.getElementById('filter_range_to').style.display = 'block';
        }
    }
    document.getElementById('filter_type').addEventListener('change', toggleFilterUI);
    toggleFilterUI();

    document.querySelector('form').addEventListener('submit', function(e){
        const type = document.getElementById('filter_type').value;
        if (type === 'yearly') {
            const y = document.getElementById('year_y').value;
            if (y) {
                let existing = document.querySelector('input[name="year"]');
                if (!existing) {
                    let inp = document.createElement('input');
                    inp.type = 'hidden'; inp.name = 'year'; inp.value = y;
                    this.appendChild(inp);
                } else existing.value = y;
            }
        }
    });
</script>
</body>
</html>
