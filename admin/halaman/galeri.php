<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak!'); document.location.href='../login/login.php';</script>";
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';

// Tampilkan pesan sukses atau error dari session
if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['success_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['error_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Kelola Galeri</h1>
    <!-- UBAH: Link diubah menjadi tombol untuk memunculkan modal -->
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahFoto">
        <i class="bi bi-plus-circle me-1"></i> Tambah Foto
    </button>
</div>

<div class="row g-3">
    <?php 
    $query_galeri = $konek->query("SELECT * FROM galleries ORDER BY created_at DESC"); 
    if ($query_galeri->num_rows > 0) { 
        while($data = $query_galeri->fetch_assoc()) { 
            echo "<div class='col-md-3'>
                <div class='card'>
                    <img src='../../assets/images/".htmlspecialchars($data['gambar'])."' class='card-img-top' style='height:200px;object-fit:cover;' alt='".htmlspecialchars($data['judul'])."'>
                    <div class='card-body'>
                        <h6 class='card-title'>".htmlspecialchars($data['judul'])."</h6>
                        <p class='card-text'><small class='text-muted'>Kategori: ".htmlspecialchars($data['kategori'])."</small></p>
                        <a href='proses/hapus_galeri.php?id=".htmlspecialchars($data['id'])."' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin ingin menghapus foto ini?\")'>Hapus</a>
                    </div>
                </div>
            </div>"; 
        } 
    } else { 
        echo "<p class='col-12 text-center'>Tidak ada foto.</p>"; 
    } 
    ?>
</div>

<div class="modal fade" id="modalTambahFoto" tabindex="-1" aria-labelledby="modalTambahFotoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTambahFotoLabel">Tambah Foto Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- PERHATIKAN: action form menunjuk ke file pemroses -->
      <form action="proses/proses_galeri.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
            <div class="mb-3">
                <label for="judul" class="form-label">Judul</label>
                <input type="text" class="form-control" name="judul" id="judul" required>
            </div>
            <div class="mb-3">
                <label for="kategori" class="form-label">Kategori</label>
                <input type="text" class="form-control" name="kategori" id="kategori" required>
            </div>
            <div class="mb-3">
                <label for="gambar" class="form-label">Gambar</label>
                <input type="file" class="form-control" name="gambar" id="gambar" accept="image/*" required>
                <div class="form-text">Format yang diizinkan: JPG, JPEG, PNG, GIF. Maksimal 2MB.</div>
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