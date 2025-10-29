<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'posko') {
    header('Location: login/login.php'); exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';
$lokasi = $_SESSION['lokasi'] ?? '';

 $sql = "SELECT p.*, t.nama_paket, t.lokasi, u.nama_lengkap
        FROM pemesanan p
        JOIN tiket t ON p.tiket_id = t.id
        JOIN users u ON p.user_id = u.id
        WHERE t.lokasi = ?
        ORDER BY p.created_at DESC";
 $stmt = $konek->prepare($sql);
 $stmt->bind_param('s', $lokasi);
 $stmt->execute();
 $res = $stmt->get_result();
?>

<div class="page-title">
    <h1>Dashboard Posko</h1>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total Tiket</h5>
                        <h3><?= $res->num_rows ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-ticket-perforated fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Terverifikasi</h5>
                        <h3>
                            <?php 
                            $stmt = $konek->prepare("SELECT COUNT(*) FROM pemesanan p JOIN tiket t ON p.tiket_id = t.id WHERE t.lokasi = ? AND p.status = 'dibayar'");
                            $stmt->bind_param('s', $lokasi);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $row = $result->fetch_row();
                            echo $row[0];
                            ?>
                        </h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-check-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-warning mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Menunggu</h5>
                        <h3>
                            <?php 
                            $stmt = $konek->prepare("SELECT COUNT(*) FROM pemesanan p JOIN tiket t ON p.tiket_id = t.id WHERE t.lokasi = ? AND p.status = 'pending'");
                            $stmt->bind_param('s', $lokasi);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $row = $result->fetch_row();
                            echo $row[0];
                            ?>
                        </h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-hourglass-split fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Pemesanan Tiket</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
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
                    <?php while($row = $res->fetch_assoc()): ?>
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
    </div>
</div>