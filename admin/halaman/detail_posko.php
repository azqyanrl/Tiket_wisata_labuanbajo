<?php
ob_start();
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

if (!isset($_GET['posko'])) {
    header('Location: ?page=statistik_posko');
    exit(); 
}

 $posko = $_GET['posko'];
 $tanggal_awal = $_GET['awal'] ?? date('Y-m-01');
 $tanggal_akhir = $_GET['akhir'] ?? date('Y-m-d');
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Detail Posko: <?= htmlspecialchars($posko) ?></h1>
    <a href="?page=statistik_posko&awal=<?= $tanggal_awal ?>&akhir=<?= $tanggal_akhir ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<!-- Statistik Posko -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">
                            <?php
                            $query_total = $konek->prepare("
                                SELECT COUNT(*) as total 
                                FROM pemesanan p 
                                JOIN tiket t ON p.tiket_id = t.id 
                                WHERE t.lokasi = ? AND DATE(p.created_at) BETWEEN ? AND ?
                            ");
                            $query_total->bind_param("sss", $posko, $tanggal_awal, $tanggal_akhir);
                            $query_total->execute();
                            echo $query_total->get_result()->fetch_assoc()['total'];
                            ?>
                        </h4>
                        <p class="mb-0">Total Pemesanan</p>
                    </div>
                    <i class="bi bi-cart-check fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">
                            <?php
                            $query_selesai = $konek->prepare("
                                SELECT COUNT(*) as total 
                                FROM pemesanan p 
                                JOIN tiket t ON p.tiket_id = t.id 
                                WHERE t.lokasi = ? AND p.status = 'selesai' AND DATE(p.created_at) BETWEEN ? AND ?
                            ");
                            $query_selesai->bind_param("sss", $posko, $tanggal_awal, $tanggal_akhir);
                            $query_selesai->execute();
                            echo $query_selesai->get_result()->fetch_assoc()['total'];
                            ?>
                        </h4>
                        <p class="mb-0">Berhasil</p>
                    </div>
                    <i class="bi bi-check-circle fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">
                            <?php
                            $query_pending = $konek->prepare("
                                SELECT COUNT(*) as total 
                                FROM pemesanan p 
                                JOIN tiket t ON p.tiket_id = t.id 
                                WHERE t.lokasi = ? AND p.status = 'pending' AND DATE(p.created_at) BETWEEN ? AND ?
                            ");
                            $query_pending->bind_param("sss", $posko, $tanggal_awal, $tanggal_akhir);
                            $query_pending->execute();
                            echo $query_pending->get_result()->fetch_assoc()['total'];
                            ?>
                        </h4>
                        <p class="mb-0">Menunggu Bayar</p>
                    </div>
                    <i class="bi bi-clock-history fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">Rp 
                            <?php
                            $query_pendapatan = $konek->prepare("
                                SELECT SUM(p.total_harga) as total 
                                FROM pemesanan p 
                                JOIN tiket t ON p.tiket_id = t.id 
                                WHERE t.lokasi = ? AND p.status = 'selesai' AND DATE(p.created_at) BETWEEN ? AND ?
                            ");
                            $query_pendapatan->bind_param("sss", $posko, $tanggal_awal, $tanggal_akhir);
                            $query_pendapatan->execute();
                            echo number_format($query_pendapatan->get_result()->fetch_assoc()['total'] ?? 0, 0, ',', '.');
                            ?>
                        </h4>
                        <p class="mb-0">Pendapatan</p>
                    </div>
                    <i class="bi bi-currency-dollar fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Pemesanan -->
<div class="card">
    <div class="card-header">
        <h5>Daftar Pemesanan</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Kode Booking</th>
                        <th>Pelanggan</th>
                        <th>Paket</th>
                        <th>Jumlah</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Diverifikasi oleh</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = $konek->prepare("
                        SELECT p.kode_booking, u.nama_lengkap, t.nama_paket, p.jumlah_tiket, 
                               p.total_harga, p.status, p.created_at,
                               vh.admin_id, u2.nama_lengkap as admin_nama
                        FROM pemesanan p 
                        JOIN users u ON p.user_id = u.id 
                        JOIN tiket t ON p.tiket_id = t.id
                        LEFT JOIN verifikasi_history vh ON p.id = vh.pemesanan_id
                        LEFT JOIN users u2 ON vh.admin_id = u2.id
                        WHERE t.lokasi = ? AND DATE(p.created_at) BETWEEN ? AND ?
                        ORDER BY p.created_at DESC
                    ");
                    $query->bind_param("sss", $posko, $tanggal_awal, $tanggal_akhir);
                    $query->execute();
                    $result = $query->get_result();
                    
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $statusClass = ($row['status']=='pending')?'bg-warning text-dark':
                                           (($row['status']=='dibayar')?'bg-info':
                                           (($row['status']=='selesai')?'bg-success':'bg-danger'));
                            echo "<tr>
                                <td>" . htmlspecialchars($row['kode_booking']) . "</td>
                                <td>" . htmlspecialchars($row['nama_lengkap']) . "</td>
                                <td>" . htmlspecialchars($row['nama_paket']) . "</td>
                                <td>" . htmlspecialchars($row['jumlah_tiket']) . "</td>
                                <td>Rp " . number_format($row['total_harga'], 0, ',', '.') . "</td>
                                <td><span class='badge $statusClass'>" . ucfirst(htmlspecialchars($row['status'])) . "</span></td>
                                <td>" . ($row['admin_nama'] ? htmlspecialchars($row['admin_nama']) : '<span class="text-muted">Belum</span>') . "</td>
                                <td>" . date('d/m/Y H:i', strtotime($row['created_at'])) . "</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center text-muted'>Tidak ada data.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
ob_end_flush(); // Kirim output ke browser
?>