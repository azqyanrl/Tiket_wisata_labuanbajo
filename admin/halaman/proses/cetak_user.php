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
    
    // Query untuk data user
    $query_user = $konek->prepare("SELECT * FROM users WHERE id = ?");
    $query_user->bind_param("i", $user_id);
    $query_user->execute();
    $result_user = $query_user->get_result();
    $user = $result_user->fetch_assoc();
    
    if ($user) {
        // Query untuk data pemesanan user
        $query_pemesanan = $konek->prepare("SELECT p.*, t.nama_paket, t.gambar FROM pemesanan p JOIN tiket t ON p.tiket_id = t.id WHERE p.user_id = ? ORDER BY p.created_at DESC");
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
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .user-info { margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .ticket-img { max-width: 100px; height: auto; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Labuan Bajo Tourism System</h2>
        <h3>Data User</h3>
    </div>
    
    <div class="user-info">
        <h4>Informasi User</h4>
        <table class="table">
            <tr>
                <td width="20%">Nama Lengkap</td>
                <td width="2%">:</td>
                <td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
            </tr>
            <tr>
                <td>Username</td>
                <td>:</td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
            </tr>
            <tr>
                <td>Email</td>
                <td>:</td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
            </tr>
            <tr>
                <td>No. HP</td>
                <td>:</td>
                <td><?php echo htmlspecialchars($user['no_hp']); ?></td>
            </tr>
            <tr>
                <td>Role</td>
                <td>:</td>
                <td><?php echo ucfirst(htmlspecialchars($user['role'])); ?></td>
            </tr>
            <tr>
                <td>Tanggal Daftar</td>
                <td>:</td>
                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
            </tr>
        </table>
    </div>
    
    <h4>Riwayat Pemesanan</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Kode Booking</th>
                <th>Paket</th>
                <th>Gambar</th>
                <th>Tanggal Kunjungan</th>
                <th>Jumlah Tiket</th>
                <th>Total Harga</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_pemesanan->num_rows > 0): ?>
                <?php while($pemesanan = $result_pemesanan->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($pemesanan['kode_booking']); ?></td>
                    <td><?php echo htmlspecialchars($pemesanan['nama_paket']); ?></td>
                    <td><img src="../../../assets/images/tiket/<?php echo htmlspecialchars($pemesanan['gambar']); ?>" class="ticket-img" alt="<?php echo htmlspecialchars($pemesanan['nama_paket']); ?>"></td>
                    <td><?php echo date('d/m/Y', strtotime($pemesanan['tanggal_kunjungan'])); ?></td>
                    <td><?php echo htmlspecialchars($pemesanan['jumlah_tiket']); ?></td>
                    <td>Rp <?php echo number_format($pemesanan['total_harga'], 0, ',', '.'); ?></td>
                    <td><?php echo ucfirst(htmlspecialchars($pemesanan['status'])); ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">Tidak ada riwayat pemesanan</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="no-print" style="margin-top: 20px;">
        <button onclick="window.print()" class="btn btn-primary">Cetak</button>
        <button onclick="window.close()" class="btn btn-secondary">Kembali</button>
    </div>
    
    <div class="text-center mt-4">
        <p class="small text-muted">Dicetak pada tanggal: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>
</body>
</html>
<?php
    } else {
        echo "User tidak ditemukan";
    }
} else {
    echo "ID user tidak disertakan";
}
?>