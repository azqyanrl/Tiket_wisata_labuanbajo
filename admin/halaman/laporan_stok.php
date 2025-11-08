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
$filter_type  = isset($_GET['filter_type']) ? esc($konek, $_GET['filter_type']) : 'hari';
$filter_date  = isset($_GET['filter_date']) ? esc($konek, $_GET['filter_date']) : '';
$from_date    = isset($_GET['from_date']) ? esc($konek, $_GET['from_date']) : '';
$to_date      = isset($_GET['to_date']) ? esc($konek, $_GET['to_date']) : '';
$filter_month = isset($_GET['filter_month']) ? esc($konek, $_GET['filter_month']) : '';
$filter_year  = isset($_GET['filter_year']) ? esc($konek, $_GET['filter_year']) : '';

// Jika user memilih filter "bulan" tapi tidak memilih tahun, pakai tahun sekarang
if ($filter_type === 'bulan' && !empty($filter_month) && empty($filter_year)) {
    $filter_year = date('Y');
}

// 🔹 Query laporan stok otomatis tanpa tabel tiket_terjual_harian
$query = "
    SELECT 
        t.id AS tiket_id,
        t.nama_paket,
        t.stok AS stok_default,
        p.tanggal_kunjungan,
        SUM(CASE WHEN p.status != 'batal' THEN p.jumlah_tiket ELSE 0 END) AS total_terjual
    FROM tiket t
    LEFT JOIN pemesanan p ON t.id = p.tiket_id
";

$where = [];
if ($filter_type === 'hari' && !empty($filter_date)) {
    $where[] = "DATE(p.tanggal_kunjungan) = '$filter_date'";
} elseif ($filter_type === 'periode' && !empty($from_date) && !empty($to_date)) {
    $where[] = "p.tanggal_kunjungan BETWEEN '$from_date' AND '$to_date'";
} elseif ($filter_type === 'bulan' && !empty($filter_month)) {
    if (!empty($filter_year)) {
        $where[] = "MONTH(p.tanggal_kunjungan) = '$filter_month' AND YEAR(p.tanggal_kunjungan) = '$filter_year'";
    } else {
        $where[] = "MONTH(p.tanggal_kunjungan) = '$filter_month'";
    }
} elseif ($filter_type === 'tahun' && !empty($filter_year)) {
    $where[] = "YEAR(p.tanggal_kunjungan) = '$filter_year'";
}

if (count($where) > 0) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " GROUP BY t.id, p.tanggal_kunjungan ORDER BY p.tanggal_kunjungan DESC";

$result = mysqli_query($konek, $query);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <h3 class="fw-bold">📊 Laporan Stok Tiket</h3>
        <button class="btn btn-primary" onclick="printReport()">
            <i class="bi bi-printer"></i> Cetak Laporan
        </button>
    </div>

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
        <select name="filter_month" id="filter_month" class="form-select">
            <option value="">-- Pilih Bulan --</option>
            <?php for($i=1;$i<=12;$i++): ?>
                <option value="<?= $i ?>" <?= ($filter_month==$i)?'selected':''; ?>>
                    <?= date('F', mktime(0,0,0,$i,1)); ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>

    <div class="col-md-3 filter-year" style="display:none;">
        <label class="form-label">Tahun</label>
        <select name="filter_year" id="filter_year" class="form-select">
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
                    $tersisa = max($stok_awal - $terjual, 0);
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

</div>
</div>

<script>
function toggleFilterFields() {
    const type = document.getElementById('filter_type').value;
    document.querySelectorAll('.filter-hari').forEach(el => el.style.display = (type === 'hari') ? '' : 'none');
    document.querySelectorAll('.filter-periode').forEach(el => el.style.display = (type === 'periode') ? '' : 'none');
    document.querySelectorAll('.filter-bulan').forEach(el => el.style.display = (type === 'bulan') ? '' : 'none');
    document.querySelectorAll('.filter-year').forEach(el => el.style.display = (type === 'bulan' || type === 'tahun') ? '' : 'none');

    // Isi otomatis tahun sekarang kalau pilih "Bulan" tapi belum isi tahun
    if (type === 'bulan') {
        const yr = document.getElementById('filter_year');
        if (yr && !yr.value) {
            yr.value = new Date().getFullYear();
        }
    }
}
toggleFilterFields();

function printReport() {
    window.print();
}
</script>

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
