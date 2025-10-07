<?php
include '../../database/konek.php';
include "session_cek.php";
include '../../includes/navbar.php';
include '../../includes/boot.php';

// Validasi user login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location='../login/login.php';</script>";
    exit;
}

 $id_pengguna = intval($_SESSION['user_id']);

// Ambil data riwayat booking user
 $query_riwayat = $konek->prepare("
    SELECT p.kode_booking, p.tanggal_kunjungan, p.jumlah_tiket, p.total_harga, p.status, p.created_at,
           t.nama_paket, t.gambar
    FROM pemesanan p
    JOIN tiket t ON p.tiket_id = t.id
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
");
 $query_riwayat->bind_param("i", $id_pengguna);
 $query_riwayat->execute();
 $hasil_riwayat = $query_riwayat->get_result();
?>

<!-- Hero Section -->
<section class="text-center text-white bg-dark py-5" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1558979158-65a1eaa08691?auto=format&fit=crop&w=1350&q=80'); background-size: cover; background-position: center;">
    <div class="container py-5">
        <h1 class="display-4 fw-bold mb-3">Jelajahi Keindahan Labuan Bajo</h1>
        <p class="lead mb-4">Temukan pengalaman tak terlupakan dengan paket wisata terbaik kami</p>
        <a href="destinasi.php" class="btn btn-primary btn-lg rounded-pill fw-semibold px-4">
            <i class="bi bi-ticket-detailed me-2"></i>Lihat Paket Tiket
        </a>
    </div>
</section>

<div class="container my-5">
    <h3 class="text-center mb-4 fw-bold">Riwayat Booking Anda</h3>

    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
            <thead class="table-primary">
                <tr>
                    <th>Kode Booking</th>
                    <th>Paket Wisata</th>
                    <th>Tanggal Kunjungan</th>
                    <th>Jumlah Tiket</th>
                    <th>Total Harga</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($hasil_riwayat->num_rows > 0): ?>
                    <?php while ($data_booking = $hasil_riwayat->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($data_booking['kode_booking']); ?></td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <?php if (!empty($data_booking['gambar'])): ?>
                                        <img src="../../assets/images/<?= htmlspecialchars($data_booking['gambar']); ?>" alt="<?= htmlspecialchars($data_booking['nama_paket']); ?>" style="width: 60px; height: 45px; object-fit: cover; border-radius: 4px;">
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($data_booking['nama_paket']); ?></span>
                                </div>
                            </td>
                            <td><?= date('d-m-Y', strtotime($data_booking['tanggal_kunjungan'])); ?></td>
                            <td><?= intval($data_booking['jumlah_tiket']); ?></td>
                            <td>Rp <?= number_format($data_booking['total_harga'], 0, ',', '.'); ?></td>
                            <td>
                                <?php
                                $statusClass = '';
                                switch ($data_booking['status']) {
                                    case 'pending':
                                        $statusClass = 'bg-warning text-dark';
                                        break;
                                    case 'dibayar':
                                        $statusClass = 'bg-info text-white';
                                        break;
                                    case 'selesai':
                                        $statusClass = 'bg-success text-white';
                                        break;
                                    case 'batal':
                                        $statusClass = 'bg-danger text-white';
                                        break;
                                }
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= ucfirst(htmlspecialchars($data_booking['status'])); ?></span>
                            </td>
                            <td>
                                <a href="cetak_booking.php?kode_booking=<?= urlencode($data_booking['kode_booking']); ?>" class="btn btn-sm btn-success" target="_blank">Cetak</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-muted">Belum ada data booking.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>