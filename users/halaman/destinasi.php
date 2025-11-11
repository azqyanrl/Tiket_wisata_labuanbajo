<?php
include '../../database/konek.php';
include "session_cek.php";
include '../../includes/navbar.php';
include '../../includes/boot.php';

// Ambil kata pencarian dari GET
 $cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';

if($cari != '') {
    $param = "%$cari%";
    $sql = "SELECT t.*, k.nama as kategori_nama, l.nama_lokasi 
            FROM tiket t 
            LEFT JOIN kategori k ON t.kategori_id = k.id 
            LEFT JOIN lokasi l ON t.lokasi = l.nama_lokasi
            WHERE t.status='aktif' AND 
            (t.nama_paket LIKE ? OR t.deskripsi LIKE ? OR t.itinerary LIKE ? OR t.fasilitas LIKE ? OR t.lokasi LIKE ?)
            ORDER BY t.created_at DESC";
    $stmt = $konek->prepare($sql);
    $stmt->bind_param("sssss", $param, $param, $param, $param, $param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $konek->query("SELECT t.*, k.nama as kategori_nama, l.nama_lokasi 
                            FROM tiket t 
                            LEFT JOIN kategori k ON t.kategori_id = k.id 
                            LEFT JOIN lokasi l ON t.lokasi = l.nama_lokasi
                            WHERE t.status='aktif' 
                            ORDER BY t.created_at DESC");
}
?>

<style>
    /* Destinations Section */
.destinations-section {
  padding: 80px 0;
}

.destination-card {
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
  height: 100%;
  border: none;
  position: relative;
}

.destination-img {
  height: 250px;
  object-fit: cover;
}

.destination-price {
  font-weight: 700;
  color: #1e40af; /* secondary color */
  font-size: 1.5rem;
}

.btn-destination {
  background: linear-gradient(135deg, #0ea5e9, #0284c7); /* primary gradient */
  border: none;
  border-radius: 30px;
  padding: 10px 25px;
  font-weight: 600;
}

.features-list small {
  display: block;
  margin-bottom: 3px;
}
</style>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
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

<div class="container my-5 destinations-section">
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
                // Ambil fasilitas dari database, maksimal 3 item
                $fasilitasArray = explode(',', $data['fasilitas']);
                $fasilitasToShow = array_slice($fasilitasArray, 0, 3);
        ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="destination-card">
                        <div class="position-relative overflow-hidden">
                            <img src="../../assets/images/tiket/<?= htmlspecialchars($data['gambar']); ?>" 
                                 class="destination-img w-100" 
                                 alt="<?= htmlspecialchars($data['nama_paket']); ?>">
                            <div class="position-absolute top-0 end-0 p-2">
                                <span class="badge bg-primary rounded-pill">
                                    <?= htmlspecialchars($data['kategori_nama']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <h5 class="fw-semibold mb-2"><?= htmlspecialchars($data['nama_paket']); ?></h5>
                            <p class="text-muted mb-2"><i class="fas fa-clock me-2"></i><?= htmlspecialchars($data['durasi']); ?></p>
                            <p class="text-muted mb-3"><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($data['lokasi']); ?></p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="destination-price">
                                    Rp <?= number_format($data['harga'], 0, ',', '.'); ?>
                                </div>
                                <div class="text-muted">
                                    <small><i class="fas fa-users me-1"></i><?= $data['stok']; ?> tersedia</small>
                                </div>
                            </div>
                            
                            <div class="features-list mb-3">
                                <?php foreach ($fasilitasToShow as $fasilitas) { ?>
                                    <small class="text-muted">
                                        <i class="fas fa-check text-success me-1"></i> <?= htmlspecialchars(trim($fasilitas)); ?>
                                    </small><br>
                                <?php } ?>
                            </div>
                            
                            <a href="detail_destinasi.php?id=<?= $data['id']; ?>" class="btn btn-destination w-100 text-light">
                                <i class="fas fa-shopping-cart me-2"></i>Pesan Sekarang
                            </a>
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