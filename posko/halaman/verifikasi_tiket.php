<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login posko
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'posko') {
    $_SESSION['error_message'] = 'Akses ditolak!';
    header('Location: ../login/login.php');
    exit;
}

// Pastikan session id_user dan lokasi ada
if (!isset($_SESSION['id_user']) || !isset($_SESSION['lokasi'])) {
    $_SESSION['error_message'] = 'Session tidak lengkap. Silakan login ulang.';
    header('Location: ../login/login.php');
    exit;
}

// Cek kode booking
if (!isset($_GET['kode']) || empty($_GET['kode'])) {
    header("location:?page=posko_dashboard&pesan=kode_tidak_ditemukan");
    exit();
}

$kode_booking = $_GET['kode'];
$id_admin = $_SESSION['id_user'];
$lokasi_admin = $_SESSION['lokasi'];

// Pastikan koneksi database tersedia
include '../../database/konek.php';


// Ambil data pemesanan berdasarkan kode dan lokasi posko
 $stmt = $konek->prepare("
    SELECT p.*, t.nama_paket, u.nama_lengkap 
    FROM pemesanan p 
    JOIN tiket t ON p.tiket_id = t.id 
    JOIN users u ON p.user_id = u.id 
    WHERE p.kode_booking = ? AND t.lokasi = ?
");
 $stmt->bind_param("ss", $kode_booking, $lokasi_admin);
 $stmt->execute();
 $result = $stmt->get_result();
 $data = $result->fetch_assoc();

if (!$data) {
    header("location:?page=posko_dashboard&pesan=data_tidak_ditemukan");
    exit();
}

// Ambil riwayat verifikasi untuk menampilkan siapa yang sudah memverifikasi
 $history_stmt = $konek->prepare("
    SELECT vh.*, u.nama_lengkap as admin_nama 
    FROM verifikasi_history vh 
    JOIN users u ON vh.admin_id = u.id 
    WHERE vh.pemesanan_id = ? 
    ORDER BY vh.created_at DESC
");
 $history_stmt->bind_param("i", $data['id']);
 $history_stmt->execute();
 $history_result = $history_stmt->get_result();

// Proses update verifikasi
if (isset($_POST['verifikasi'])) {
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $status_baru = $_POST['status'];
    $catatan = $_POST['catatan'];

    // Update status di tabel pemesanan
    $update_stmt = $konek->prepare("UPDATE pemesanan SET status = ?, metode_pembayaran = ? WHERE id = ?");
    $update_stmt->bind_param("ssi", $status_baru, $metode_pembayaran, $data['id']);

    // Catat ke history verifikasi
    $history_stmt = $konek->prepare("
        INSERT INTO verifikasi_history (pemesanan_id, admin_id, metode_pembayaran, status, catatan) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $history_stmt->bind_param("iisss", $data['id'], $id_admin, $metode_pembayaran, $status_baru, $catatan);

    if ($update_stmt->execute() && $history_stmt->execute()) {
        header("location:?page=posko_dashboard&pesan=berhasil_verifikasi");
        exit();
    } else {
        $error = true;
    }
}
?>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h4>Verifikasi Pembayaran</h4>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">Terjadi kesalahan saat memperbarui data.</div>
        <?php endif; ?>

        <p><strong>Kode Booking:</strong> <?= htmlspecialchars($data['kode_booking']) ?></p>
        
        <form action="" method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Pelanggan:</strong> <?= htmlspecialchars($data['nama_lengkap']) ?></p>
                    <p><strong>Paket:</strong> <?= htmlspecialchars($data['nama_paket']) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Tanggal Kunjungan:</strong> <?= htmlspecialchars($data['tanggal_kunjungan']) ?></p>
                    <p><strong>Total Harga:</strong> Rp <?= number_format($data['total_harga'], 0, ',', '.') ?></p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="metode_pembayaran" class="form-label">Metode Pembayaran</label>
                    <select name="metode_pembayaran" id="metode_pembayaran" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <option value="Tunai">Tunai</option>
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="QRIS">QRIS</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="pending">Pending</option>
                        <option value="dibayar">Dibayar</option>
                        <option value="selesai">Selesai</option>
                        <option value="batal">Batal</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="catatan" class="form-label">Catatan (Opsional)</label>
                <textarea name="catatan" id="catatan" class="form-control" rows="3"></textarea>
            </div>

            <button type="submit" name="verifikasi" class="btn btn-success">
                <i class="bi bi-check-circle"></i> Simpan Verifikasi
            </button>
            <a href="?page=posko_dashboard" class="btn btn-secondary">Batal</a>
        </form>
        
        <!-- Riwayat Verifikasi -->
        <div class="mt-4">
            <h5>Riwayat Verifikasi</h5>
            <?php if ($history_result->num_rows > 0): ?>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Admin</th>
                            <th>Status</th>
                            <th>Metode</th>
                            <th>Catatan</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($history = $history_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($history['admin_nama']) ?></td>
                            <td><span class="badge bg-info"><?= ucfirst($history['status']) ?></span></td>
                            <td><?= htmlspecialchars($history['metode_pembayaran']) ?></td>
                            <td><?= htmlspecialchars($history['catatan']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($history['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">Belum ada riwayat verifikasi</p>
            <?php endif; ?>
        </div>
    </div>
</div>