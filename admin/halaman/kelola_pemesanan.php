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
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Kelola Pemesanan</h1>
</div>

<!-- Filter Berdasarkan Posko -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <input type="hidden" name="page" value="kelola_pemesanan">
                    <div class="row">
                        <div class="col-md-2">
                            <label for="posko_filter" class="form-label">Filter Posko</label>
                            <select class="form-select" id="posko_filter" name="posko_filter">
                                <option value="">Semua Posko</option>
                                <?php
                                $posko_list = $konek->query("SELECT DISTINCT lokasi FROM tiket ORDER BY lokasi");
                                while ($posko = $posko_list->fetch_assoc()):
                                ?>
                                <option value="<?= htmlspecialchars($posko['lokasi']) ?>" <?= (isset($_GET['posko_filter']) && $_GET['posko_filter'] == $posko['lokasi']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($posko['lokasi']) ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status_filter" class="form-label">Status</label>
                            <select class="form-select" id="status_filter" name="status_filter">
                                <option value="">Semua Status</option>
                                <option value="pending" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="dibayar" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] == 'dibayar') ? 'selected' : ''; ?>>Dibayar</option>
                                <option value="selesai" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] == 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                                <option value="batal" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] == 'batal') ? 'selected' : ''; ?>>Batal</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
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
                <th>Posko</th>
                <th>Jumlah</th>
                <th>Total</th>
                <th>Status</th>
                <th>Diverifikasi oleh</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT p.*, u.nama_lengkap, t.nama_paket, t.lokasi AS nama_posko,
                           vh.admin_id, u2.nama_lengkap AS admin_nama
                    FROM pemesanan p 
                    JOIN users u ON p.user_id = u.id 
                    JOIN tiket t ON p.tiket_id = t.id
                    LEFT JOIN verifikasi_history vh ON p.id = vh.pemesanan_id
                    LEFT JOIN users u2 ON vh.admin_id = u2.id";

            $conditions = [];
            $params = [];
            $types = '';

            if (!empty($_GET['posko_filter'])) {
                $conditions[] = "t.lokasi = ?";
                $params[] = $_GET['posko_filter'];
                $types .= 's';
            }

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
            ?>
                <tr>
                    <td><?= htmlspecialchars($data['kode_booking']) ?></td>
                    <td><?= htmlspecialchars($data['nama_lengkap']) ?></td>
                    <td><?= htmlspecialchars($data['nama_paket']) ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($data['nama_posko']) ?></span></td>
                    <td><?= (int)$data['jumlah_tiket'] ?></td>
                    <td>Rp <?= number_format($data['total_harga'], 0, ',', '.') ?></td>
                    <td><span class="badge <?= $statusClass ?>"><?= ucfirst(htmlspecialchars($data['status'])) ?></span></td>
                    <td>
                        <?php
                        if ($data['status'] === 'pending') {
                            echo "<span class='text-muted'>Belum diverifikasi</span>";
                        } elseif (!empty($data['admin_nama'])) {
                            echo htmlspecialchars($data['admin_nama']);
                        } else {
                            echo "<span class='text-primary'>Admin Pusat</span>";
                        }
                        ?>
                    </td>
                    <td><?= date('d/m/Y', strtotime($data['created_at'])) ?></td>
                    <td>
                        <a href='?page=detail_pemesanan&id=<?= htmlspecialchars($data['id']) ?>' class='btn btn-sm btn-info mb-1'>
                            <i class='bi bi-eye'></i> Detail
                        </a>

                        <?php if ($data['status'] === 'dibayar'): ?>
                            <a href='proses/proses_pemesanan.php?action=complete&id=<?= htmlspecialchars($data['id']) ?>'
                               class='btn btn-sm btn-success mb-1'
                               onclick="return confirm('Tandai pesanan ini sebagai selesai?');">
                               <i class='bi bi-check-circle'></i> Tandai Selesai
                            </a>
                        <?php elseif ($data['status'] === 'pending'): ?>
                            <a href='proses/proses_pemesanan.php?action=cancel&id=<?= htmlspecialchars($data['id']) ?>'
                               class='btn btn-sm btn-danger mb-1'
                               onclick="return confirm('Yakin ingin membatalkan pesanan ini?');">
                               <i class='bi bi-x-circle'></i> Batalkan
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php
                }
            } else {
                echo "<tr><td colspan='10' class='text-center'>Tidak ada data yang cocok dengan kriteria pencarian.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
