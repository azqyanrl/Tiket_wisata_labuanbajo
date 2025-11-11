<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hanya admin dan posko yang boleh mengakses
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'posko'])) {
    echo "<script>alert('Akses ditolak!'); document.location.href='../../../login/login.php';</script>";
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
    <title>Cetak Data User - <?= htmlspecialchars($user['nama_lengkap']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        @media print {
            .d-print-none { display: none !important; }
        }
        .avatar-circle {
            width: 120px; height: 120px;
            border-radius: 50%;
            background-color: #0d6efd;
            color: white;
            font-size: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            text-transform: uppercase;
            margin: auto;
        }
    </style>
</head>
<body class="p-4">
    <div class="container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <img src="../../../assets/images/logo.png" alt="Logo" width="100" onerror="this.style.display='none'">
            </div>
            <div class="text-center flex-fill">
                <h3 class="fw-bold text-primary mb-0">Labuan Bajo Tourism System</h3>
                <small class="text-muted">Laporan Data Pengguna</small>
            </div>
            <div class="text-end d-print-none">
                <span class="badge bg-primary">
                    <i class="bi bi-calendar3"></i> <?= date('d/m/Y'); ?>
                </span>
            </div>
        </div>
        <hr>

        <!-- Informasi Pengguna -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-person-circle me-2"></i>Informasi Pengguna
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    <div class="col-md-3 text-center">
                        <?php
                        $foto_path = "../../../assets/images/profile/" . htmlspecialchars($user['profile_photo'] ?? '');
                        if (!empty($user['profile_photo']) && file_exists($foto_path)) {
                            echo '<img src="' . $foto_path . '" alt="Foto Profil" class="img-thumbnail rounded-circle" width="120" height="120" style="object-fit:cover;">';
                        } else {
                            $initial = strtoupper(substr($user['username'], 0, 1));
                            echo '<div class="avatar-circle">' . $initial . '</div>';
                        }
                        ?>
                    </div>
                    <div class="col-md-9">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr><th>Nama Lengkap</th><td><?= htmlspecialchars($user['nama_lengkap']); ?></td></tr>
                                <tr><th>Username</th><td><?= htmlspecialchars($user['username']); ?></td></tr>
                                <tr><th>Email</th><td><?= htmlspecialchars($user['email']); ?></td></tr>
                                <tr><th>No. HP</th><td><?= htmlspecialchars($user['no_hp']); ?></td></tr>
                                <tr>
                                    <th>Role</th>
                                    <td>
                                        <span class="badge 
                                            <?= $user['role'] == 'admin' ? 'bg-danger' : 
                                               ($user['role'] == 'posko' ? 'bg-primary' : 'bg-success'); ?>">
                                            <?= ucfirst(htmlspecialchars($user['role'])); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr><th>Lokasi</th><td><?= htmlspecialchars($user['lokasi'] ?: '-'); ?></td></tr>
                                <tr><th>Tanggal Daftar</th><td><?= date('d F Y', strtotime($user['created_at'])); ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat Pemesanan -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div><i class="bi bi-clock-history me-2"></i>Riwayat Pemesanan</div>
                <span class="badge bg-light text-dark"><?= $result_pemesanan->num_rows; ?> Transaksi</span>
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
                                while($p = $result_pemesanan->fetch_assoc()):
                                    $total_transaksi += $p['total_harga'];
                                ?>
                                <tr>
                                    <td class="text-center"><span class="badge bg-info text-dark"><?= htmlspecialchars($p['kode_booking']); ?></span></td>
                                    <td><?= htmlspecialchars($p['nama_paket']); ?></td>
                                    <td class="text-center">
                                        <img src="../../../assets/images/tiket/<?= htmlspecialchars($p['gambar']); ?>" width="70" height="70" class="rounded" style="object-fit:cover;">
                                    </td>
                                    <td><?= date('d F Y', strtotime($p['tanggal_kunjungan'])); ?></td>
                                    <td class="text-center"><?= htmlspecialchars($p['jumlah_tiket']); ?> Tiket</td>
                                    <td>Rp <?= number_format($p['total_harga'], 0, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <?php 
                                        $status = strtolower($p['status']);
                                        $class = $status == 'selesai' ? 'success' : ($status == 'pending' ? 'warning' : 'danger');
                                        ?>
                                        <span class="badge bg-<?= $class; ?>"><?= ucfirst($status); ?></span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="5" class="text-end">Total Transaksi</th>
                                    <th colspan="2">Rp <?= number_format($total_transaksi, 0, ',', '.'); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-inbox display-6 text-muted"></i>
                        <p class="text-muted mb-0">Tidak ada riwayat pemesanan</p>
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
            Dicetak pada <?= date('d F Y H:i:s'); ?><br>
            © <?= date('Y'); ?> Labuan Bajo Tourism System
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
