<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Akses ditolak! Anda harus login sebagai admin.";
    header('location: ../../login/login_admin.php');
    exit;
}

include '../../database/konek.php';

// Ambil data tiket jika sedang dalam mode edit
 $editing = isset($_GET['id']);
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

<!-- Modal untuk Tambah/Edit Tiket -->
<div class="modal fade" id="tiketModal" tabindex="-1" aria-labelledby="tiketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tiketModalLabel"><?= $editing ? 'Edit' : 'Tambah' ?> Tiket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="tiketForm" method="POST" action="proses/handle_tiket.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <?php if ($editing): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($_GET['id']) ?>">
                    <?php endif; ?>

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
                                        <input type="text" id="nama_paket" name="nama_paket" class="form-control" 
                                               placeholder="Masukkan nama paket wisata"
                                               value="<?= htmlspecialchars($tiket['nama_paket'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="kategori_id" class="form-label">Kategori</label>
                                        <select id="kategori_id" name="kategori_id" class="form-select" required>
                                            <option value="">-- Pilih Kategori --</option>
                                            <?php 
                                            // Reset pointer kategori
                                            if ($query_kategori->num_rows > 0) {
                                                $query_kategori->data_seek(0);
                                                while($kat = $query_kategori->fetch_assoc()): 
                                            ?>
                                                <option value="<?= $kat['id'] ?>" 
                                                        <?= ($tiket['kategori_id'] ?? '') == $kat['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($kat['nama']) ?>
                                                </option>
                                            <?php 
                                                endwhile; 
                                            }
                                            ?>
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
                                            // Reset pointer lokasi
                                            if ($query_lokasi->num_rows > 0) {
                                                $query_lokasi->data_seek(0);
                                                while($lok = $query_lokasi->fetch_assoc()): 
                                            ?>
                                                <option value="<?= $lok['nama_lokasi'] ?>" 
                                                        <?= ($tiket['lokasi'] ?? '') == $lok['nama_lokasi'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($lok['nama_lokasi']) ?>
                                                </option>
                                            <?php 
                                                endwhile; 
                                            } else {
                                                // Jika tabel lokasi kosong, gunakan nilai default
                                            ?>
                                                <option value="Labuan Bajo" 
                                                        <?= ($tiket['lokasi'] ?? '') == 'Labuan Bajo' ? 'selected' : '' ?>>
                                                    Labuan Bajo
                                                </option>
                                            <?php
                                            }
                                            ?>
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
                                                while($tipe = $query_tipe->fetch_assoc()): 
                                            ?>
                                                <option value="<?= $tipe['id'] ?>" 
                                                        <?= ($tiket['tipe_trip_id'] ?? '') == $tipe['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($tipe['nama']) ?>
                                                </option>
                                            <?php 
                                                endwhile; 
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="durasi" class="form-label">Durasi</label>
                                        <input type="text" id="durasi" name="durasi" class="form-control" 
                                               placeholder="Contoh: 3 Hari 2 Malam"
                                               value="<?= htmlspecialchars($tiket['durasi'] ?? '') ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="deskripsi" class="form-label">Deskripsi</label>
                                        <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3" 
                                                  placeholder="Jelaskan deskripsi paket wisata"
                                                  required><?= htmlspecialchars($tiket['deskripsi'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select id="status" name="status" class="form-select">
                                            <option value="aktif" <?= ($tiket['status'] ?? '') == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                            <option value="nonaktif" <?= ($tiket['status'] ?? '') == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="harga" class="form-label">Harga</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" id="harga" name="harga" class="form-control" 
                                                   placeholder="0"
                                                   value="<?= htmlspecialchars($tiket['harga'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="stok" class="form-label">Stok Harian</label>
                                        <input type="number" id="stok" name="stok" class="form-control" 
                                               placeholder="0"
                                               value="<?= htmlspecialchars($tiket['stok'] ?? '0') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="stok_default" class="form-label">Stok Default</label>
                                        <input type="number" id="stok_default" name="stok_default" class="form-control" 
                                               placeholder="0"
                                               value="<?= htmlspecialchars($tiket['stok_default'] ?? $tiket['stok'] ?? '0') ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="kapasitas" class="form-label">Kapasitas</label>
                                        <input type="number" id="kapasitas" name="kapasitas" class="form-control" 
                                               placeholder="0"
                                               value="<?= htmlspecialchars($tiket['kapasitas'] ?? '0') ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="jadwal" class="form-label">Jadwal Keberangkatan</label>
                                        <input type="text" id="jadwal" name="jadwal" class="form-control" 
                                               placeholder="Contoh: Setiap Sabtu, Minggu"
                                               value="<?= htmlspecialchars($tiket['jadwal'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="gambar" class="form-label">Gambar</label>
                                        <input type="file" id="gambar" name="gambar" class="form-control" accept="image/*" 
                                               <?= !$editing ? 'required' : '' ?>>
                                        <div id="imagePreview" class="mt-2">
                                            <?php if ($editing && !empty($tiket['gambar'])): ?>
                                                <p class="form-text">Gambar saat ini:</p>
                                                <img src="../../../assets/images/tiket/<?= htmlspecialchars($tiket['gambar']) ?>" 
                                                     width="100" alt="Current image" class="img-thumbnail">
                                            <?php endif; ?>
                                        </div>
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
                                <textarea id="fasilitas" name="fasilitas" class="form-control" rows="3" 
                                          placeholder="Pisahkan setiap fasilitas dengan koma (,)"><?= htmlspecialchars($tiket['fasilitas'] ?? '') ?></textarea>
                                <div class="form-text">Pisahkan setiap fasilitas dengan koma (,)</div>
                            </div>

                            <div class="mb-3">
                                <label for="itinerary" class="form-label">Itinerary</label>
                                <textarea id="itinerary" name="itinerary" class="form-control" rows="5" 
                                          placeholder="Jelaskan rencana perjalanan hari per hari"><?= htmlspecialchars($tiket['itinerary'] ?? '') ?></textarea>
                                <div class="form-text">Jelaskan rencana perjalanan hari per hari</div>
                            </div>

                            <div class="mb-3">
                                <label for="syarat" class="form-label">Syarat & Ketentuan</label>
                                <textarea id="syarat" name="syarat" class="form-control" rows="3" 
                                          placeholder="Jelaskan syarat dan ketentuan yang berlaku"><?= htmlspecialchars($tiket['syarat'] ?? '') ?></textarea>
                                <div class="form-text">Jelaskan syarat dan ketentuan yang berlaku</div>
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

<?php if ($editing): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var myModal = new bootstrap.Modal(document.getElementById('tiketModal'));
    myModal.show();
});
</script>
<?php endif; ?>

<script>
// Preview gambar sebelum upload
document.getElementById('gambar').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = `
                <p class="form-text">Preview gambar:</p>
                <img src="${e.target.result}" width="100" alt="Preview" class="img-thumbnail">
            `;
        }
        reader.readAsDataURL(file);
    }
});

// Validasi form sebelum submit
document.getElementById('tiketForm').addEventListener('submit', function(e) {
    const namaPaket = document.getElementById('nama_paket').value.trim();
    const kategori = document.getElementById('kategori_id').value;
    const lokasi = document.getElementById('lokasi').value;
    const durasi = document.getElementById('durasi').value.trim();
    const deskripsi = document.getElementById('deskripsi').value.trim();
    const harga = document.getElementById('harga').value;
    const stok = document.getElementById('stok').value;
    
    if (!namaPaket || !kategori || !lokasi || !durasi || !deskripsi || !harga || !stok) {
        e.preventDefault();
        alert('Mohon lengkapi semua field yang wajib diisi!');
        return false;
    }
    
    return true;
});
</script>