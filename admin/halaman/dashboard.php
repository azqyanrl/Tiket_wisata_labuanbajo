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

// --- QUERY STATISTIK DASHBOARD ---
 $query_total = $konek->prepare("SELECT COUNT(*) as total FROM pemesanan");
 $query_total->execute();
 $result_total = $query_total->get_result();
 $totalPemesanan = $result_total->fetch_assoc()['total'];

 $query_selesai = $konek->prepare("SELECT COUNT(*) as total FROM pemesanan WHERE status = 'selesai'");
 $query_selesai->execute();
 $result_selesai = $query_selesai->get_result();
 $totalSelesai = $result_selesai->fetch_assoc()['total'];

 $query_pending = $konek->prepare("SELECT COUNT(*) as total FROM pemesanan WHERE status = 'pending'");
 $query_pending->execute();
 $result_pending = $query_pending->get_result();
 $totalPending = $result_pending->fetch_assoc()['total'];

 $query_pendapatan = $konek->prepare("SELECT SUM(total_harga) as total FROM pemesanan WHERE status = 'selesai'");
 $query_pendapatan->execute();
 $result_pendapatan = $query_pendapatan->get_result();
 $pendapatan = $result_pendapatan->fetch_assoc()['total'] ?? 0;

// --- QUERY PEMESANAN TERBARU ---
 $query_recent = $konek->prepare("SELECT p.kode_booking, u.nama_lengkap, t.nama_paket, p.total_harga, p.status, p.created_at 
                               FROM pemesanan p 
                               JOIN users u ON p.user_id = u.id 
                               JOIN tiket t ON p.tiket_id = t.id 
                               ORDER BY p.created_at DESC LIMIT 5");
 $query_recent->execute();
 $recentBookings = $query_recent->get_result();
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
                        <h4 class="mb-0"><?php echo $totalPemesanan; ?></h4>
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
                        <h4 class="mb-0"><?php echo $totalSelesai; ?></h4>
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
                        <h4 class="mb-0"><?php echo $totalPending; ?></h4>
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
                        <h4 class="mb-0">Rp <?php echo number_format($pendapatan, 0, ',', '.'); ?></h4>
                        <p class="mb-0">Pendapatan</p>
                    </div>
                    <i class="bi bi-currency-dollar fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Pemesanan Terbaru -->
<h4 class="mb-3">Pemesanan Terbaru</h4>
<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Kode Booking</th>
                <th>User</th>
                <th>Tiket</th>
                <th>Total</th>
                <th>Status</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($recentBookings->num_rows > 0) { 
                while($row = $recentBookings->fetch_assoc()) { 
                    $statusClass = ($row['status']=='pending')?'bg-warning text-dark':(($row['status']=='dibayar')?'bg-info':(($row['status']=='selesai')?'bg-success':'bg-danger')); 
                    echo "<tr>
                        <td>" . htmlspecialchars($row['kode_booking']) . "</td>
                        <td>" . htmlspecialchars($row['nama_lengkap']) . "</td>
                        <td>" . htmlspecialchars($row['nama_paket']) . "</td>
                        <td>Rp " . number_format($row['total_harga'], 0, ',', '.') . "</td>
                        <td><span class='badge $statusClass'>" . ucfirst(htmlspecialchars($row['status'])) . "</span></td>
                        <td>" . date('d/m/Y', strtotime($row['created_at'])) . "</td>
                    </tr>"; 
                } 
            } else { 
                echo "<tr><td colspan='6' class='text-center text-muted'>Tidak ada data.</td></tr>"; 
            } ?>
        </tbody>
    </table>
</div>