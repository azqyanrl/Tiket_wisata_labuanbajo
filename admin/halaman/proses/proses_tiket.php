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
include '../../includes/boot.php';

// Ambil data tiket jika sedang dalam mode edit
$editing = isset($_GET['id']);
$tiket = null; // Inisialisasi $tiket untuk menghindari error
if ($editing) {
    $query_tiket = $konek->prepare("SELECT * FROM tiket WHERE id = ?");
    $query_tiket->bind_param("i", $_GET['id']);
    $query_tiket->execute();
    $tiket = $query_tiket->get_result()->fetch_assoc();
}
?>


<!-- Modal untuk Tambah/Edit Tiket -->
<div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= $editing ? 'Edit' : 'Tambah' ?> Tiket</h5>
                <a href="?page=kelola_tiket" class="btn-close"></a>
            </div>
            <form method="POST" action="proses/handle_tiket.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <?php if ($editing): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($_GET['id']) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Nama Paket</label>
                        <input type="text" name="nama_paket" class="form-control" value="<?= htmlspecialchars($tiket['nama_paket'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3" required><?= htmlspecialchars($tiket['deskripsi'] ?? '') ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Harga</label>
                                <input type="number" name="harga" class="form-control" value="<?= htmlspecialchars($tiket['harga'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Stok Harian</label>
                                <input type="number" name="stok" class="form-control" value="<?= htmlspecialchars($tiket['stok'] ?? '0') ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Durasi</label>
                                <input type="text" name="durasi" class="form-control" value="<?= htmlspecialchars($tiket['durasi'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Kategori</label>
                                <select name="kategori" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <option value="Trekking" <?= ($tiket['kategori'] ?? '') == 'Trekking' ? 'selected' : '' ?>>Trekking</option>
                                    <option value="Adventure" <?= ($tiket['kategori'] ?? '') == 'Adventure' ? 'selected' : '' ?>>Adventure</option>
                                    <option value="Snorkeling" <?= ($tiket['kategori'] ?? '') == 'Snorkeling' ? 'selected' : '' ?>>Snorkeling</option>
                                    <option value="Cultural" <?= ($tiket['kategori'] ?? '') == 'Cultural' ? 'selected' : '' ?>>Cultural</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="aktif" <?= ($tiket['status'] ?? '') == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= ($tiket['status'] ?? '') == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>

                    <!-- Field Tambahan -->
                    <div class="mb-3">
                        <label class="form-label">Fasilitas</label>
                        <textarea name="fasilitas" class="form-control" rows="2"><?= htmlspecialchars($tiket['fasilitas'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Itinerary</label>
                        <textarea name="itinerary" class="form-control" rows="3"><?= htmlspecialchars($tiket['itinerary'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Syarat & Ketentuan</label>
                        <textarea name="syarat" class="form-control" rows="2"><?= htmlspecialchars($tiket['syarat'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Gambar</label>
                        <input type="file" name="gambar" class="form-control" accept="image/*">
                        <?php if ($editing && !empty($tiket['gambar'])): ?>
                            <div class="mt-2">
                                <p class="form-text">Gambar saat ini:</p>
                                <img src="../../assets/images/tiket<?= htmlspecialchars($tiket['gambar']) ?>" width="100" alt="Current image">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="?page=kelola_tiket" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>