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
?>

<!-- Modal untuk Tambah/Edit Tiket -->
<div class="modal fade" id="tiketModal" tabindex="-1" aria-labelledby="tiketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tiketModalLabel"><?= $editing ? 'Edit' : 'Tambah' ?> Tiket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="proses/handle_tiket.php" enctype="multipart/form-data">
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
                                        <label class="form-label">Nama Paket</label>
                                        <input type="text" name="nama_paket" class="form-control" 
                                               value="<?= htmlspecialchars($tiket['nama_paket'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Kategori</label>
                                        <select name="kategori_id" class="form-select" required>
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
                                        <label class="form-label">Lokasi</label>
                                        <select name="lokasi" class="form-select" required>
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
                                        <label class="form-label">Durasi</label>
                                        <input type="text" name="durasi" class="form-control" 
                                               value="<?= htmlspecialchars($tiket['durasi'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="aktif" <?= ($tiket['status'] ?? '') == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                            <option value="nonaktif" <?= ($tiket['status'] ?? '') == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="3" required><?= htmlspecialchars($tiket['deskripsi'] ?? '') ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Harga</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" name="harga" class="form-control" 
                                                   value="<?= htmlspecialchars($tiket['harga'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Stok Harian</label>
                                        <input type="number" name="stok" class="form-control" 
                                               value="<?= htmlspecialchars($tiket['stok'] ?? '0') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Stok Default</label>
                                        <input type="number" name="stok_default" class="form-control" 
                                               value="<?= htmlspecialchars($tiket['stok_default'] ?? $tiket['stok'] ?? '0') ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Gambar</label>
                                        <input type="file" name="gambar" class="form-control" accept="image/*" 
                                               <?= !$editing ? 'required' : '' ?>>
                                        <?php if ($editing && !empty($tiket['gambar'])): ?>
                                            <div class="mt-2">
                                                <p class="form-text">Gambar saat ini:</p>
                                                <img src="../../../assets/images/tiket/<?= htmlspecialchars($tiket['gambar']) ?>" 
                                                     width="100" alt="Current image">
                                            </div>
                                        <?php endif; ?>
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
                                <label class="form-label">Fasilitas</label>
                                <textarea name="fasilitas" class="form-control" rows="3"><?= htmlspecialchars($tiket['fasilitas'] ?? '') ?></textarea>
                                <div class="form-text">Pisahkan setiap fasilitas dengan koma (,)</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Itinerary</label>
                                <textarea name="itinerary" class="form-control" rows="5"><?= htmlspecialchars($tiket['itinerary'] ?? '') ?></textarea>
                                <div class="form-text">Jelaskan rencana perjalanan hari per hari</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Syarat & Ketentuan</label>
                                <textarea name="syarat" class="form-control" rows="3"><?= htmlspecialchars($tiket['syarat'] ?? '') ?></textarea>
                                <div class="form-text">Jelaskan syarat dan ketentuan yang berlaku</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
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