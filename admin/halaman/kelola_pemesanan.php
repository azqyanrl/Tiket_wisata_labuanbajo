<?php
// Proses perubahan status (dibayar, selesai, batal)
include '../../database/konek.php';
include '../../includes/boot.php';
if (isset($_GET['action']) && isset($_GET['id'])) {
    $booking_id = intval($_GET['id']); $new_status = $_GET['action'];
    if (in_array($new_status, ['dibayar', 'selesai', 'batal'])) {
        $stmtUpdate = $konek->prepare("UPDATE pemesanan SET status = ? WHERE id = ?");
        $stmtUpdate->bind_param("si", $new_status, $booking_id); $stmtUpdate->execute();
        $_SESSION['success_message'] = "Status pemesanan berhasil diperbarui."; $stmtUpdate->close();
    }
    header("Location: ?page=kelola_pemesanan"); exit();
}

// Logika Pencarian
 $search = isset($_GET['search']) ? trim($_GET['search']) : '';
 $query = "SELECT p.id, p.kode_booking, p.tanggal_kunjungan, p.total_harga, p.status, u.nama_lengkap, u.email, t.nama_paket, pay.bukti_transfer, pay.status as payment_status FROM pemesanan p JOIN users u ON p.user_id = u.id JOIN tiket t ON p.tiket_id = t.id LEFT JOIN payments pay ON p.id = pay.pemesanan_id";
if ($search != '') { $query .= " WHERE p.kode_booking LIKE ? OR u.nama_lengkap LIKE ? OR u.email LIKE ?"; }
 $query .= " ORDER BY p.created_at DESC";
 $stmt = $konek->prepare($query);
if ($search != '') { $param = "%$search%"; $stmt->bind_param("sss", $param, $param, $param); }
 $stmt->execute(); $result = $stmt->get_result();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"><h1 class="h2">Kelola Pemesanan</h1></div>
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert"><?= $_SESSION['success_message']; ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>
<form method="GET" class="mb-4"><div class="input-group"><input type="text" name="search" class="form-control" placeholder="Cari berdasarkan Kode Booking, Nama, atau Email..." value="<?= htmlspecialchars($search); ?>"><button class="btn btn-outline-secondary" type="submit">Cari</button></div></form>
<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-light"><tr><th>Kode Booking</th><th>Pemesan</th><th>Paket</th><th>Total</th><th>Status</th><th>Bukti Pembayaran</th><th>Aksi</th></tr></thead>
        <tbody>
            <?php if ($result->num_rows > 0): while ($data = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($data['kode_booking']); ?></td><td><?= htmlspecialchars($data['nama_lengkap']); ?></td><td><?= htmlspecialchars($data['nama_paket']); ?></td><td>Rp <?= number_format($data['total_harga'], 0, ',', '.'); ?></td>
                    <td><span class="badge bg-<?php echo ($data['status'] == 'pending') ? 'warning text-dark' : (($data['status'] == 'dibayar') ? 'info' : (($data['status'] == 'selesai') ? 'success' : 'danger')); ?>"><?= ucfirst(htmlspecialchars($data['status'])); ?></span></td>
                    <td>
                        <?php if ($data['bukti_transfer']): ?>
                            <a href="../../assets/bukti_pembayaran/<?= htmlspecialchars($data['bukti_transfer']); ?>" target="_blank" class="btn btn-sm btn-info">Lihat Bukti</a><br><small class="text-muted">(<?= ucfirst($data['payment_status']); ?>)</small>
                        <?php else: ?><span class="text-muted">Belum ada</span><?php endif; ?>
                    </td>
                    <td>
                        <?php if ($data['status'] == 'pending' && $data['bukti_transfer'] && $data['payment_status'] == 'unverified'): ?>
                            <a href="proses/verifikasi_pembayaran.php?id=<?= $data['id']; ?>&action=confirm" class="btn btn-sm btn-success" onclick="return confirm('Konfirmasi pembayaran ini?')">Verifikasi & Konfirmasi</a>
                            <a href="proses/verifikasi_pembayaran.php?id=<?= $data['id']; ?>&action=reject" class="btn btn-sm btn-danger" onclick="return confirm('Tolak bukti pembayaran ini?')">Tolak</a>
                        <?php elseif ($data['status'] == 'dibayar'): ?>
                            <a href="?page=kelola_pemesanan&action=selesai&id=<?= $data['id']; ?>" class="btn btn-sm btn-primary" onclick="return confirm('Tandai sebagai selesai?')">Selesai</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="7" class="text-center text-muted">Tidak ada data pemesanan.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>