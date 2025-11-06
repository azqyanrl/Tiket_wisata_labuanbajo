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

$lokasi_admin = $_SESSION['lokasi'];
$kode_booking = trim($_GET['kode_booking'] ?? '');
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Verifikasi Pemesanan Posko <?= htmlspecialchars($lokasi_admin) ?></h1>
</div>

<!-- 🔍 Form Pencarian -->
<div class="card mb-4">
    <div class="card-header">
        <h5>Cari Pemesanan Berdasarkan Kode Booking</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="">
            <input type="hidden" name="page" value="cari_tiket">
            <div class="input-group">
                <input type="text" name="kode_booking" class="form-control" 
                    placeholder="Masukkan kode booking..." 
                    value="<?= htmlspecialchars($kode_booking) ?>" required>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Cari
                </button>
                <a href="?page=cari_tiket" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- 📋 Hasil Pencarian -->
<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Kode Booking</th>
                <th>Pelanggan</th>
                <th>Paket</th>
                <th>Tanggal</th>
                <th>Jumlah</th>
                <th>Total</th>
                <th>Status</th>
                <th>Diverifikasi oleh</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($kode_booking)) {
                $stmt = $konek->prepare("
                    SELECT p.*, u.nama_lengkap, t.nama_paket, t.lokasi 
                    FROM pemesanan p
                    JOIN users u ON p.user_id = u.id
                    JOIN tiket t ON p.tiket_id = t.id
                    WHERE p.kode_booking = ? AND t.lokasi = ?
                    ORDER BY p.created_at DESC
                ");
                $stmt->bind_param("ss", $kode_booking, $lokasi_admin);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0):
                    while ($data = $result->fetch_assoc()):
                        $status = $data['status'];
                        $statusClass = match ($status) {
                            'pending' => 'bg-warning text-dark',
                            'dibayar' => 'bg-info text-dark',
                            'selesai' => 'bg-success',
                            'batal'   => 'bg-danger',
                            default   => 'bg-secondary'
                        };

                        // Ambil info verifikasi terakhir
                        $verifikasi_stmt = $konek->prepare("
                            SELECT u.nama_lengkap, vh.status, vh.created_at 
                            FROM verifikasi_history vh
                            JOIN users u ON vh.admin_id = u.id
                            WHERE vh.pemesanan_id = ?
                            ORDER BY vh.created_at DESC LIMIT 1
                        ");
                        $verifikasi_stmt->bind_param("i", $data['id']);
                        $verifikasi_stmt->execute();
                        $verif = $verifikasi_stmt->get_result()->fetch_assoc();
            ?>
            <tr>
                <td><?= htmlspecialchars($data['kode_booking']) ?></td>
                <td><?= htmlspecialchars($data['nama_lengkap']) ?></td>
                <td><?= htmlspecialchars($data['nama_paket']) ?></td>
                <td><?= date('d/m/Y', strtotime($data['tanggal_kunjungan'])) ?></td>
                <td><?= (int)$data['jumlah_tiket'] ?></td>
                <td>Rp <?= number_format($data['total_harga'], 0, ',', '.') ?></td>
                <td><span class="badge <?= $statusClass ?>"><?= ucfirst($status) ?></span></td>
                <td>
                    <?php 
                    if ($status === 'pending') {
                        echo "<span class='text-muted'>Belum diverifikasi</span>";
                    } elseif ($verif) {
                        echo htmlspecialchars($verif['nama_lengkap']) . " <br><small>(" . ucfirst($verif['status']) . ")</small>";
                    } else {
                        echo "<span class='text-primary'>Admin Pusat</span>";
                    }
                    ?>
                </td>
                <td>
                    <?php if ($status === 'pending'): ?>
                        <a href="?page=verifikasi_tiket&kode=<?= urlencode($data['kode_booking']) ?>" 
                           class="btn btn-sm btn-primary">
                           <i class="bi bi-check-circle"></i> Verifikasi
                        </a>
                        <a href="proses/proses_pemesanan.php?action=cancel&id=<?= $data['id'] ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Batalkan pesanan ini?')">
                           <i class="bi bi-x-circle"></i> Batalkan
                        </a>
                    <?php elseif ($status === 'dibayar'): ?>
                        <a href="proses/proses_pemesanan.php?action=complete&id=<?= $data['id'] ?>"
                           class="btn btn-sm btn-success"
                           onclick="return confirm('Tandai pesanan ini sebagai selesai?')">
                           <i class="bi bi-check-circle"></i> Tandai Selesai
                        </a>
                    <?php else: ?>
                        <span class="text-muted">Tidak ada aksi</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php
                    endwhile;
                else:
                    echo "<tr><td colspan='9' class='text-center text-muted'>Kode booking tidak ditemukan atau tidak dalam status pending.</td></tr>";
                endif;
            } else {
                echo "<tr><td colspan='9' class='text-center text-muted'>Masukkan kode booking untuk memulai pencarian.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
