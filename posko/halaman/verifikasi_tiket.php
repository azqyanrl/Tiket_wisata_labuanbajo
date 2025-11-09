<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'posko') {
    echo "<script>alert('Akses ditolak!'); document.location.href='../login/login.php';</script>";
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';

// Redirect kalau diakses langsung (tanpa sidebar)
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header('Location: index.php?page=verifikasi_tiket');
    exit;
}

// Ambil ID admin dari database berdasarkan username
$username_admin = $_SESSION['username'];
$get_admin = $konek->prepare("SELECT id FROM users WHERE username = ? AND role = 'posko'");
$get_admin->bind_param("s", $username_admin);
$get_admin->execute();
$admin_result = $get_admin->get_result();
$admin_data = $admin_result->fetch_assoc();

if (!$admin_data || empty($admin_data['id'])) {
    echo "<div class='alert alert-danger'>Admin tidak ditemukan. Silakan login ulang.</div>";
    echo "<script>setTimeout(function() { window.location.href='../login/login.php'; }, 2000);</script>";
    exit;
}

$id_admin = $admin_data['id'];

// ✅ Proses form verifikasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pemesanan_id'])) {
    $pemesanan_id = intval($_POST['pemesanan_id']);
    $metode_pembayaran = $_POST['metode_pembayaran'] ?? '';
    $catatan = $_POST['catatan'] ?? '';

    // ⚙️ Status langsung diset "dibayar" seperti versi admin
    $status = 'dibayar';

    // Update status pemesanan
    $update = $konek->prepare("UPDATE pemesanan SET status = ?, metode_pembayaran = ? WHERE id = ?");
    $update->bind_param('ssi', $status, $metode_pembayaran, $pemesanan_id);

    if ($update->execute()) {
        // Simpan ke tabel verifikasi_history
        $insert = $konek->prepare("
            INSERT INTO verifikasi_history (pemesanan_id, admin_id, status, metode_pembayaran, catatan, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $insert->bind_param('iisss', $pemesanan_id, $id_admin, $status, $metode_pembayaran, $catatan);

        if ($insert->execute()) {
            echo "<div class='alert alert-success'>✅ Verifikasi berhasil disimpan dan status otomatis menjadi 'Dibayar'.</div>";
        } else {
            echo "<div class='alert alert-danger'>Gagal menyimpan riwayat: " . $insert->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Gagal memperbarui status: " . $update->error . "</div>";
    }

    $id = $pemesanan_id; // Refresh data
}

// Pastikan ID dikirim
if (!isset($_GET['id'])) {
    die("<div class='alert alert-danger'>ID pemesanan tidak ditemukan.</div>");
}

$id = intval($_GET['id']);

// Ambil data pemesanan
$query = $konek->prepare("
    SELECT p.id, p.kode_booking, u.nama_lengkap, t.nama_paket, p.tanggal_kunjungan, p.total_harga, p.status
    FROM pemesanan p
    JOIN users u ON p.user_id = u.id
    JOIN tiket t ON p.tiket_id = t.id
    WHERE p.id = ?
");
$query->bind_param("i", $id);
$query->execute();
$data = $query->get_result()->fetch_assoc();

if (!$data) {
    die("<div class='alert alert-danger'>Data pemesanan tidak ditemukan.</div>");
}

// Ambil riwayat
$history_query = $konek->prepare("
    SELECT vh.*, a.nama_lengkap AS admin_nama
    FROM verifikasi_history vh
    JOIN users a ON vh.admin_id = a.id
    WHERE vh.pemesanan_id = ?
    ORDER BY vh.created_at DESC
");
$history_query->bind_param("i", $id);
$history_query->execute();
$history_result = $history_query->get_result();
?>

<div class="card mt-3">
    <div class="card-header bg-primary text-white"><h4>Verifikasi Pembayaran</h4></div>
    <div class="card-body">
        <p><strong>Kode Booking:</strong> <?= htmlspecialchars($data['kode_booking']) ?></p>

        <form method="POST" action="">
            <input type="hidden" name="pemesanan_id" value="<?= $data['id'] ?>">

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
                    <label class="form-label">Metode Pembayaran</label>
                    <select name="metode_pembayaran" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <option value="Tunai">Tunai</option>
                        <option value="QRIS">QRIS</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status (Otomatis)</label>
                    <input type="text" class="form-control bg-light" value="Dibayar" readonly>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Catatan (Opsional)</label>
                <textarea name="catatan" class="form-control" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Simpan Verifikasi</button>
            <a href="?page=cari_tiket" class="btn btn-secondary">Batal</a>
        </form>

        <hr>
        <h5>Riwayat Verifikasi</h5>
        <?php if ($history_result->num_rows > 0): ?>
        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Admin</th>
                    <th>Status</th>
                    <th>Metode</th>
                    <th>Catatan</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($h = $history_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($h['admin_nama']) ?></td>
                    <td><span class="badge bg-success"><?= ucfirst($h['status']) ?></span></td>
                    <td><?= htmlspecialchars($h['metode_pembayaran']) ?></td>
                    <td><?= htmlspecialchars($h['catatan']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="text-muted">Belum ada riwayat verifikasi.</p>
        <?php endif; ?>
    </div>
</div>
