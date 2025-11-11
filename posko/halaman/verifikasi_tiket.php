<?php   
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login admin posko
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'posko') {
    $_SESSION['error_message'] = 'Akses ditolak!';
    header('Location: login/login.php');
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';

$lokasi_admin = $_SESSION['lokasi'];
$id_admin = $_SESSION['id_user'] ?? 0; // dari session login

// === PROSES AKSI (verifikasi / tolak / selesai) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pemesanan_id'], $_POST['action'])) {
    $pemesanan_id = intval($_POST['pemesanan_id']);
    $action = $_POST['action'];

    // Cek apakah pemesanan valid dan milik posko ini
    $cek = $konek->prepare("
        SELECT p.id, p.status 
        FROM pemesanan p
        JOIN tiket t ON p.tiket_id = t.id
        WHERE p.id = ? AND t.lokasi = ?
    ");
    $cek->bind_param("is", $pemesanan_id, $lokasi_admin);
    $cek->execute();
    $data = $cek->get_result()->fetch_assoc();

    if (!$data) {
        $_SESSION['error_message'] = "Pemesanan tidak ditemukan atau bukan wilayah Anda.";
        header("Location: index.php?page=verifikasi_tiket");
        exit;
    }

    $status_sekarang = $data['status'];

    // === CEGAH VERIFIKASI ULANG ===
    if ($status_sekarang !== 'pending' && $action === 'confirm') {
        $_SESSION['error_message'] = "Pesanan ini sudah diverifikasi dan tidak bisa diverifikasi ulang.";
        header("Location: index.php?page=verifikasi_tiket");
        exit;
    }

    switch ($action) {
        case 'confirm':
            $new_status = 'dibayar';
            $catatan = 'Pembayaran diverifikasi oleh posko';
            $metode = 'Tunai';
            break;

        case 'reject':
            if ($status_sekarang !== 'pending') {
                $_SESSION['error_message'] = "Pesanan tidak dapat dibatalkan karena sudah diverifikasi.";
                header("Location: index.php?page=verifikasi_tiket");
                exit;
            }
            $new_status = 'batal';
            $catatan = 'Pembayaran ditolak oleh posko';
            $metode = 'Manual';
            break;

        case 'complete':
            if ($status_sekarang !== 'dibayar') {
                $_SESSION['error_message'] = "Pesanan belum diverifikasi atau sudah selesai.";
                header("Location: index.php?page=verifikasi_tiket");
                exit;
            }
            $new_status = 'selesai';
            $catatan = 'Pesanan diselesaikan oleh posko';
            $metode = 'Manual';
            break;

        default:
            $_SESSION['error_message'] = "Aksi tidak valid.";
            header("Location: index.php?page=verifikasi_tiket");
            exit;
    }

    // Update status
    $update = $konek->prepare("UPDATE pemesanan SET status = ?, updated_at = NOW() WHERE id = ?");
    $update->bind_param("si", $new_status, $pemesanan_id);
    $update->execute();

    if ($update->affected_rows > 0) {
        // Catat ke history
        $his = $konek->prepare("
            INSERT INTO verifikasi_history (pemesanan_id, admin_id, metode_pembayaran, status, catatan, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $his->bind_param("iisss", $pemesanan_id, $id_admin, $metode, $new_status, $catatan);
        $his->execute();

        $_SESSION['success_message'] = "Status pesanan berhasil diubah menjadi '$new_status'.";
    } else {
        $_SESSION['error_message'] = "Tidak ada perubahan status.";
    }

    header("Location: index.php?page=verifikasi_tiket");
    exit;
}

// === QUERY UTAMA ===
$sql = "
    SELECT 
        p.*, 
        t.nama_paket, 
        u.nama_lengkap,
        vh.admin_id,
        ua.nama_lengkap AS verifikator,
        ua.role AS role_verifikator
    FROM pemesanan p
    JOIN tiket t ON p.tiket_id = t.id
    JOIN users u ON p.user_id = u.id
    LEFT JOIN verifikasi_history vh ON vh.pemesanan_id = p.id
        AND vh.id = (SELECT MAX(id) FROM verifikasi_history WHERE pemesanan_id = p.id)
    LEFT JOIN users ua ON vh.admin_id = ua.id
    WHERE t.lokasi = ?
    ORDER BY p.created_at DESC
";
$stmt = $konek->prepare($sql);
$stmt->bind_param('s', $lokasi_admin);
$stmt->execute();
$res = $stmt->get_result();

// === STATISTIK ===
$stats_sql = "
    SELECT 
        COUNT(*) as total,
        SUM(p.status = 'selesai') as completed,
        SUM(p.status = 'dibayar') as verified,
        SUM(p.status = 'pending') as pending
    FROM pemesanan p
    JOIN tiket t ON p.tiket_id = t.id
    WHERE t.lokasi = ?
";
$st = $konek->prepare($stats_sql);
$st->bind_param('s', $lokasi_admin);
$st->execute();
$stats = $st->get_result()->fetch_assoc();
?>

<!-- Alert pesan -->
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<!-- Statistik -->
<div class="row g-4 mb-4">
    <div class="col-md-3"><div class="card bg-primary text-white p-3"><h6>Total Tiket</h6><h3><?= $stats['total'] ?></h3></div></div>
    <div class="col-md-3"><div class="card bg-warning text-dark p-3"><h6>Pending</h6><h3><?= $stats['pending'] ?></h3></div></div>
    <div class="col-md-3"><div class="card bg-info text-dark p-3"><h6>Dibayar</h6><h3><?= $stats['verified'] ?></h3></div></div>
    <div class="col-md-3"><div class="card bg-success text-white p-3"><h6>Selesai</h6><h3><?= $stats['completed'] ?></h3></div></div>
</div>

<!-- Tabel Pemesanan -->
<div class="card shadow-sm">
    <div class="card-header bg-white fw-bold text-primary">Daftar Pemesanan Tiket</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Kode Booking</th>
                        <th>Pelanggan</th>
                        <th>Paket</th>
                        <th>Tanggal Kunjungan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Verifikator</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($r = $res->fetch_assoc()): ?>
                    <?php
                    $badgeClass = match($r['status']) {
                        'pending' => 'bg-warning text-dark',
                        'dibayar' => 'bg-info text-dark',
                        'selesai' => 'bg-success',
                        'batal' => 'bg-danger',
                        default => 'bg-secondary'
                    };
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($r['kode_booking']) ?></td>
                        <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($r['nama_paket']) ?></td>
                        <td><?= htmlspecialchars($r['tanggal_kunjungan']) ?></td>
                        <td>Rp <?= number_format($r['total_harga'], 0, ',', '.') ?></td>
                        <td><span class="badge <?= $badgeClass ?>"><?= ucfirst($r['status']) ?></span></td>
                        <td><?= $r['verifikator'] ?: '<span class="text-muted">Belum ada</span>' ?></td>
                        <td>
                            <?php if ($r['status'] === 'pending'): ?>
                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="pemesanan_id" value="<?= $r['id'] ?>">
                                    <input type="hidden" name="action" value="confirm">
                                    <button class="btn btn-sm btn-success" onclick="return confirm('Verifikasi pembayaran?')">Verifikasi</button>
                                </form>
                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="pemesanan_id" value="<?= $r['id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Batalkan pemesanan ini?')">Batal</button>
                                </form>
                            <?php elseif ($r['status'] === 'dibayar'): ?>
                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="pemesanan_id" value="<?= $r['id'] ?>">
                                    <input type="hidden" name="action" value="complete">
                                    <button class="btn btn-sm btn-primary" onclick="return confirm('Tandai sebagai selesai?')">Selesai</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">Tidak ada aksi</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
