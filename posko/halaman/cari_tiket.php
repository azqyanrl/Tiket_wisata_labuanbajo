<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'posko') {
    $_SESSION['error_message'] = 'Akses ditolak!';
    header('Location: login/login.php');
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';
// Inisialisasi variabel
 $search_result = null;
 $search_term = '';

// Proses pencarian jika form dikirim
if (isset($_GET['search_term']) && !empty($_GET['search_term'])) {
    $search_term = trim($_GET['search_term']);
    $lokasi_admin = $_SESSION['lokasi']; // Ambil lokasi dari session

    // Cari tiket berdasarkan kode booking atau nama pelanggan, DAN filter berdasarkan lokasi admin
    $sql = "SELECT p.*, t.nama_paket, u.nama_lengkap 
            FROM pemesanan p 
            JOIN tiket t ON p.tiket_id = t.id 
            JOIN users u ON p.user_id = u.id
            WHERE (p.kode_booking LIKE ? OR u.nama_lengkap LIKE ?) AND t.lokasi = ?
            ORDER BY p.created_at DESC";
    
    $stmt = $konek->prepare($sql);
    $search_param = "%$search_term%";
    $stmt->bind_param('sss', $search_param, $search_param, $lokasi_admin);
    $stmt->execute();
    $search_result = $stmt->get_result();
}
?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-search me-2"></i>Pencarian & Verifikasi Tiket
        </h6>
    </div>
    <div class="card-body">
        <!-- Form Pencarian -->
        <form method="GET" action="">
            <input type="hidden" name="page" value="cari_tiket">
            <div class="input-group mb-3">
                <input type="text" name="search_term" class="form-control" placeholder="Masukkan kode booking atau nama pelanggan" value="<?= htmlspecialchars($search_term) ?>" required>
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-search"></i> Cari
                </button>
                <a href="?page=cari_tiket" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
        </form>

        <!-- Hasil Pencarian -->
        <div id="searchResults">
            <?php if ($search_result !== null): ?>
                <?php if ($search_result->num_rows > 0): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle me-2"></i>Ditemukan <strong><?= $search_result->num_rows ?></strong> data untuk kata kunci "<strong><?= htmlspecialchars($search_term) ?></strong>"
                    </div>
                    <div class="table-responsive mt-4">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode Booking</th>
                                    <th>Pelanggan</th>
                                    <th>Paket</th>
                                    <th>Tanggal Kunjungan</th>
                                    <th>Jumlah</th>
                                    <th>Total Harga</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $search_result->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['kode_booking']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_paket']) ?></td>
                                    <td><?= htmlspecialchars($row['tanggal_kunjungan']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['jumlah_tiket']) ?></td>
                                    <td class="text-end"><?= 'Rp ' . number_format($row['total_harga'], 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <?php 
                                        $badgeClass = '';
                                        switch($row['status']) {
                                            case 'pending': $badgeClass = 'bg-warning text-dark'; break;
                                            case 'dibayar': $badgeClass = 'bg-success'; break;
                                            case 'selesai': $badgeClass = 'bg-info'; break;
                                            case 'batal': $badgeClass = 'bg-danger'; break;
                                        }
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= ucfirst($row['status']) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="?page=verifikasi_tiket&kode=<?= urlencode($row['kode_booking']) ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="bi bi-check-circle"></i> Verifikasi
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mt-3" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>Tidak ditemukan tiket dengan kata kunci "<strong><?= htmlspecialchars($search_term) ?></strong>"
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-search fs-1"></i>
                    <p>Masukkan kode booking atau nama pelanggan untuk memulai pencarian.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>