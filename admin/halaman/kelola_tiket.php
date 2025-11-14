<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Cek login admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Akses ditolak! Anda harus login sebagai admin.";
    header('location: ../login/login.php');
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';
include '../../includes/alerts.php';
include '../../includes/stok_otomatis.php';

// Cek jika ada parameter edit
$editing = isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']);
$tiket = null;

if ($editing) {
    $query_tiket = $konek->prepare("SELECT * FROM tiket WHERE id = ?");
    $query_tiket->bind_param("i", $_GET['id']);
    $query_tiket->execute();
    $tiket = $query_tiket->get_result()->fetch_assoc();
}

// Ambil data kategori untuk dropdown
$query_kategori = $konek->query("SELECT * FROM kategori ORDER BY nama ASC");

// Ambil data lokasi untuk dropdown
$query_lokasi = $konek->query("SELECT id, nama_lokasi FROM lokasi ORDER BY nama_lokasi ASC");

// Ambil data tipe trip untuk dropdown
$query_tipe = $konek->query("SELECT * FROM tipe_trip ORDER BY nama ASC");
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Kelola Paket Wisata</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tiketModal">
        <i class="bi bi-plus-circle me-1"></i> Tambah Paket Wisata
    </button>
</div>

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

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Gambar</th>
                <th>Nama Paket</th>
                <th>Kategori</th>
                <th>Lokasi</th>
                <th>Harga</th>
                <th>Stok Total</th>
                <th>Stok Tersisa (Hari Ini)</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $query_tiket = $konek->query("SELECT t.*, k.nama as nama_kategori FROM tiket t LEFT JOIN kategori k ON t.kategori_id = k.id ORDER BY t.created_at DESC"); 
            if ($query_tiket->num_rows > 0) { 
                while($data = $query_tiket->fetch_assoc()) { 
                    $stok_tersisa = getStokTersisa($konek, $data['id']);
                    echo "<tr>
                        <td><img src='../../assets/images/tiket/".htmlspecialchars($data['gambar'])."' width='60' class='rounded'></td>
                        <td>".htmlspecialchars($data['nama_paket'])."</td>
                        <td>".htmlspecialchars($data['nama_kategori'] ?? 'Tidak ada kategori')."</td>
                        <td>".htmlspecialchars($data['lokasi'])."</td>
                        <td>Rp " . number_format($data['harga'], 0, ',', '.') . "</td>
                        <td>" . htmlspecialchars($data['stok']) . "</td>
                        <td>" . htmlspecialchars($stok_tersisa) . "</td>
                        <td><span class='badge bg-" . (($data['status']=='aktif') ? 'success' : 'danger') . "'>" . ucfirst(htmlspecialchars($data['status'])) . "</span></td>
                        <td>
                            <button type='button' class='btn btn-sm btn-warning edit-tiket' data-id='" . htmlspecialchars($data['id']) . "'>
                                <i class='bi bi-pencil-square'></i> Edit
                            </button> 
                            <a href='proses/hapus_tiket.php?id=" . htmlspecialchars($data['id']) . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin ingin hapus?\")'>
                                <i class='bi bi-trash'></i> Hapus
                            </a>
                        </td>
                    </tr>"; 
                } 
            } else { 
                echo "<tr><td colspan='9' class='text-center'>Tidak ada data.</td></tr>"; 
            } 
            ?>
        </tbody>
    </table>
</div>

<!-- Modal Tambah/Edit Tiket -->
<div class="modal fade" id="tiketModal" tabindex="-1" aria-labelledby="tiketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tiketModalLabel">Tambah Paket Wisata</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="tiketForm" method="POST" action="proses/handle_tiket.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <!-- HIDDEN ID HARUS name="id" agar handle_tiket.php tahu ini EDIT -->
                    <input type="hidden" name="id" id="id">
                    
                    <!-- Informasi Dasar -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Informasi Dasar</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="nama_paket" class="form-label">Nama Paket</label>
                                        <input type="text" id="nama_paket" name="nama_paket" class="form-control" placeholder="Masukkan nama paket wisata" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="kategori_id" class="form-label">Kategori</label>
                                        <select id="kategori_id" name="kategori_id" class="form-select" required>
                                            <option value="">-- Pilih Kategori --</option>
                                            <?php 
                                            if ($query_kategori->num_rows > 0) {
                                                $query_kategori->data_seek(0);
                                                while($kat = $query_kategori->fetch_assoc()): ?>
                                                    <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama']) ?></option>
                                            <?php endwhile; } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="lokasi" class="form-label">Lokasi</label>
                                        <select id="lokasi" name="lokasi" class="form-select" required>
                                            <option value="">-- Pilih Lokasi --</option>
                                            <?php 
                                            if ($query_lokasi->num_rows > 0) {
                                                $query_lokasi->data_seek(0);
                                                while($lok = $query_lokasi->fetch_assoc()): ?>
                                                    <option value="<?= $lok['nama_lokasi'] ?>"><?= htmlspecialchars($lok['nama_lokasi']) ?></option>
                                            <?php endwhile; } else { ?>
                                                <option value="Labuan Bajo">Labuan Bajo</option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="tipe_trip" class="form-label">Tipe Trip</label>
                                        <select id="tipe_trip" name="tipe_trip" class="form-select">
                                            <option value="">-- Pilih Tipe Trip --</option>
                                            <?php 
                                            if ($query_tipe->num_rows > 0) {
                                                $query_tipe->data_seek(0);
                                                while($tipe = $query_tipe->fetch_assoc()): ?>
                                                    <option value="<?= $tipe['id'] ?>"><?= htmlspecialchars($tipe['nama']) ?></option>
                                            <?php endwhile; } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="durasi" class="form-label">Durasi</label>
                                        <input type="text" id="durasi" name="durasi" class="form-control" placeholder="Contoh: 3 Hari 2 Malam" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="deskripsi" class="form-label">Deskripsi</label>
                                        <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3" placeholder="Jelaskan deskripsi paket wisata" required></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select id="status" name="status" class="form-select">
                                            <option value="aktif">Aktif</option>
                                            <option value="nonaktif">Nonaktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Harga, Stok, Kapasitas -->
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="harga" class="form-label">Harga</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" id="harga" name="harga" class="form-control" placeholder="0" required min="0" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="stok" class="form-label">Stok Harian</label>
                                        <input type="number" id="stok" name="stok" class="form-control" placeholder="0" required min="0" oninput="if (this.value < 0) this.value = 0;">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="stok_default" class="form-label">Stok Default</label>
                                        <input type="number" id="stok_default" name="stok_default" class="form-control" placeholder="0" min="0" oninput="if (this.value < 0) this.value = 0;">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="kapasitas" class="form-label">Kapasitas</label>
                                        <input type="number" id="kapasitas" name="kapasitas" class="form-control" placeholder="0" min="0" oninput="if (this.value < 0) this.value = 0;">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="jadwal" class="form-label">Jadwal Keberangkatan</label>
                                        <input type="text" id="jadwal" name="jadwal" class="form-control" placeholder="Contoh: Setiap Sabtu, Minggu">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="gambar" class="form-label">Gambar</label>
                                        <input type="file" id="gambar" name="gambar" class="form-control" accept="image/*">
                                        <div id="imagePreview" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Detail -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Informasi Detail</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="fasilitas" class="form-label">Fasilitas</label>
                                <textarea id="fasilitas" name="fasilitas" class="form-control" rows="3" placeholder="Pisahkan setiap fasilitas dengan koma (,)"></textarea>
                                <div class="form-text">Pisahkan setiap fasilitas dengan koma (,)</div>
                            </div>
                            <div class="mb-3">
                                <label for="itinerary" class="form-label">Itinerary</label>
                                <textarea id="itinerary" name="itinerary" class="form-control" rows="5" placeholder="Jelaskan rencana perjalanan hari per hari"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="syarat" class="form-label">Syarat & Ketentuan</label>
                                <textarea id="syarat" name="syarat" class="form-control" rows="3" placeholder="Jelaskan syarat dan ketentuan yang berlaku"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripting: loadTiketData + modal handling -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Tombol "Tambah Paket Wisata" (di header) harus membuat input gambar required
    document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#tiketModal"]').forEach(btn => {
        btn.addEventListener('click', function() {
            // Reset form untuk mode tambah
            const form = document.getElementById('tiketForm');
            if (form) form.reset();

            // Pastikan hidden id kosong
            const hiddenId = document.getElementById('id');
            if (hiddenId) hiddenId.value = '';

            // gambar wajib untuk tambah
            const gambarEl = document.getElementById('gambar');
            if (gambarEl) gambarEl.setAttribute('required', 'required');

            // kosongkan preview
            const preview = document.getElementById('imagePreview');
            if (preview) preview.innerHTML = '';

            document.getElementById('tiketModalLabel').textContent = 'Tambah Paket Wisata';
        });
    });

    // Tombol Edit -> panggil loadTiketData
    document.querySelectorAll('.edit-tiket').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            loadTiketData(id);
        });
    });

    // Jika buka lewat URL ?action=edit&id=...
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'edit' && urlParams.get('id')) {
        loadTiketData(urlParams.get('id'));
    }

    // Saat modal ditutup reset & hapus required pada gambar
    const tiketModalEl = document.getElementById('tiketModal');
    if (tiketModalEl) {
        tiketModalEl.addEventListener('hidden.bs.modal', function () {
            const form = document.getElementById('tiketForm');
            if (form) form.reset();
            const preview = document.getElementById('imagePreview');
            if (preview) preview.innerHTML = '';
            const gambarEl = document.getElementById('gambar');
            if (gambarEl) gambarEl.removeAttribute('required');
            const hiddenId = document.getElementById('id');
            if (hiddenId) hiddenId.value = '';
            document.getElementById('tiketModalLabel').textContent = 'Tambah Paket Wisata';
        });
    }
});

// loadTiketData: fetch data tiket dan isi form (untuk Edit)
function loadTiketData(id) {

    console.log("LOAD DATA ID:", id);

    fetch("proses/get_tiket.php?id=" + encodeURIComponent(id), {
        method: "GET",
        credentials: "include"
    })
    .then(async res => {
        const raw = await res.text();
        console.log("RAW RESPONSE:", raw);

        try {
            return JSON.parse(raw);
        } catch (err) {
            console.error("JSON PARSE ERROR:", err);
            alert("Response dari server bukan JSON. Cek console (Network/Console).");
            throw err;
        }
    })
    .then(data => {

        console.log("DATA JSON:", data);

        if (data.error) {
            alert(data.error);
            return;
        }

        // Reset form
        const form = document.getElementById("tiketForm");
        if (form) form.reset();

        // Set hidden id (penting supaya server tahu EDIT)
        const hiddenId = document.getElementById('id');
        if (hiddenId) hiddenId.value = data.id || '';

        // Hapus required pada gambar saat edit
        const gambarEl = document.getElementById("gambar");
        if (gambarEl) gambarEl.removeAttribute('required');

        // Isi input sesuai data (kecuali input type=file)
        Object.keys(data).forEach(key => {
            const el = document.getElementById(key);
            if (!el) return;
            if (el.type === "file") return;
            el.value = data[key] !== null && data[key] !== undefined ? data[key] : "";
        });

        // Preview gambar lama
        const preview = document.getElementById("imagePreview");
        if (preview) {
            if (data.gambar) {
                preview.innerHTML = `
                    <p class="form-text">Gambar saat ini:</p>
                    <img src="../../assets/images/tiket/${data.gambar}" width="120" class="img-thumbnail">
                `;
            } else {
                preview.innerHTML = "";
            }
        }

        // Ubah judul modal dan tampilkan modal
        const label = document.getElementById("tiketModalLabel");
        if (label) label.textContent = "Edit Paket Wisata";

        new bootstrap.Modal(document.getElementById("tiketModal")).show();

    })
    .catch(err => {
        console.error("FETCH ERROR:", err);
        alert("Terjadi kesalahan saat memuat data tiket. Lihat console.");
    });
}
</script>
