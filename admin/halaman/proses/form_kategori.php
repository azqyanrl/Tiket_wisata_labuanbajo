<?php
// Ambil data kategori jika mode edit
$editing = isset($_GET['id']);
$kategori = null;

if ($editing) {
    // $konek sudah tersedia dari file kelola_kategori.php
    $query_kategori = $konek->prepare("SELECT * FROM kategori WHERE id = ?");
    $query_kategori->bind_param("i", $_GET['id']);
    $query_kategori->execute();
    $kategori = $query_kategori->get_result()->fetch_assoc();
}
?>

<!-- Modal untuk Tambah/Edit Kategori -->
<div class="modal fade" id="kategoriModal" tabindex="-1" aria-labelledby="kategoriModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="kategoriModalLabel"><?= $editing ? 'Edit' : 'Tambah' ?> Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- ✅ Path sudah diperbaiki -->
            <form method="POST" action="proses/handle_kategori.php">
                <div class="modal-body">
                    <?php if ($editing): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($kategori['id']) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Kategori</label>
                        <input type="text" name="nama" class="form-control" id="nama" 
                               value="<?= htmlspecialchars($kategori['nama'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" id="deskripsi" rows="3"><?= htmlspecialchars($kategori['deskripsi'] ?? '') ?></textarea>
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
    var myModal = new bootstrap.Modal(document.getElementById('kategoriModal'));
    myModal.show();
});
</script>
<?php endif; ?>
