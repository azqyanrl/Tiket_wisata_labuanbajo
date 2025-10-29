<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'posko') {
    $_SESSION['error_message'] = 'Akses ditolak!';
    header('Location: login/login.php'); 
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';
$lokasi = $_SESSION['lokasi'] ?? '';

if (!isset($_GET['kode']) || trim($_GET['kode']) === '') {
    echo '<div class="alert alert-warning">Silakan pilih tiket yang akan diverifikasi dari halaman Dashboard.</div>';
    echo '<a href="index.php?page=dashboard" class="btn btn-primary">Kembali ke Dashboard</a>';
    exit;
}

 $kode = $_GET['kode'];

// ambil pemesanan & pastikan lokasi cocok
 $sql = "SELECT p.*, t.nama_paket, t.lokasi, u.nama_lengkap 
        FROM pemesanan p 
        JOIN tiket t ON p.tiket_id = t.id 
        JOIN users u ON p.user_id = u.id
        WHERE p.kode_booking = ? LIMIT 1";
 $stmt = $konek->prepare($sql);
 $stmt->bind_param('s', $kode);
 $stmt->execute();
 $res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo "<div class='alert alert-danger'>Tiket tidak ditemukan.</div>";
    echo '<a href="index.php?page=dashboard" class="btn btn-primary">Kembali ke Dashboard</a>';
    exit;
}
 $row = $res->fetch_assoc();
if ($row['lokasi'] !== $lokasi) {
    echo "<div class='alert alert-danger'>Tiket ini bukan untuk posko Anda.</div>";
    echo '<a href="index.php?page=dashboard" class="btn btn-primary">Kembali ke Dashboard</a>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metode = $_POST['metode_pembayaran'] ?? 'cash';
    $status = $_POST['status'] ?? 'dibayar';
    
    // Update data pemesanan
    $up = $konek->prepare("UPDATE pemesanan SET metode_pembayaran = ?, status = ? WHERE kode_booking = ?");
    $up->bind_param('sss', $metode, $status, $kode);
    
    if ($up->execute()) {
        // Catat history verifikasi
        $admin_id = $_SESSION['user_id'] ?? 0;
        $catatan = $_POST['catatan'] ?? '';
        
        $history = $konek->prepare("INSERT INTO verifikasi_history (pemesanan_id, admin_id, metode_pembayaran, status, catatan, created_at) 
                                  VALUES (?, ?, ?, ?, ?, NOW())");
        $history->bind_param('iisss', $row['id'], $admin_id, $metode, $status, $catatan);
        $history->execute();
        
        $_SESSION['success_message'] = 'Tiket berhasil diverifikasi!';
        header('Location: index.php?page=dashboard');
        exit;
    } else {
        $error = "Gagal memperbarui: " . $konek->error;
    }
}
?>

<div class="page-title">
    <h1>Verifikasi Tiket</h1>
</div>

<?php if(!empty($error)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detail Tiket - <?= htmlspecialchars($row['kode_booking']) ?></h6>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <p><strong>Pelanggan:</strong> <?= htmlspecialchars($row['nama_lengkap']) ?></p>
                <p><strong>Paket:</strong> <?= htmlspecialchars($row['nama_paket']) ?></p>
                <p><strong>Tanggal Kunjungan:</strong> <?= htmlspecialchars($row['tanggal_kunjungan']) ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Jumlah Tiket:</strong> <?= htmlspecialchars($row['jumlah_tiket']) ?></p>
                <p><strong>Total Harga:</strong> <?= 'Rp ' . number_format($row['total_harga'], 0, ',', '.') ?></p>
                <p><strong>Status Saat Ini:</strong> 
                    <?php 
                    $statusClass = '';
                    switch($row['status']) {
                        case 'pending': $statusClass = 'bg-warning'; break;
                        case 'dibayar': $statusClass = 'bg-success'; break;
                        case 'selesai': $statusClass = 'bg-info'; break;
                        case 'batal': $statusClass = 'bg-danger'; break;
                    }
                    ?>
                    <span class="badge <?= $statusClass ?>"><?= ucfirst($row['status']) ?></span>
                </p>
            </div>
        </div>

        <form method="post">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Metode Pembayaran</label>
                    <select name="metode_pembayaran" class="form-select" required>
                        <option value="cash" <?=($row['metode_pembayaran']=='cash')?'selected':''?>>Cash</option>
                        <option value="qris" <?=($row['metode_pembayaran']=='qris')?'selected':''?>>QRIS</option>
                        <option value="transfer" <?=($row['metode_pembayaran']=='transfer')?'selected':''?>>Transfer Bank</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status Tiket</label>
                    <select name="status" class="form-select" required>
                        <option value="pending" <?=($row['status']=='pending')?'selected':''?>>Pending</option>
                        <option value="dibayar" <?=($row['status']=='dibayar')?'selected':''?>>Dibayar</option>
                        <option value="selesai" <?=($row['status']=='selesai')?'selected':''?>>Selesai</option>
                        <option value="batal" <?=($row['status']=='batal')?'selected':''?>>Batal</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Catatan (Opsional)</label>
                <textarea name="catatan" class="form-control" rows="3"><?= htmlspecialchars($_POST['catatan'] ?? '') ?></textarea>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="index.php?page=dashboard" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>