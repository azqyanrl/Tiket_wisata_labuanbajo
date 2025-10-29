<?php
 $search_result = null;
 $search_term = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $search_term = trim($_POST['search_term']);
    
    if (!empty($search_term)) {
        // Cari tiket berdasarkan kode booking atau nama pelanggan
        $sql = "SELECT p.*, t.nama_paket, t.lokasi, u.nama_lengkap 
                FROM pemesanan p 
                JOIN tiket t ON p.tiket_id = t.id 
                JOIN users u ON p.user_id = u.id
                WHERE (p.kode_booking LIKE ? OR u.nama_lengkap LIKE ?) AND t.lokasi = ?
                ORDER BY p.created_at DESC";
        $stmt = $konek->prepare($sql);
        $search_param = "%$search_term%";
        $stmt->bind_param('sss', $search_param, $search_param, $lokasi);
        $stmt->execute();
        $search_result = $stmt->get_result();
    }
}
?>

<div class="page-title">
    <h1>Cari Tiket</h1>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-primary">Pencarian Tiket</h6>
    </div>
    <div class="card-body">
        <form method="post">
            <div class="input-group mb-3">
                <input type="text" name="search_term" class="form-control" placeholder="Masukkan kode booking atau nama pelanggan" value="<?= htmlspecialchars($search_term) ?>" required>
                <button class="btn btn-primary" type="submit" name="search">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
        </form>

        <?php if ($search_result !== null): ?>
            <?php if ($search_result->num_rows > 0): ?>
                <div class="table-responsive mt-4">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Kode Booking</th>
                                <th>Pelanggan</th>
                                <th>Paket</th>
                                <th>Tanggal</th>
                                <th>Jumlah</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $search_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['kode_booking']) ?></td>
                                <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                <td><?= htmlspecialchars($row['nama_paket']) ?></td>
                                <td><?= htmlspecialchars($row['tanggal_kunjungan']) ?></td>
                                <td><?= htmlspecialchars($row['jumlah_tiket']) ?></td>
                                <td><?= 'Rp ' . number_format($row['total_harga'], 0, ',', '.') ?></td>
                                <td>
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
                                </td>
                                <td>
                                    <a href="index.php?page=verifikasi_tiket&kode=<?= urlencode($row['kode_booking']) ?>" class="btn btn-primary btn-sm">
                                        <i class="bi bi-check-circle"></i> Verifikasi
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-triangle"></i> Tidak ditemukan tiket dengan kata kunci "<strong><?= htmlspecialchars($search_term) ?></strong>"
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>