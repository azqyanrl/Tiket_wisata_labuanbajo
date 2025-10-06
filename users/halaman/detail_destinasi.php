<?php 
include '../../includes/boot.php'; 
include '../../database/konek.php';
include '../../includes/navbar.php'; 

// Ambil ID tiket dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data tiket berdasarkan ID
$tiket = $konek->query("SELECT * FROM tiket WHERE id = $id")->fetch_assoc();
if (!$tiket) {
    echo "<div class='container text-center py-5'><h3>Data tidak ditemukan</h3></div>";
    exit;
}
?>

<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; color: #333;">

<!-- Header Gambar -->
<div style="position: relative; height: 60vh; margin-top: 76px;">
    <img src="<?= $tiket['gambar'] ?>" 
         alt="<?= $tiket['nama_paket'] ?>" 
         style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.7)); display: flex; align-items: flex-end;">
        <div class="container pb-5">
            <h1 style="font-weight: 700; font-size: 3.5rem; color: white; margin-bottom: 10px;"><?= $tiket['nama_paket'] ?></h1>
            <p style="font-size: 1.2rem; color: rgba(255, 255, 255, 0.9); margin-bottom: 0;">
                <i class="bi bi-geo-alt-fill me-2"></i>Labuan Bajo, Nusa Tenggara Timur
            </p>
        </div>
    </div>
</div>

<!-- Konten -->
<div class="container" style="margin-top: -40px; margin-bottom: 80px;">
    <div class="row">
        <!-- Kolom Kiri -->
        <div class="col-lg-8">
            <div class="card shadow-lg" style="border-radius: 15px; border: none; margin-bottom: 30px;">
                <div class="card-body p-4">
                    <h3 style="font-weight: 700; margin-bottom: 20px;">Tentang Destinasi</h3>
                    <p style="line-height: 1.6; color: #495057;">
                        <?= nl2br($tiket['deskripsi']) ?>
                    </p>

                    <!-- Fasilitas -->
                    <h4 style="font-weight: 700; margin: 30px 0 20px;">Fasilitas</h4>
                    <ul class="list-group list-group-flush mb-4">
                        <?php 
                        $fasilitas = explode(',', $tiket['fasilitas']);
                        foreach ($fasilitas as $f) {
                            echo "<li class='list-group-item'><i class='bi bi-check-circle-fill text-success me-2'></i> $f</li>";
                        }
                        ?>
                    </ul>

                    <!-- Itinerary -->
                    <h4 style="font-weight: 700; margin: 30px 0 20px;">Rencana Perjalanan</h4>
                    <ul class="list-group list-group-flush mb-4">
                        <?php 
                        $itinerary = preg_split('/[;\n]/', $tiket['itinerary']);
                        foreach ($itinerary as $i) {
                            echo "<li class='list-group-item'><i class='bi bi-clock me-2 text-primary'></i> $i</li>";
                        }
                        ?>
                    </ul>

                    <!-- Syarat -->
                    <h4 style="font-weight: 700; margin: 30px 0 20px;">Syarat & Ketentuan</h4>
                    <ul class="list-group list-group-flush mb-4">
                        <?php 
                        $syarat = preg_split('/[;\n]/', $tiket['syarat']);
                        foreach ($syarat as $s) {
                            echo "<li class='list-group-item'><i class='bi bi-info-circle me-2 text-warning'></i> $s</li>";
                        }
                        ?>
                    </ul>

                    <!-- Lokasi -->
                    <?php if (!empty($tiket['latitude']) && !empty($tiket['longitude'])): ?>
                    <h4 style="font-weight: 700; margin: 30px 0 20px;">Lokasi</h4>
                    <div id="map" style="width: 100%; height: 400px; border-radius: 15px;"></div>
                    <?php else: ?>
                    <p class="text-muted fst-italic mt-4">Lokasi belum tersedia.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan -->
        <div class="col-lg-4">
            <div class="card shadow-lg" style="border-radius: 15px; border: none; position: sticky; top: 100px;">
                <div class="card-body p-4">
                    <h3 style="font-weight: 700; margin-bottom: 20px;">Informasi Tiket</h3>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span style="color: #6c757d;">Harga Tiket</span>
                            <span style="font-weight: 700; font-size: 1.2rem; color: #0d6efd;">Rp <?= number_format($tiket['harga'], 0, ',', '.') ?></span>
                        </div>
                        <small style="color: #6c757d;">Harga per orang</small>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span style="color: #6c757d;">Durasi</span>
                            <span style="font-weight: 600;"><?= $tiket['durasi'] ?></span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span style="color: #6c757d;">Kategori</span>
                            <span style="font-weight: 600;"><?= $tiket['kategori'] ?></span>
                        </div>
                    </div>

                    <hr>

                    <!-- Input jumlah tiket -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Jumlah Tiket</label>
                        <div class="input-group">
                            <button class="btn btn-outline-secondary" type="button" id="kurangTiket">-</button>
                            <input type="number" id="jumlahTiket" class="form-control text-center" value="1" min="1">
                            <button class="btn btn-outline-secondary" type="button" id="tambahTiket">+</button>
                        </div>
                    </div>

                    <!-- Total harga -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Total Harga</span>
                            <span id="totalHarga" class="fw-bold text-primary fs-5">Rp <?= number_format($tiket['harga'], 0, ',', '.') ?></span>
                        </div>
                    </div>

                    <button class="btn btn-primary w-100" 
                            style="background-color: #0d6efd; border: none; padding: 12px; font-weight: 600; border-radius: 8px;"
                            onclick="window.location.href='form.php?id=<?= $tiket['id'] ?>'">
                        Pesan Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<!-- LEAFLET.JS (PETA GRATIS) -->
<?php if (!empty($tiket['latitude']) && !empty($tiket['longitude'])): ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const lokasi = [<?= $tiket['latitude'] ?>, <?= $tiket['longitude'] ?>];
    const map = L.map('map').setView(lokasi, 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
    }).addTo(map);
    L.marker(lokasi).addTo(map)
      .bindPopup("<?= addslashes($tiket['nama_paket']) ?>")
      .openPopup();
  });
</script>
<?php endif; ?>

<!-- LOGIKA JUMLAH TIKET -->
<script>
document.addEventListener("DOMContentLoaded", function () {
  const hargaTiket = <?= $tiket['harga'] ?>;
  const qtyInput = document.getElementById("jumlahTiket");
  const totalEl = document.getElementById("totalHarga");

  function updateTotal() {
    const jumlah = parseInt(qtyInput.value) || 1;
    totalEl.textContent = "Rp " + (hargaTiket * jumlah).toLocaleString('id-ID');
  }

  document.getElementById("tambahTiket").addEventListener("click", function () {
    qtyInput.value = parseInt(qtyInput.value) + 1;
    updateTotal();
  });

  document.getElementById("kurangTiket").addEventListener("click", function () {
    if (parseInt(qtyInput.value) > 1) {
      qtyInput.value = parseInt(qtyInput.value) - 1;
      updateTotal();
    }
  });

  updateTotal();
});
</script>

</body>
</html>
