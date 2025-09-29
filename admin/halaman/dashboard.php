<?php
include '../../database/konek.php';
include '../boot.php';

session_start();

if (!isset($_SESSION['username'])) {
    echo "<script>document.location.href='../login/login.php';</script>";
    exit;
}

// Tambahkan pengecekan role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak! Hanya admin yang diizinkan.'); document.location.href='../login/login.php';</script>";
    exit;
}

// Cek koneksi
if ($konek->connect_error) {
    die("Koneksi gagal: " . $konek->connect_error);
}

// --- STATISTIK DASHBOARD ---
// Menghitung total tiket yang tersedia di database
$queryTotalTiket = "SELECT COUNT(*) as total FROM tickets";
$hasilTotalTiket = $konek->query($queryTotalTiket);
$dataTotalTiket = $hasilTotalTiket->fetch_assoc();
$totalTiketTersedia = $dataTotalTiket['total'];

// Menghitung total user dengan role 'user' (bukan admin)
$queryTotalUser = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
$hasilTotalUser = $konek->query($queryTotalUser);
$dataTotalUser = $hasilTotalUser->fetch_assoc();
$totalUserTerdaftar = $dataTotalUser['total'];

// Menghitung transaksi yang sudah dibayar (status 'paid')
$queryTransaksiSukses = "SELECT COUNT(*) as total FROM transactions WHERE status = 'paid'";
$hasilTransaksiSukses = $konek->query($queryTransaksiSukses);
$dataTransaksiSukses = $hasilTransaksiSukses->fetch_assoc();
$totalTransaksiBerhasil = $dataTransaksiSukses['total'];

// Menghitung total pendapatan dari transaksi yang sudah dibayar
$queryPendapatanTotal = "SELECT SUM(total_harga) as total FROM transactions WHERE status = 'paid'";
$hasilPendapatan = $konek->query($queryPendapatanTotal);
$dataPendapatan = $hasilPendapatan->fetch_assoc();
$totalPendapatanKotor = $dataPendapatan['total'] ?? 0; // Handle jika tidak ada transaksi

// --- DATA TRANSAKSI TERBARU ---
// Mengambil 5 transaksi terbaru dengan detail lengkap
$queryTransaksiTerbaru = "
    SELECT 
        t.id AS id_transaksi,
        t.user_id,
        t.ticket_id,
        t.jumlah_tiket,
        t.total_harga,
        t.status,
        t.tanggal_pesan,
        u.nama_lengkap,
        tk.nama_paket,
        tk.harga AS harga_per_tiket
    FROM transactions t 
    JOIN users u ON t.user_id = u.id 
    JOIN tickets tk ON t.ticket_id = tk.id 
    ORDER BY t.tanggal_pesan DESC 
    LIMIT 5
";
$hasilTransaksiTerbaru = $konek->query($queryTransaksiTerbaru);
$daftarTransaksiTerbaru = [];

// Ambil semua data transaksi terbaru
while ($baris = $hasilTransaksiTerbaru->fetch_assoc()) {
    $daftarTransaksiTerbaru[] = $baris;
}

// Tutup koneksi
$konek->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Tiket Labuan Bajo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

<body style="margin:0; padding:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color:#f8f9fa;">
    <!-- Main Content -->
    <div style="flex:1; padding:20px;">
        <div style="background:white; border-radius:10px; box-shadow:0 4px 15px rgba(0,0,0,0.1); padding:20px; margin-bottom:30px;">
            <h1 style="margin:0 0 20px; color:#2c3e50;">Dashboard Admin</h1>

            <!-- Statistik Cards -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:20px; margin-bottom:30px;">
                <div style="background:#e3f2fd; border-left:4px solid #2196f3; padding:20px; border-radius:5px;">
                    <h3 style="margin:0 0 10px; color:#2196f3;"><?php echo $totalTiketTersedia; ?></h3>
                    <p style="margin:0; color:#757575;">Total Tiket</p>
                </div>
                <div style="background:#e8f5e9; border-left:4px solid #4caf50; padding:20px; border-radius:5px;">
                    <h3 style="margin:0 0 10px; color:#4caf50;"><?php echo $totalUserTerdaftar; ?></h3>
                    <p style="margin:0; color:#757575;">Total User</p>
                </div>
                <div style="background:#fff3e0; border-left:4px solid #ff9800; padding:20px; border-radius:5px;">
                    <h3 style="margin:0 0 10px; color:#ff9800;"><?php echo $totalTransaksiBerhasil; ?></h3>
                    <p style="margin:0; color:#757575;">Transaksi</p>
                </div>
                <div style="background:#fce4ec; border-left:4px solid #e91e63; padding:20px; border-radius:5px;">
                    <h3 style="margin:0 0 10px; color:#e91e63;">Rp <?php echo number_format($totalPendapatanKotor, 0, ',', '.'); ?></h3>
                    <p style="margin:0; color:#757575;">Pendapatan</p>
                </div>
            </div>

            <!-- Recent Transactions -->
            <h2 class="text-dark mb-4">Transaksi Terbaru</h2>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Tiket</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($daftarTransaksiTerbaru)): ?>
                            <?php foreach ($daftarTransaksiTerbaru as $transaction): ?>
                                <tr>
                                    <td><?php echo $transaction['id_transaksi']; ?></td>
                                    <td><?php echo $transaction['nama_lengkap']; ?></td>
                                    <td><?php echo $transaction['nama_paket']; ?></td>
                                    <td>Rp <?php echo number_format($transaction['total_harga'], 0, ',', '.'); ?></td>
                                    <td>
                                        <?php if ($transaction['status'] == 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php elseif ($transaction['status'] == 'paid'): ?>
                                            <span class="badge bg-success">Paid</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($transaction['tanggal_pesan'])); ?></td>
                                    <td>
                                        <a href="transactions/detail.php?id=<?php echo $transaction['id_transaksi']; ?>" class="btn btn-sm btn-primary">Detail</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data transaksi</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
</body>

</html>