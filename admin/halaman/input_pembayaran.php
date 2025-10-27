<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('location: ../login/login.php');
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';

$kode_booking = '';
$pemesanan_data = null;
$user_data = null;
$tiket_data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cari_booking'])) {
    $kode_booking = trim($_POST['kode_booking']);

    // 🔧 diubah agar pencarian bisa sebagian (LIKE)
    $query_pemesanan = $konek->prepare("
        SELECT p.*, u.nama_lengkap, u.email, u.no_hp, t.nama_paket, t.harga, t.gambar 
        FROM pemesanan p 
        JOIN users u ON p.user_id = u.id 
        JOIN tiket t ON p.tiket_id = t.id 
        WHERE p.kode_booking LIKE ?
        LIMIT 10
    ");
    $kode_booking_like = "%" . $kode_booking . "%";
    $query_pemesanan->bind_param("s", $kode_booking_like);
    $query_pemesanan->execute();
    $result_pemesanan = $query_pemesanan->get_result();

    if ($result_pemesanan->num_rows > 0) {
        // 🔧 ambil hanya satu dulu (bisa dikembangkan nanti untuk multi hasil)
        $pemesanan_data = $result_pemesanan->fetch_assoc();
        $user_data = [
            'nama_lengkap' => $pemesanan_data['nama_lengkap'],
            'email' => $pemesanan_data['email'],
            'no_hp' => $pemesanan_data['no_hp']
        ];
        $tiket_data = [
            'nama_paket' => $pemesanan_data['nama_paket'],
            'harga' => $pemesanan_data['harga'],
            'gambar' => $pemesanan_data['gambar']
        ];
    } else {
        $error_message = "Kode booking tidak ditemukan";
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Input Pembayaran</h1>
</div>

<?php if (isset($error_message)): ?>

```
<div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
```

<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>

```
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']);
unset($_SESSION['error_message']); ?></div>
```

<?php endif; ?>

<?php if (isset($_SESSION['success_message'])): ?>

```
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']);
unset($_SESSION['success_message']); ?></div>
```

<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <h5>Cari Pemesanan Berdasarkan Kode Booking</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-8">
                    <label for="kode_booking" class="form-label">Kode Booking</label>
                    <input type="text" class="form-control" id="kode_booking" name="kode_booking"
                        value="<?php echo htmlspecialchars($kode_booking); ?>" required>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" name="cari_booking" class="btn btn-primary w-100">Cari</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($pemesanan_data): ?>
<!-- bagian detail pemesanan dan pembayaran tetap sama -->
<div class="card mb-4">
    <div class="card-header">
        <h5>Detail Pemesanan</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Informasi Pemesanan</h6>
                <p><strong>Kode Booking:</strong> <?php echo htmlspecialchars($pemesanan_data['kode_booking']); ?></p>
                <p><strong>Tanggal Kunjungan:</strong> <?php echo date('d/m/Y', strtotime($pemesanan_data['tanggal_kunjungan'])); ?></p>
                <p><strong>Jumlah Tiket:</strong> <?php echo htmlspecialchars($pemesanan_data['jumlah_tiket']); ?></p>
                <p><strong>Total Harga:</strong> Rp <?php echo number_format($pemesanan_data['total_harga'], 0, ',', '.'); ?></p>
                <p><strong>Status:</strong> 
                    <span class="badge bg-<?php echo ($pemesanan_data['status'] == 'pending') ? 'warning' : 'info'; ?>">
                        <?php echo ucfirst(htmlspecialchars($pemesanan_data['status'])); ?>
                    </span>
                </p>
            </div>
            <div class="col-md-6">
                <h6>Informasi Pengguna</h6>
                <p><strong>Nama Lengkap:</strong> <?php echo htmlspecialchars($user_data['nama_lengkap']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user_data['email']); ?></p>
                <p><strong>No. HP:</strong> <?php echo htmlspecialchars($user_data['no_hp']); ?></p>
                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalCetakUser">
                    <i class="bi bi-printer"></i> Cetak Data User
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Proses Pembayaran</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="proses/proses_pembayaran.php">
            <input type="hidden" name="pemesanan_id" value="<?php echo htmlspecialchars($pemesanan_data['id']); ?>">
            <input type="hidden" name="kode_booking" value="<?php echo htmlspecialchars($pemesanan_data['kode_booking']); ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="metode_pembayaran" class="form-label">Metode Pembayaran</label>
                    <select class="form-select" id="metode_pembayaran" name="metode_pembayaran" required>
                        <option value="">Pilih Metode Pembayaran</option>
                        <option value="tunai">Tunai</option>
                        <option value="Qris">Qris</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" name="proses_pembayaran" class="btn btn-primary">Proses Pembayaran</button>
                    <a href="index.php?page=kelola_pemesanan" class="btn btn-secondary">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>
```

<?php endif; ?>

<!-- Modal Cetak User (tidak diubah) -->

<script>
function printDiv(divName) {
    var printContents = document.getElementById(divName).innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
}
</script>
