<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../../database/konek.php';

$lokasi_admin = $_SESSION['lokasi'] ?? '';
$keyword = trim($_POST['kode_booking'] ?? '');

if ($keyword === '') {
    echo '<div class="text-center text-muted p-3">Masukkan kode booking untuk mencari.</div>';
    exit;
}

$query = $konek->prepare("
    SELECT 
        p.*, 
        t.nama_paket, 
        u.nama_lengkap
    FROM pemesanan p
    JOIN tiket t ON p.tiket_id = t.id
    JOIN users u ON p.user_id = u.id
    WHERE t.lokasi = ? 
      AND p.status = 'pending'
      AND (p.kode_booking LIKE CONCAT('%', ?, '%'))
    ORDER BY p.created_at DESC
");
$query->bind_param('ss', $lokasi_admin, $keyword);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo '<div class="text-center text-danger p-3">Kode booking tidak ditemukan atau tidak ada pesanan pending.</div>';
    exit;
}
?>

<table class="table table-striped table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th>Kode Booking</th>
            <th>Pelanggan</th>
            <th>Paket</th>
            <th>Tanggal Kunjungan</th>
            <th>Jumlah</th>
            <th>Total</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['kode_booking']) ?></td>
            <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
            <td><?= htmlspecialchars($row['nama_paket']) ?></td>
            <td><?= date('d/m/Y', strtotime($row['tanggal_kunjungan'])) ?></td>
            <td><?= (int)$row['jumlah_tiket'] ?></td>
            <td>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
            <td><span class="badge bg-warning text-dark">Pending</span></td>
            <td>
                <a href="index.php?page=verifikasi_tiket&id=<?= urlencode($row['id']) ?>" 
                   class="btn btn-sm btn-success">
                   <i class="bi bi-check-circle"></i> Verifikasi
                </a>

                <form method="POST" action="index.php?page=verifikasi_tiket" style="display:inline-block;" 
                      onsubmit="return confirm('Batalkan pesanan ini?')">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <input type="hidden" name="action" value="reject">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
