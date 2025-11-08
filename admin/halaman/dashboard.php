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
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Beranda</h1>
</div>

<!-- Statistik Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card text-white bg-primary shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">
                            <?php 
                                $query_total = $konek->prepare("SELECT COUNT(*) as total FROM pemesanan");
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
    <div class="col-xl-3 col-md-6">
        <div class="card text-white bg-success shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">
                            <?php 
                                $query_selesai = $konek->prepare("SELECT COUNT(*) as total FROM pemesanan WHERE status = 'selesai'");
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
    <div class="col-xl-3 col-md-6">
        <div class="card text-white bg-warning shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">
                            <?php 
                                $query_pending = $konek->prepare("SELECT COUNT(*) as total FROM pemesanan WHERE status = 'pending'");
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
    <div class="col-xl-3 col-md-6">
        <div class="card text-white bg-info shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">
                            Rp 
                            <?php 
                                $query_pendapatan = $konek->prepare("SELECT SUM(total_harga) as total FROM pemesanan WHERE status = 'selesai'");
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

<!-- Statistik Per Posko -->
<div class="row mb-4">
    <div class="col-12">
        <h4>Statistik Per Posko</h4>
    </div>
    <?php
    $stats_posko = $konek->query("
        SELECT 
            t.lokasi,
            COUNT(p.id) as total_pemesanan,
            SUM(CASE WHEN p.status = 'selesai' THEN 1 ELSE 0 END) as total_selesai,
            SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) as total_pending,
            SUM(CASE WHEN p.status = 'selesai' THEN p.total_harga ELSE 0 END) as total_pendapatan
        FROM pemesanan p
        JOIN tiket t ON p.tiket_id = t.id
        GROUP BY t.lokasi
        ORDER BY total_pendapatan DESC
    ");
    
    while($stat = $stats_posko->fetch_assoc()):
    ?>
    <div class="col-md-3">
        <div class="card border-start border-4 border-primary shadow h-100 py-2">
            <div class="card-body">
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
    <?php endwhile; ?>
</div>

<!-- Pemesanan Terbaru -->
<h4 class="mb-3">Pemesanan Terbaru</h4>
<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Kode Booking</th>
                <th>User</th>
                <th>Tiket</th>
                <th>Posko</th>
                <th>Jumlah</th>
                <th>Total</th>
                <th>Status</th>
                <th>Diverifikasi Oleh</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query_recent = $konek->prepare("
                SELECT p.kode_booking, u.nama_lengkap, t.nama_paket, t.lokasi AS nama_posko,
                       p.jumlah_tiket, p.total_harga, p.status, p.created_at,
                       u2.nama_lengkap AS admin_nama, u2.lokasi AS admin_posko
                FROM pemesanan p
                JOIN users u ON p.user_id = u.id
                JOIN tiket t ON p.tiket_id = t.id
                LEFT JOIN verifikasi_history vh ON vh.pemesanan_id = p.id
                LEFT JOIN users u2 ON vh.admin_id = u2.id
                ORDER BY p.created_at DESC
                LIMIT 10
            ");
            $query_recent->execute();
            $recent = $query_recent->get_result();

            if ($recent->num_rows > 0):
                while($r = $recent->fetch_assoc()):
                    $statusClass = match($r['status']) {
                        'pending' => 'bg-warning text-dark',
                        'dibayar' => 'bg-info',
                        'selesai' => 'bg-success',
                        'dibatalkan' => 'bg-danger',
                        default => 'bg-secondary'
                    };

                    // Tentukan teks "Diverifikasi oleh"
                    if ($r['status'] == 'pending') {
                        $verifikator = '<span class="text-muted">-</span>';
                    } elseif ($r['admin_nama']) {
                        // Ada admin yang verifikasi
                        if (!empty($r['admin_posko'])) {
                            $verifikator = htmlspecialchars($r['admin_nama']) . " <span class='badge bg-secondary'>" . htmlspecialchars($r['admin_posko']) . "</span>";
                        } else {
                            $verifikator = '<span class="fw-semibold text-primary">Admin Pusat</span>';
                        }
                    } else {
                        // Tidak ada riwayat, tapi status bukan pending
                        $verifikator = '<span class="fw-semibold text-primary">Admin Pusat</span>';
                    }
            ?>
            <tr>
                <td><?= htmlspecialchars($r['kode_booking']) ?></td>
                <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
                <td><?= htmlspecialchars($r['nama_paket']) ?></td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars($r['nama_posko']) ?></span></td>
                <td><?= htmlspecialchars($r['jumlah_tiket']) ?></td>
                <td>Rp <?= number_format($r['total_harga'], 0, ',', '.') ?></td>
                <td><span class="badge <?= $statusClass ?>"><?= ucfirst($r['status']) ?></span></td>
                <td><?= $verifikator ?></td>
                <td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="9" class="text-center text-muted">Tidak ada data pemesanan terbaru.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
