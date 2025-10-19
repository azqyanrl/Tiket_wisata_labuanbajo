<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak!'); document.location.href='../login/login.php';</script>";
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';

// Tampilkan pesan sukses jika ada
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['success_message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    unset($_SESSION['success_message']);
}

// Tampilkan pesan error jika ada
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['error_message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    unset($_SESSION['error_message']);
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Kelola Pemesanan</h1>
</div>

<!-- Form Pencarian -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <input type="hidden" name="page" value="kelola_pemesanan">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                            <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal"
                                value="<?php echo isset($_GET['tanggal_awal']) ? htmlspecialchars($_GET['tanggal_awal']) : ''; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir"
                                value="<?php echo isset($_GET['tanggal_akhir']) ? htmlspecialchars($_GET['tanggal_akhir']) : ''; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="status_filter" class="form-label">Status</label>
                            <select class="form-select" id="status_filter" name="status_filter">
                                <option value="">Semua Status</option>
                                <option value="pending" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="dibayar" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'dibayar') ? 'selected' : ''; ?>>Dibayar</option>
                                <option value="selesai" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                                <option value="batal" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'batal') ? 'selected' : ''; ?>>Batal</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Cari</button>
                            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=kelola_pemesanan" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Kode Booking</th>
                <th>User</th>
                <th>Tiket</th>
                <th>Jumlah Tiket</th>
                <th>Tanggal Kunjungan</th>
                <th>Total</th>
                <th>Metode Pembayaran</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Query dasar
            $sql = "SELECT p.*, u.nama_lengkap, t.nama_paket 
                    FROM pemesanan p 
                    JOIN users u ON p.user_id = u.id 
                    JOIN tiket t ON p.tiket_id = t.id";

            $conditions = [];
            $params = [];
            $types = '';

            // Filter tanggal awal
            if (!empty($_GET['tanggal_awal'])) {
                $conditions[] = "p.tanggal_kunjungan >= ?";
                $params[] = $_GET['tanggal_awal'];
                $types .= 's';
            }

            // Filter tanggal akhir
            if (!empty($_GET['tanggal_akhir'])) {
                $conditions[] = "p.tanggal_kunjungan <= ?";
                $params[] = $_GET['tanggal_akhir'];
                $types .= 's';
            }

            // Filter status
            if (!empty($_GET['status_filter'])) {
                $conditions[] = "p.status = ?";
                $params[] = $_GET['status_filter'];
                $types .= 's';
            }

            if (count($conditions) > 0) {
                $sql .= " WHERE " . implode(' AND ', $conditions);
            }

            $sql .= " ORDER BY p.created_at DESC";

            $stmt = $konek->prepare($sql);
            if ($stmt) {
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $result = false;
            }

            if ($result && $result->num_rows > 0) {
                while ($data = $result->fetch_assoc()) {
                    $statusClass = match ($data['status']) {
                        'pending' => 'bg-warning text-dark',
                        'dibayar' => 'bg-info text-dark',
                        'selesai' => 'bg-success',
                        'batal'   => 'bg-danger',
                        default   => 'bg-secondary'
                    };
                    echo "<tr>
                        <td>" . htmlspecialchars($data['kode_booking']) . "</td>
                        <td>" . htmlspecialchars($data['nama_lengkap']) . "</td>
                        <td>" . htmlspecialchars($data['nama_paket']) . "</td>
                        <td>" . (int)$data['jumlah_tiket'] . "</td>
                        <td>" . date('d/m/Y', strtotime($data['tanggal_kunjungan'])) . "</td>
                        <td>Rp " . number_format($data['total_harga'], 0, ',', '.') . "</td>
                        <td>" . htmlspecialchars($data['metode_pembayaran']) . "</td>
                        <td><span class='badge $statusClass'>" . ucfirst(htmlspecialchars($data['status'])) . "</span></td>
                        <td>";

                    // Tombol Aksi berdasarkan status
                    if ($data['status'] == 'pending') {
                        echo "<a href='proses/proses_pemesanan.php?id=" . htmlspecialchars($data['id']) . "&action=confirm' class='btn btn-sm btn-success'>Konfirmasi</a> ";
                        echo "<a href='proses/proses_pemesanan.php?id=" . htmlspecialchars($data['id']) . "&action=reject' class='btn btn-sm btn-danger'>Tolak</a>";
                    } elseif ($data['status'] == 'dibayar') {
                        echo "<a href='proses/proses_pemesanan.php?id=" . htmlspecialchars($data['id']) . "&action=complete' class='btn btn-sm btn-success'>Selesaikan</a> ";
                        echo "<a href='proses/proses_pemesanan.php?id=" . htmlspecialchars($data['id']) . "&action=cancel' class='btn btn-sm btn-danger' onclick='return confirm(\"Apakah Anda yakin ingin membatalkan pesanan ini?\")'>Batalkan</a>";
                    } elseif ($data['status'] == 'selesai') {
                        echo "<span class='text-muted'>Selesai</span>";
                    } else {
                        echo "<span class='text-muted'>Dibatalkan</span>";
                    }

                    echo "</td></tr>";
                }
            } else {
                echo "<tr><td colspan='9' class='text-center'>Tidak ada data yang cocok dengan kriteria pencarian.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
