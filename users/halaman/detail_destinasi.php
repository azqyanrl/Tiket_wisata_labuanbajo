<?php
include '../../database/konek.php';
include '../../includes/stok_otomatis.php';
include '../../includes/navbar.php';
include '../../includes/boot.php';
include "session_cek.php";

 $paket_wisata_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($paket_wisata_id <= 0) {
    echo "<div class='container my-5'><div class='alert alert-danger'>ID destinasi tidak valid.</div></div>";
    include '../../includes/footer.php';
    exit;
}

// Query dengan JOIN untuk mendapatkan nama kategori, tipe trip, dan lokasi
 $query_paket = $konek->prepare("SELECT t.*, k.nama as nama_kategori, tt.nama as nama_tipe_trip, l.nama_lokasi 
                                FROM tiket t 
                                LEFT JOIN kategori k ON t.kategori_id = k.id 
                                LEFT JOIN tipe_trip tt ON t.tipe_trip_id = tt.id
                                LEFT JOIN lokasi l ON t.lokasi = l.nama_lokasi 
                                WHERE t.id = ?");
 $query_paket->bind_param("i", $paket_wisata_id);
 $query_paket->execute();
 $data_paket = $query_paket->get_result()->fetch_assoc();
 $query_paket->close();

if (!$data_paket) {
    echo "<div class='container my-5'><div class='alert alert-warning'>Data destinasi tidak ditemukan.</div></div>";
    include '../../includes/footer.php';
    exit;
}

 $stok_tersisa = getStokTersisa($konek, $data_paket['id']);
?>
<section class="hero-section" style="height: 60vh; position: relative; overflow: hidden;">
    <img src="../../assets/images/tiket/<?= htmlspecialchars($data_paket['gambar']); ?>"
         alt="<?= htmlspecialchars($data_paket['nama_paket']); ?>"
         style="width: 100%; height: 100%; object-fit: cover;">
    <div class="hero-overlay" style="position: absolute; inset: 0; background: rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center;">
        <h1 class="text-white fw-bold display-5"><?= htmlspecialchars($data_paket['nama_paket']); ?></h1>
    </div>
</section>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h3 class="fw-bold mb-0"><?= htmlspecialchars($data_paket['nama_paket']); ?></h3>
                </div>
                <div class="card-body">
                    <!-- BADGES INFORMASI UTAMA -->
                    <div class="mb-3">
                        <span class="badge bg-primary"><?= htmlspecialchars($data_paket['nama_kategori']); ?></span>
                        <span class="badge bg-info"><?= htmlspecialchars($data_paket['durasi']); ?></span>
                        <span class="badge <?= ($data_paket['status'] == 'aktif') ? 'bg-success' : 'bg-danger' ?>">
                            <?= ucfirst(htmlspecialchars($data_paket['status'])); ?>
                        </span>
                        
                        <!-- TAMBAHAN: BADGES TIPE TRIP, LOKASI, KAPASITAS -->
                        <?php if (!empty($data_paket['nama_tipe_trip'])): ?>
                        <span class="badge bg-secondary">
                            <i class="bi bi-people-fill me-1"></i><?= htmlspecialchars($data_paket['nama_tipe_trip']); ?>
                        </span>
                        <?php endif; ?>

                        <?php if (!empty($data_paket['nama_lokasi'])): ?>
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($data_paket['nama_lokasi']); ?>
                        </span>
                        <?php endif; ?>

                        <?php if (!empty($data_paket['kapasitas']) && $data_paket['kapasitas'] > 0): ?>
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-person-badge me-1"></i>Kapasitas: <?= htmlspecialchars($data_paket['kapasitas']); ?> Orang
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- TAMBAHAN: INFO JADWAL -->
                    <?php if (!empty($data_paket['jadwal'])): ?>
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="bi bi-calendar-event-fill me-2"></i>
                        <div>
                            <strong>Jadwal:</strong> <?= nl2br(htmlspecialchars($data_paket['jadwal'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <p class="lead"><?= nl2br(htmlspecialchars($data_paket['deskripsi'])); ?></p>
                    
                    <!-- PERBAIKAN: FASILITAS DENGAN CARD -->
                    <?php if (!empty($data_paket['fasilitas'])): ?>
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-star-fill text-warning me-2"></i>Fasilitas</h5>
                            <div class="row">
                                <?php 
                                $fasilitas = explode(',', $data_paket['fasilitas']);
                                foreach ($fasilitas as $fas): 
                                ?>
                                <div class="col-md-6 mb-2">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    <?= htmlspecialchars(trim($fas)); ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- PERBAIKAN: ITINERARY DENGAN CARD -->
                    <?php if (!empty($data_paket['itinerary'])): ?>
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-calendar-check text-primary me-2"></i>Itinerary</h5>
                            <div class="bg-light p-3 rounded">
                                <?= nl2br(htmlspecialchars($data_paket['itinerary'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- PERBAIKAN: SYARAT & KETENTUAN DENGAN CARD -->
                    <?php if (!empty($data_paket['syarat'])): ?>
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-info-circle text-info me-2"></i>Syarat & Ketentuan</h5>
                            <div class="bg-light p-3 rounded">
                                <?= nl2br(htmlspecialchars($data_paket['syarat'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h5 class="text-center mb-3">Form Booking</h5>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">Harga:</span>
                        <span class="text-primary fw-bold">Rp <?= number_format($data_paket['harga'], 0, ',', '.'); ?></span>
                    </div>

                    <p id="stok-info" class="<?= ($stok_tersisa > 0) ? 'text-success' : 'text-danger' ?> text-center">
                        <strong>
                            <?= ($stok_tersisa > 0) ? "Stok hari ini: " . intval($stok_tersisa) . " tiket" : "Tiket hari ini habis"; ?>
                        </strong>
                    </p>

                    <!-- Form Booking -->
                    <form action="handle_tiket.php" method="post" novalidate>
                        <input type="hidden" name="id_paket_wisata" value="<?= intval($data_paket['id']); ?>">
                        <input type="hidden" name="harga" value="<?= htmlspecialchars($data_paket['harga']); ?>">

                        <div class="mb-3">
                            <label for="tanggal_kunjungan_user" class="form-label">Tanggal Kunjungan</label>
                            <input type="date" name="tanggal_kunjungan_user" id="tanggal_kunjungan_user" class="form-control" required min="<?= date('Y-m-d'); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="jumlah_tiket_dipesan" class="form-label">Jumlah Tiket</label>
                            <input type="number" name="jumlah_tiket_dipesan" id="jumlah_tiket_dipesan" class="form-control" value="1" min="1" required>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Total Harga:</span>
                                <span id="total_harga_display" class="text-primary fw-bold">Rp <?= number_format($data_paket['harga'], 0, ',', '.'); ?></span>
                            </div>
                        </div>

                        <button type="submit" id="btn-booking" class="btn btn-primary w-100">Booking Sekarang</button>
                    </form>
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                            <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                        <div>
                            <strong>Catatan Penting:</strong> 
                            <ul class="mb-0 mt-2">
                                <li>jika lokasi wisata lebih dari 1, lakukan pembayaran di lokasi pertama wisata, seperti diitenerary</li>
                            </ul>
                        </div>
                    </div>
                    <small class="text-muted d-block text-center mt-2">Pembayaran dilakukan langsung ke admin (offline).</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    // aman-kan data PHP ke JS menggunakan json_encode
    const hargaDasar = <?= json_encode((float)$data_paket['harga']); ?>;
    const tiketId = <?= json_encode((int)$data_paket['id']); ?>;
    const inputJumlah = document.getElementById('jumlah_tiket_dipesan');
    const inputTanggal = document.getElementById('tanggal_kunjungan_user');
    const displayTotal = document.getElementById('total_harga_display');
    const stokInfo = document.getElementById('stok-info');
    const tombolBooking = document.getElementById('btn-booking');

    function updateTotal() {
        const jumlah = parseInt(inputJumlah.value, 10) || 1;
        const total = hargaDasar * jumlah;
        displayTotal.textContent = 'Rp ' + total.toLocaleString('id-ID');
    }

    async function cekStokTanggal() {
        const tanggal = inputTanggal.value;
        if (!tanggal) return;

        stokInfo.textContent = "Memeriksa stok...";
        stokInfo.classList.remove('text-danger', 'text-success');

        try {
            // pastikan path cek_stok.php sesuai lokasi file ini
            const resp = await fetch(`../../includes/cek_stok.php?tiket_id=${encodeURIComponent(tiketId)}&tanggal=${encodeURIComponent(tanggal)}`);
            if (!resp.ok) throw new Error('Network response not ok');
            const data = await resp.json();

            if (data.success) {
                stokInfo.textContent = `Stok tersedia: ${data.stok_tersisa} tiket`;
                stokInfo.classList.add('text-success');
                inputJumlah.max = data.stok_tersisa;
                inputJumlah.disabled = false;
                tombolBooking.disabled = false;
            } else {
                stokInfo.textContent = data.message || 'Tiket habis untuk tanggal tersebut.';
                stokInfo.classList.add('text-danger');
                inputJumlah.disabled = true;
                tombolBooking.disabled = true;
            }
        } catch (err) {
            console.error(err);
            stokInfo.textContent = 'Gagal memeriksa stok';
            stokInfo.classList.add('text-danger');
            inputJumlah.disabled = true;
            tombolBooking.disabled = true;
        }
    }

    // event listeners
    inputJumlah.addEventListener('input', updateTotal);
    inputTanggal.addEventListener('change', cekStokTanggal);

    // inisialisasi on load
    document.addEventListener('DOMContentLoaded', function () {
        updateTotal();
        if (inputTanggal.value) cekStokTanggal();
    });
})();
</script>

<?php include '../../includes/footer.php'; ?>