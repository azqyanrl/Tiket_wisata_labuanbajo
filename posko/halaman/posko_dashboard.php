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
 $id_admin = $_SESSION['id_user'] ?? 0; // Menggunakan id_user dari session login

// Proses aksi (confirm, reject, complete, cancel)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pemesanan_id']) && isset($_POST['action'])) {
    $pemesanan_id = intval($_POST['pemesanan_id']);
    $action = $_POST['action'];

    // Debug: Tampilkan informasi
    echo "<div class='alert alert-info'>Debug: Processing action '$action' for booking ID: $pemesanan_id</div>";

    if (!$id_admin) {
        $_SESSION['error_message'] = "Gagal mencatat verifikasi: admin tidak terdeteksi.";
        header("Location: index.php?page=verifikasi_tiket");
        exit();
    }

    switch ($action) {
        // VERIFIKASI PEMBAYARAN (ubah status ke 'dibayar')
        case 'confirm':
            // Pertama, cek apakah pemesanan ada dan statusnya 'pending'
            $cek_query = $konek->prepare("
                SELECT p.id, p.status 
                FROM pemesanan p
                JOIN tiket t ON p.tiket_id = t.id
                WHERE p.id = ? AND t.lokasi = ? AND p.status = 'pending'
            ");
            $cek_query->bind_param("is", $pemesanan_id, $lokasi_admin);
            $cek_query->execute();
            $cek_result = $cek_query->get_result();

            if ($cek_result->num_rows === 0) {
                $_SESSION['error_message'] = "Pemesanan tidak ditemukan atau status bukan 'Pending'.";
                header("Location: index.php?page=verifikasi_tiket");
                exit();
            }

            $query_pemesanan = $konek->prepare("UPDATE pemesanan SET status = 'dibayar' WHERE id = ? AND status = 'pending'");
            $query_pemesanan->bind_param("i", $pemesanan_id);
            $query_pemesanan->execute();

            if ($query_pemesanan->affected_rows > 0) {
                // Catat ke tabel verifikasi_history
                $catatan = 'Pembayaran diverifikasi oleh posko';
                $metode = 'Tunai';
                $status = 'dibayar';
                $insert_history = $konek->prepare("
                    INSERT INTO verifikasi_history (pemesanan_id, admin_id, metode_pembayaran, status, catatan, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $insert_history->bind_param("iisss", $pemesanan_id, $id_admin, $metode, $status, $catatan);
                $insert_history->execute();

                $_SESSION['success_message'] = "Pembayaran berhasil diverifikasi.";
            } else {
                $_SESSION['error_message'] = "Gagal memverifikasi. Pastikan status pesanan masih 'Pending'.";
            }
            break;

        // TOLAK PEMBAYARAN
        case 'reject':
            // Pertama, cek apakah pemesanan ada dan statusnya 'pending'
            $cek_query = $konek->prepare("
                SELECT p.id, p.status 
                FROM pemesanan p
                JOIN tiket t ON p.tiket_id = t.id
                WHERE p.id = ? AND t.lokasi = ? AND p.status = 'pending'
            ");
            $cek_query->bind_param("is", $pemesanan_id, $lokasi_admin);
            $cek_query->execute();
            $cek_result = $cek_query->get_result();

            if ($cek_result->num_rows === 0) {
                $_SESSION['error_message'] = "Pemesanan tidak ditemukan atau status bukan 'Pending'.";
                header("Location: index.php?page=verifikasi_tiket");
                exit();
            }

            $query_pemesanan = $konek->prepare("UPDATE pemesanan SET status = 'batal' WHERE id = ? AND status = 'pending'");
            $query_pemesanan->bind_param("i", $pemesanan_id);
            $query_pemesanan->execute();

            if ($query_pemesanan->affected_rows > 0) {
                // Catat ke verifikasi_history
                $catatan = 'Pembayaran ditolak oleh posko';
                $metode = 'manual';
                $status = 'batal';
                $insert_history = $konek->prepare("
                    INSERT INTO verifikasi_history (pemesanan_id, admin_id, metode_pembayaran, status, catatan, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $insert_history->bind_param("iisss", $pemesanan_id, $id_admin, $metode, $status, $catatan);
                $insert_history->execute();

                $_SESSION['success_message'] = "Pembayaran ditolak dan pesanan dibatalkan.";
            } else {
                $_SESSION['error_message'] = "Gagal menolak pembayaran. Pastikan status pesanan masih 'Pending'.";
            }
            break;

        // SELESAIKAN PEMESANAN (ubah dari 'dibayar' ke 'selesai')
        case 'complete':
            // Pertama, cek apakah pemesanan ada dan statusnya 'dibayar'
            $cek_query = $konek->prepare("
                SELECT p.id, p.status 
                FROM pemesanan p
                JOIN tiket t ON p.tiket_id = t.id
                WHERE p.id = ? AND t.lokasi = ? AND p.status = 'dibayar'
            ");
            $cek_query->bind_param("is", $pemesanan_id, $lokasi_admin);
            $cek_query->execute();
            $cek_result = $cek_query->get_result();

            if ($cek_result->num_rows === 0) {
                $_SESSION['error_message'] = "Pemesanan tidak ditemukan atau status bukan 'Dibayar'.";
                header("Location: index.php?page=verifikasi_tiket");
                exit();
            }

            $query = $konek->prepare("UPDATE pemesanan SET status = 'selesai' WHERE id = ? AND status = 'dibayar'");
            $query->bind_param("i", $pemesanan_id);
            $query->execute();

            if ($query->affected_rows > 0) {
                $catatan = 'Pesanan diselesaikan oleh posko';
                $metode = 'manual';
                $status = 'selesai';
                $insert_history = $konek->prepare("
                    INSERT INTO verifikasi_history (pemesanan_id, admin_id, metode_pembayaran, status, catatan, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $insert_history->bind_param("iisss", $pemesanan_id, $id_admin, $metode, $status, $catatan);
                $insert_history->execute();

                $_SESSION['success_message'] = "Pesanan berhasil diselesaikan.";
            } else {
                $_SESSION['error_message'] = "Gagal menyelesaikan pesanan. Pastikan status pesanan adalah 'Dibayar'.";
            }
            break;

        default:
            $_SESSION['error_message'] = "Aksi tidak valid.";
            break;
    }

    // Redirect kembali ke halaman verifikasi
    header("Location: index.php?page=verifikasi_tiket");
    exit();
}

// Tampilkan detail pemesanan jika ID ada
if (isset($_GET['id'])) {
    $pemesanan_id = $_GET['id'];
    
    // Ambil data pemesanan
    $query = $konek->prepare("
        SELECT p.*, u.nama_lengkap, u.email, u.no_hp, t.nama_paket, t.lokasi as nama_posko
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
        exit();
    }

    // Ambil riwayat verifikasi
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
    
    // Tampilkan detail pemesanan
    ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Detail Pemesanan</h1>
        <a href="?page=verifikasi_tiket" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Informasi Pemesanan -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Informasi Pemesanan</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Kode Booking:</strong> <?= htmlspecialchars($pemesanan['kode_booking']) ?></p>
                    <p><strong>Pelanggan:</strong> <?= htmlspecialchars($pemesanan['nama_lengkap']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($pemesanan['email']) ?></p>
                    <p><strong>No. HP:</strong> <?= htmlspecialchars($pemesanan['no_hp']) ?></p>
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
    </div>

    <!-- Aksi Verifikasi -->
    <?php if ($pemesanan['status'] === 'pending'): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5>Aksi Verifikasi</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-2">
                    <a href="?page=verifikasi_tiket&id=<?= $pemesanan['id'] ?>&action=confirm" 
                       class="btn btn-success w-100" onclick="return confirm('Konfirmasi pembayaran ini?')">
                        <i class="bi bi-check-circle"></i> Konfirmasi Pembayaran
                    </a>
                </div>
                <div class="col-md-6 mb-2">
                    <a href="?page=verifikasi_tiket&id=<?= $pemesanan['id'] ?>&action=reject" 
                       class="btn btn-danger w-100" onclick="return confirm('Tolak pembayaran ini?')">
                        <i class="bi bi-x-circle"></i> Tolak & Batalkan
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Riwayat Verifikasi -->
    <div class="card">
        <div class="card-header">
            <h5>Riwayat Verifikasi</h5>
        </div>
        <div class="card-body">
            <?php if ($history->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Admin</th>
                                <th>Posko Admin</th>
                                <th>Status</th>
                                <th>Metode</th>
                                <th>Catatan</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
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
                <p class="text-muted">Belum ada riwayat verifikasi</p>
            <?php endif; ?>
        </div>
    </div>
    <?php
    exit(); // Keluar dari script setelah menampilkan detail
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

// === QUERY STATISTIK ===
 $stats_sql = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN p.status = 'selesai' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN p.status = 'dibayar' THEN 1 ELSE 0 END) as verified,
        SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) as pending
    FROM pemesanan p
    JOIN tiket t ON p.tiket_id = t.id
    WHERE t.lokasi = ?
";
 $stats_stmt = $konek->prepare($stats_sql);
 $stats_stmt->bind_param('s', $lokasi_admin);
 $stats_stmt->execute();
 $stats_result = $stats_stmt->get_result();
 $stats = $stats_result->fetch_assoc();
?>

<!-- Tampilkan pesan sukses/error -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['success_message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error_message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<!-- Statistik -->
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
                <h5>Menunggu Verifikasi</h5>
                <h2><?= $stats['pending'] ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Pemesanan -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="m-0 fw-bold text-primary">Daftar Pemesanan Tiket</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Kode Booking</th>
                        <th>Pelanggan</th>
                        <th>Paket</th>
                        <th>Tanggal Kunjungan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Verifikasi / Pembatalan Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res->num_rows > 0): ?>
                        <?php while ($row = $res->fetch_assoc()): ?>
                            <?php
                            $badgeClass = match($row['status']) {
                                'pending' => 'bg-warning text-dark',
                                'dibayar' => 'bg-info text-dark',
                                'selesai' => 'bg-success',
                                'batal' => 'bg-danger',
                                default => 'bg-secondary'
                            };

                            if ($row['status'] === 'pending') {
                                $verifikasiOleh = '<span class="text-muted">Belum diverifikasi</span>';
                            } elseif (!empty($row['verifikator'])) {
                                $verifikasiOleh = htmlspecialchars($row['verifikator']);
                            } else {
                                $verifikasiOleh = '<span class="text-danger fw-bold">Admin Pusat</span>';
                            }
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['kode_booking']) ?></td>
                                <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                <td><?= htmlspecialchars($row['nama_paket']) ?></td>
                                <td><?= htmlspecialchars($row['tanggal_kunjungan']) ?></td>
                                <td><?= 'Rp ' . number_format($row['total_harga'], 0, ',', '.') ?></td>
                                <td><span class="badge <?= $badgeClass ?>"><?= ucfirst($row['status']) ?></span></td>
                                <td><?= $verifikasiOleh ?></td>
                                <td>
                                    <?php if ($row['status'] === 'pending'): ?>
                                        <!-- Tombol verifikasi yang mengarah ke halaman verifikasi -->
                                        <a href="?page=verifikasi_tiket&id=<?= $row['id'] ?>" class="btn btn-sm btn-primary me-1">
                                            <i class="bi bi-eye"></i> Verifikasi
                                        </a>
                                        
                                        <!-- Tombol batal -->
                                        <form method="POST" style="display:inline-block;">
                                            <input type="hidden" name="pemesanan_id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Yakin ingin batalkan tiket ini?')">
                                                <i class="bi bi-x-circle"></i> Batalkan
                                            </button>
                                        </form>
                                    <?php elseif ($row['status'] === 'dibayar'): ?>
                                        <!-- Tombol selesai langsung tanpa redirect -->
                                        <form method="POST" style="display:inline-block;">
                                            <input type="hidden" name="pemesanan_id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="action" value="complete">
                                            <button type="submit" class="btn btn-sm btn-success" 
                                                    onclick="return confirm('Yakin ingin menyelesaikan tiket ini?')">
                                                <i class="bi bi-check2-square"></i> Selesai
                                            </button>
                                        </form>
                                    <?php elseif ($row['status'] === 'selesai'): ?>
                                        <span class="badge bg-success">Selesai</span>
                                    <?php elseif ($row['status'] === 'batal'): ?>
                                        <span class="badge bg-danger">Dibatalkan</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted">Tidak ada data pemesanan</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>