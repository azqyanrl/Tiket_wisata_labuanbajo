<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Akses ditolak! Anda harus login sebagai admin.";
    header('location: ../login/login.php');
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';
?>

<div class="container-fluid mt-3">

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
        <h1 class="h4 fw-bold">Kelola Galeri</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahFoto">
            <i class="bi bi-plus-circle me-1"></i> Tambah Foto
        </button>
    </div>

    <?php 
    // Ambil data galeri dan kategori
    $query_galeri = $konek->query("
        SELECT g.*, k.nama AS kategori_nama 
        FROM galleries g
        LEFT JOIN kategori k ON g.kategori_id = k.id
        ORDER BY g.created_at DESC
    "); 

    // Ambil daftar kategori untuk dropdown
    $kategori_result = $konek->query("SELECT id, nama FROM kategori ORDER BY nama ASC");
    $kategori_options = $kategori_result ? $kategori_result->fetch_all(MYSQLI_ASSOC) : [];
    ?>

    <div class="row g-3">
        <?php if ($query_galeri && $query_galeri->num_rows > 0): ?>
            <?php while($data = $query_galeri->fetch_assoc()): ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="card shadow-sm border-0">
                        <img src='../../assets/images/galery/<?= htmlspecialchars($data['gambar'] ?? ''); ?>' 
                             class='card-img-top' 
                             style='height:200px;object-fit:cover;' 
                             alt='<?= htmlspecialchars($data['judul'] ?? ''); ?>'>
                        <div class='card-body'>
                            <h6 class='card-title mb-1 fw-semibold'><?= htmlspecialchars($data['judul'] ?? ''); ?></h6>
                            <small class='text-muted d-block mb-2'>
                                Kategori: <?= htmlspecialchars($data['kategori_nama'] ?? 'Tanpa Kategori'); ?>
                            </small>
                            <div class="d-flex justify-content-between">
                                <a href='proses/hapus_galeri.php?id=<?= htmlspecialchars($data['id']); ?>' 
                                   class='btn btn-sm btn-danger'
                                   onclick='return confirm("Yakin ingin menghapus foto ini?")'>
                                   <i class="bi bi-trash"></i> Hapus
                                </a>
                                <button class='btn btn-sm btn-warning' 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalEditFoto<?= $data['id']; ?>">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class='col-12 text-center text-muted'>Tidak ada foto.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Tambah Foto -->
<div class="modal fade" id="modalTambahFoto" tabindex="-1" aria-labelledby="modalTambahFotoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalTambahFotoLabel">Tambah Foto Baru</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="proses/proses_galeri.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
            <div class="mb-3">
                <label for="judul" class="form-label fw-semibold">Judul</label>
                <input type="text" class="form-control" name="judul" id="judul" required>
            </div>
            <div class="mb-3">
                <label for="kategori" class="form-label fw-semibold">Kategori</label>
                <select class="form-select" name="kategori_id" id="kategori" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($kategori_options as $kat): ?>
                        <option value="<?= htmlspecialchars($kat['id']); ?>">
                            <?= htmlspecialchars($kat['nama']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="gambar" class="form-label fw-semibold">Gambar</label>
                <input type="file" class="form-control" name="gambar" id="gambar" accept="image/*" required>
                <div class="form-text">Format: JPG, JPEG, PNG, GIF (maks 2MB).</div>
                <img id="previewTambah" class="img-fluid rounded mt-2 d-none" style="max-height:150px;">
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

<!-- Modal Edit Foto -->
<?php
if ($query_galeri && $query_galeri->num_rows > 0):
$query_galeri->data_seek(0);
while ($data = $query_galeri->fetch_assoc()): ?>
<div class="modal fade" id="modalEditFoto<?= $data['id']; ?>" tabindex="-1" aria-labelledby="modalEditFotoLabel<?= $data['id']; ?>" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalEditFotoLabel<?= $data['id']; ?>">Edit Foto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="proses/edit_galeri.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
            <input type="hidden" name="id" value="<?= $data['id']; ?>">

            <div class="mb-3">
                <label class="form-label fw-semibold">Judul</label>
                <input type="text" class="form-control" name="judul" value="<?= htmlspecialchars($data['judul'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Kategori</label>
                <select class="form-select" name="kategori_id" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($kategori_options as $kat): ?>
                        <option value="<?= htmlspecialchars($kat['id']); ?>"
                            <?= ($kat['id'] == $data['kategori_id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($kat['nama']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Ganti Gambar (Opsional)</label>
                <input type="file" class="form-control previewEdit" name="gambar" accept="image/*" data-preview="previewEdit<?= $data['id']; ?>">
                <div class="form-text">Kosongkan jika tidak ingin mengubah gambar.</div>
                <img src='../../assets/images/galery/<?= htmlspecialchars($data['gambar'] ?? ''); ?>' 
                     id="previewEdit<?= $data['id']; ?>"
                     class='img-fluid rounded mt-2' 
                     style='max-height:150px;object-fit:cover;'>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endwhile; endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tambahInput = document.getElementById('gambar');
    const tambahPreview = document.getElementById('previewTambah');

    tambahInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            tambahPreview.classList.remove('d-none');
            tambahPreview.src = URL.createObjectURL(file);
        } else {
            tambahPreview.classList.add('d-none');
        }
    });

    document.querySelectorAll('.previewEdit').forEach(input => {
        input.addEventListener('change', function() {
            const target = document.getElementById(this.dataset.preview);
            const file = this.files[0];
            if (file) target.src = URL.createObjectURL(file);
        });
    });
});
</script>
