<?php
include '../../database/konek.php';
include "session_cek.php";
include '../../includes/navbar.php';
include '../../includes/boot.php';

// Ambil kata pencarian dari GET
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';

if($cari != '') {
    $param = "%$cari%";
    $sql = "SELECT * FROM tiket
            WHERE status='aktif' AND 
            (nama_paket LIKE ? OR deskripsi LIKE ? OR itinerary LIKE ? OR fasilitas LIKE ? OR lokasi LIKE ?)
            ORDER BY created_at DESC";
    $stmt = $konek->prepare($sql);
    $stmt->bind_param("sssss", $param, $param, $param, $param, $param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $konek->query("SELECT * FROM tiket WHERE status='aktif' ORDER BY created_at DESC");
}
?>


<!-- Hero Section -->
<section class="text-center text-white bg-dark py-5" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('../../assets/images/bg/kelor_island.jpg'); background-size: cover; background-position: center;">
    <div class="container py-5">
        <h1 class="display-4 fw-bold mb-3">Jelajahi Keindahan Labuan Bajo</h1>
        <p class="lead mb-4">Temukan pengalaman tak terlupakan dengan paket wisata terbaik kami</p>
        <a href="galeri.php" class="btn btn-primary btn-lg rounded-pill fw-semibold px-4">
            <i class="bi bi-ticket-detailed me-2"></i>Lihat Galeri
        </a>
    </div>
</section>

<div class="container my-5">
    <h3 class="text-center mb-4">Semua Destinasi Wisata</h3>

    <!-- Form Search -->
    <form class="d-flex justify-content-center mb-4" method="get" action="">
        <input type="text" name="cari" class="form-control w-50 me-2" placeholder="Cari destinasi..." value="<?= htmlspecialchars($cari); ?>">
        <button type="submit" class="btn btn-primary">Cari</button>
    </form>

    <div class="row">
        <?php
        if ($result && $result->num_rows > 0) {
            while ($data = $result->fetch_assoc()) {
        ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 ">
                        <img src="../../assets/images/tiket/<?= htmlspecialchars($data['gambar']); ?>" class="card-img-top" alt="<?= htmlspecialchars($data['nama_paket']); ?>"  style="height: 210px; width: 100%; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($data['nama_paket']); ?></h5>
                            <p class="text-muted small"><?= htmlspecialchars(substr($data['deskripsi'], 0, 100)); ?>...</p>
                            <p class="fw-bold text-primary mt-auto">Rp <?= number_format($data['harga'], 0, ',', '.'); ?></p>
                            <a href="detail_destinasi.php?id=<?= $data['id']; ?>" class="btn btn-sm btn-outline-primary mt-2">Lihat Detail</a>
                        </div>
                    </div>
                </div>
        <?php
            }
        } else {
            echo "<div class='col-12 text-center text-muted'>Tidak ada destinasi ditemukan.</div>";
        }
        ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>