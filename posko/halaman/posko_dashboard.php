<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Cek login admin posko
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'posko') {
    $_SESSION['error_message'] = 'Akses ditolak!';
    header('Location: login/login.php');
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';

$lokasi_admin = $_SESSION['lokasi'];
$id_admin = $_SESSION['id_user'] ?? 0;

// ========================================
// =========== PROSES AKSI VERIFIKASI =====
// ========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pemesanan_id'], $_POST['action'])) {
    $pemesanan_id = intval($_POST['pemesanan_id']);
    $action = $_POST['action'];

    // Ambil status saat ini
    $cek_status = $konek->prepare("SELECT status FROM pemesanan WHERE id = ?");
    $cek_status->bind_param("i", $pemesanan_id);
    $cek_status->execute();
    $result = $cek_status->get_result()->fetch_assoc();

    if (!$result) {
        $_SESSION['error_message'] = "Pemesanan tidak ditemukan.";
        header("Location: index.php?page=verifikasi_tiket");
        exit;
    }

    $current_status = $result['status'];

    // Cegah aksi tidak valid
    if ($action === 'confirm' && $current_status !== 'pending') {
        $_SESSION['error_message'] = "Pesanan ini sudah diverifikasi.";
        header("Location: index.php?page=verifikasi_tiket");
        exit;
    }

    if ($action === 'reject' && $current_status !== 'pending') {
        $_SESSION['error_message'] = "Pesanan ini tidak bisa dibatalkan lagi.";
        header("Location: index.php?page=verifikasi_tiket");
        exit;
    }

    if ($action === 'complete' && $current_status !== 'dibayar') {
        $_SESSION['error_message'] = "Pesanan ini belum dibayar atau sudah selesai.";
        header("Location: index.php?page=verifikasi_tiket");
        exit;
    }

    // Tentukan status baru
    switch ($action) {
        case 'confirm':
            $status_baru = 'dibayar';
            $catatan = 'Pembayaran diverifikasi oleh posko';
            $metode = 'Tunai';
            break;
        case 'reject':
            $status_baru = 'batal';
            $catatan = 'Pembayaran ditolak oleh posko';
            $metode = 'Manual';
            break;
        case 'complete':
            $status_baru = 'selesai';
            $catatan = 'Pesanan diselesaikan oleh posko';
            $metode = 'Manual';
            break;
        default:
            $_SESSION['error_message'] = "Aksi tidak valid.";
            header("Location: index.php?page=verifikasi_tiket");
            exit;
    }

    // Update status pemesanan
    $update = $konek->prepare("UPDATE pemesanan SET status = ? WHERE id = ?");
    $update->bind_param("si", $status_baru, $pemesanan_id);
    $update->execute();

    if ($update->affected_rows > 0) {
        // Catat ke history
        $insert = $konek->prepare("
            INSERT INTO verifikasi_history (pemesanan_id, admin_id, metode_pembayaran, status, catatan, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $insert->bind_param("iisss", $pemesanan_id, $id_admin, $metode, $status_baru, $catatan);
        $insert->execute();

        $_SESSION['success_message'] = "Aksi berhasil dilakukan.";
    } else {
        $_SESSION['error_message'] = "Tidak ada perubahan dilakukan.";
    }

    header("Location: index.php?page=verifikasi_tiket");
    exit;
}

// ========================================
// =========== DETAIL PEMESANAN ===========
// ========================================
if (isset($_GET['id'])) {
    $pemesanan_id = $_GET['id'];
    $query = $konek->prepare("
        SELECT p.*, u.nama_lengkap, u.email, u.no_hp, 
               t.nama_paket, t.lokasi as nama_posko
        FROM pemesanan p
        JOIN users u ON p.user_id = u.id
        JOIN tiket t ON p.tiket_id = t.id
        WHERE p.id = ? AND t.lokasi = ?
    ");
    $query->bind_param("is", $pemesanan_id, $lokasi_admin);
    $query->execute();
    $pemesanan = $query->get_result()->fetch_assoc();

    if (!$pemesanan) {
        echo '<div class="alert alert-danger">Pemesanan tidak ditemukan atau bukan wilayah Anda.</div>';
        echo '<a href="?page=verifikasi_tiket" class="btn btn-secondary">Kembali</a>';
        exit;
    }

    $history_query = $konek->prepare("
        SELECT vh.*, u.nama_lengkap as admin_nama, u.lokasi as admin_posko
        FROM verifikasi_history vh
        JOIN users u ON vh.admin_id = u.id
        WHERE vh.pemesanan_id = ?
        ORDER BY vh.created_at DESC
    ");
    $history_query->bind_param("i", $pemesanan_id);
    $history_query->execute();
    $history = $history_query->get_result();
?>
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Detail Pemesanan</h1>
        <a href="?page=verifikasi_tiket" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h5>Informasi Pemesanan</h5></div>
        <div class="card-body row">
            <div class="col-md-6">
                <p><strong>Kode Booking:</strong> <?= htmlspecialchars($pemesanan['kode_booking']) ?></p>
                <p><strong>Pelanggan:</strong> <?= htmlspecialchars($pemesanan['nama_lengkap']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($pemesanan['email']) ?></p>
                <p><strong>No HP:</strong> <?= htmlspecialchars($pemesanan['no_hp']) ?></p>
                <p><strong>Paket:</strong> <?= htmlspecialchars($pemesanan['nama_paket']) ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Posko:</strong> <span class="badge bg-secondary"><?= htmlspecialchars($pemesanan['nama_posko']) ?></span></p>
                <p><strong>Jumlah Tiket:</strong> <?= $pemesanan['jumlah_tiket'] ?></p>
                <p><strong>Total Harga:</strong> Rp <?= number_format($pemesanan['total_harga'], 0, ',', '.') ?></p>
                <p><strong>Status:</strong> <span class="badge bg-info"><?= ucfirst($pemesanan['status']) ?></span></p>
                <p><strong>Tanggal Kunjungan:</strong> <?= date('d/m/Y', strtotime($pemesanan['tanggal_kunjungan'])) ?></p>
            </div>
        </div>
    </div>

    <?php if ($pemesanan['status'] === 'pending'): ?>
    <div class="card mb-4">
        <div class="card-header"><h5>Aksi Verifikasi</h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-2">
                    <form method="POST">
                        <input type="hidden" name="pemesanan_id" value="<?= $pemesanan['id'] ?>">
                        <input type="hidden" name="action" value="confirm">
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Konfirmasi pembayaran ini?')">
                            <i class="bi bi-check-circle"></i> Konfirmasi Pembayaran
                        </button>
                    </form>
                </div>
                <div class="col-md-6 mb-2">
                    <form method="POST">
                        <input type="hidden" name="pemesanan_id" value="<?= $pemesanan['id'] ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Tolak pembayaran ini?')">
                            <i class="bi bi-x-circle"></i> Tolak & Batalkan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><h5>Riwayat Verifikasi</h5></div>
        <div class="card-body">
            <?php if ($history->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Admin</th><th>Posko</th><th>Status</th><th>Metode</th><th>Catatan</th><th>Waktu</th></tr></thead>
                        <tbody>
                            <?php while($row = $history->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['admin_nama']) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($row['admin_posko']) ?></span></td>
                                <td><span class="badge bg-info"><?= ucfirst($row['status']) ?></span></td>
                                <td><?= htmlspecialchars($row['metode_pembayaran']) ?></td>
                                <td><?= htmlspecialchars($row['catatan']) ?></td>
                                <td><?= date('d/m/Y H:i:s', strtotime($row['created_at'])) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">Belum ada riwayat verifikasi.</p>
            <?php endif; ?>
        </div>
    </div>

<?php
exit;
}



$stats_sql = "
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN p.status='selesai' THEN 1 ELSE 0 END) AS completed,
        SUM(CASE WHEN p.status='dibayar' THEN 1 ELSE 0 END) AS verified,
        SUM(CASE WHEN p.status='pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN p.status='batal' THEN 1 ELSE 0 END) AS batal
    FROM pemesanan p
    JOIN tiket t ON p.tiket_id = t.id
    WHERE t.lokasi = ?
";


$stats_stmt = $konek->prepare($stats_sql);
$stats_stmt->bind_param("s", $lokasi_admin);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Query data utama
$sql = "
    SELECT p.*, t.nama_paket, u.nama_lengkap,
           vh.admin_id, ua.nama_lengkap AS verifikator
    FROM pemesanan p
    JOIN tiket t ON p.tiket_id = t.id
    JOIN users u ON p.user_id = u.id
    LEFT JOIN verifikasi_history vh ON vh.pemesanan_id=p.id 
        AND vh.id=(SELECT MAX(id) FROM verifikasi_history WHERE pemesanan_id=p.id)
    LEFT JOIN users ua ON vh.admin_id=ua.id
    WHERE t.lokasi = ?
    ORDER BY p.created_at DESC
";
$stmt = $konek->prepare($sql);
$stmt->bind_param('s', $lokasi_admin);
$stmt->execute();
$res = $stmt->get_result();
?>

<div class="row g-4 mb-4">

    <div class="col-md-3">
        <div class="card text-white bg-primary shadow-sm">
            <div class="card-body">
                <h5>Total Tiket</h5>
                <h2><?= $stats['total'] ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-white bg-success shadow-sm">
            <div class="card-body">
                <h5>Selesai</h5>
                <h2><?= $stats['completed'] ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-white bg-info shadow-sm">
            <div class="card-body">
                <h5>Terverifikasi</h5>
                <h2><?= $stats['verified'] ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-white bg-warning shadow-sm">
            <div class="card-body">
                <h5>Menunggu</h5>
                <h2><?= $stats['pending'] ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-white bg-danger shadow-sm">
            <div class="card-body">
                <h5>Batal</h5>
                <h2><?= $stats['batal'] ?></h2>
            </div>
        </div>
    </div>

</div>


<div class="card shadow-sm">
    <div class="card-header bg-white fw-bold text-primary">Daftar Pemesanan Tiket</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Pelanggan</th>
                        <th>Paket</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Verifikator</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>

<?php if ($res->num_rows > 0): while ($row = $res->fetch_assoc()): ?>

<?php
$badgeClass = match($row['status']) {
    'pending' => 'bg-warning text-dark',
    'dibayar' => 'bg-info text-dark',
    'selesai' => 'bg-success',
    'batal' => 'bg-danger',
    default => 'bg-secondary'
};
?>
                    <tr>
                        <td><?= htmlspecialchars($row['kode_booking']) ?></td>
                        <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($row['nama_paket']) ?></td>
                        <td><?= htmlspecialchars($row['tanggal_kunjungan']) ?></td>
                        <td>Rp <?= number_format($row['total_harga'],0,',','.') ?></td>
                        <td><span class="badge <?= $badgeClass ?>"><?= ucfirst($row['status']) ?></span></td>
                        <td><?= $row['verifikator'] ? htmlspecialchars($row['verifikator']) : '<span class="text-muted">-</span>' ?></td>

                        <td>
                            <?php if ($row['status'] === 'pending'): ?>
                                <a href="?page=verifikasi_tiket&id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Verifikasi</a>

                            <?php elseif ($row['status'] === 'dibayar'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="pemesanan_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="action" value="complete">
                                    <button class="btn btn-sm btn-success"
                                            onclick="return confirm('Tandai pesanan ini selesai?')">Selesai</button>
                                </form>

                            <?php elseif ($row['status'] === 'selesai'): ?>
                                <a href="struk.php?id=<?= $row['id'] ?>"
                                   class="btn btn-sm btn-warning" target="_blank">
                                    Cetak Struk
                                </a>

                            <?php else: ?>
                                <span class="badge bg-danger">Dibatalkan</span>
                            <?php endif; ?>
                        </td>
                    </tr>

<?php endwhile; else: ?>

                    <tr>
                        <td colspan="8" class="text-center text-muted">Tidak ada data</td>
                    </tr>

<?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>
