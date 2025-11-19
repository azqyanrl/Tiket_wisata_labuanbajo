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
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
<!-- Hero Section -->
<section class="text-center text-white bg-dark py-5" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('../../assets/images/bg/padar3.jpg'); background-size: cover; background-position: center;">
    <div class="container py-5">
        <h1 class="display-4 fw-bold mb-3">Jelajahi Keindahan Labuan Bajo</h1>
        <p class="lead mb-4">Temukan pengalaman tak terlupakan dengan paket wisata terbaik kami</p>
        <a href="destinasi.php" class="btn btn-primary btn-lg rounded-pill fw-semibold px-4">
            <i class="bi bi-ticket-detailed me-2"></i>Lihat Paket Tiket
        </a>
    </div>
</section>

<div class="container my-5">
    <?php
    if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

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
                                <div class="d-flex gap-2">
                                    <?php if (!empty($data_booking['gambar'])): ?>
                                        <img src="../../assets/images/tiket/<?= htmlspecialchars($data_booking['gambar']); ?>" alt="<?= htmlspecialchars($data_booking['nama_paket']); ?>" style="width: 60px; height: 45px; object-fit: cover; border-radius: 4px;">
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