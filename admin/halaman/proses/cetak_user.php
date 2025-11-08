<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak!'); document.location.href='../login/login.php';</script>";
    exit;
}

include '../../../database/konek.php';
include '../../../includes/boot.php';

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    
    $query_user = $konek->prepare("SELECT * FROM users WHERE id = ?");
    $query_user->bind_param("i", $user_id);
    $query_user->execute();
    $result_user = $query_user->get_result();
    $user = $result_user->fetch_assoc();
    
    if ($user) {
        $query_pemesanan = $konek->prepare("
            SELECT p.*, t.nama_paket, t.gambar 
            FROM pemesanan p 
            JOIN tiket t ON p.tiket_id = t.id 
            WHERE p.user_id = ? 
            ORDER BY p.created_at DESC
        ");
        $query_pemesanan->bind_param("i", $user_id);
        $query_pemesanan->execute();
        $result_pemesanan = $query_pemesanan->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Data User - <?php echo htmlspecialchars($user['nama_lengkap']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="p-4">
    <div class="container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="text-center">
                <img src="../../../assets/images/logo.png" alt="Logo" width="100" onerror="this.style.display='none'">
            </div>
            <div class="text-center">
                <h3 class="fw-bold text-primary mb-0">Labuan Bajo Tourism System</h3>
                <small class="text-muted">Laporan Data Pengguna</small>
            </div>
            <div class="text-end d-print-none">
                <span class="badge bg-primary">
                    <i class="bi bi-calendar3"></i> <?php echo date('d/m/Y'); ?>
                </span>
            </div>
        </div>
        <hr>

        <!-- Informasi Pengguna -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-person-circle me-2"></i>Informasi Pengguna
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3 text-center">
                        <img src="../../../assets/images/profile/<?php echo htmlspecialchars($user['profile_photo'] ?: 'default.png'); ?>"
                             alt="Foto Profil" class="img-thumbnail rounded-circle"
                             onerror="this.src='../../../assets/images/profile/default.png'">
                    </div>
                    <div class="col-md-9">
                        <table class="table table-sm table-borderless">
                            <tbody>
                                <tr><th>Nama Lengkap</th><td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td></tr>
                                <tr><th>Username</th><td><?php echo htmlspecialchars($user['username']); ?></td></tr>
                                <tr><th>Email</th><td><?php echo htmlspecialchars($user['email']); ?></td></tr>
                                <tr><th>No. HP</th><td><?php echo htmlspecialchars($user['no_hp']); ?></td></tr>
                                <tr>
                                    <th>Role</th>
                                    <td>
                                        <span class="badge 
                                            <?php echo $user['role'] == 'admin' ? 'bg-danger' : 
                                                  ($user['role'] == 'posko' ? 'bg-primary' : 'bg-success'); ?>">
                                            <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr><th>Lokasi</th><td><?php echo htmlspecialchars($user['lokasi'] ?: '-'); ?></td></tr>
                                <tr><th>Tanggal Daftar</th><td><?php echo date('d F Y', strtotime($user['created_at'])); ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat Pemesanan -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div><i class="bi bi-clock-history me-2"></i>Riwayat Pemesanan</div>
                <span class="badge bg-light text-dark"><?php echo $result_pemesanan->num_rows; ?> Transaksi</span>
            </div>
            <div class="card-body p-0">
                <?php if ($result_pemesanan->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th>Kode</th>
                                    <th>Paket</th>
                                    <th>Gambar</th>
                                    <th>Tanggal</th>
                                    <th>Jumlah</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_transaksi = 0;
                                while($pemesanan = $result_pemesanan->fetch_assoc()):
                                    $total_transaksi += $pemesanan['total_harga'];
                                ?>
                                <tr>
                                    <td class="text-center"><span class="badge bg-info text-dark"><?php echo htmlspecialchars($pemesanan['kode_booking']); ?></span></td>
                                    <td><?php echo htmlspecialchars($pemesanan['nama_paket']); ?></td>
                                    <td class="text-center">
                                        <img src="../../../assets/images/tiket/<?php echo htmlspecialchars($pemesanan['gambar']); ?>" 
                                             width="70" height="70" class="rounded" alt="">
                                    </td>
                                    <td><?php echo date('d F Y', strtotime($pemesanan['tanggal_kunjungan'])); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($pemesanan['jumlah_tiket']); ?> Tiket</td>
                                    <td>Rp <?php echo number_format($pemesanan['total_harga'], 0, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <?php 
                                        $status = strtolower($pemesanan['status']);
                                        $class = $status == 'selesai' ? 'success' : ($status == 'pending' ? 'warning' : 'danger');
                                        ?>
                                        <span class="badge bg-<?php echo $class; ?>"><?php echo ucfirst(htmlspecialchars($pemesanan['status'])); ?></span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="5" class="text-end">Total Transaksi</th>
                                    <th colspan="2">Rp <?php echo number_format($total_transaksi, 0, ',', '.'); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-inbox display-6 text-muted"></i>
                        <p class="text-muted">Tidak ada riwayat pemesanan</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="d-flex justify-content-between d-print-none mb-4">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Cetak
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Tutup
            </button>
        </div>

        <!-- Footer -->
        <div class="text-center text-muted small">
            Dicetak pada <?php echo date('d F Y H:i:s'); ?><br>
            © <?php echo date('Y'); ?> Labuan Bajo Tourism System
        </div>
    </div>
</body>
</html>
<?php
    } else {
        echo '<div class="alert alert-danger">User tidak ditemukan</div>';
    }
} else {
    echo '<div class="alert alert-warning">ID user tidak disertakan</div>';
}
?>
