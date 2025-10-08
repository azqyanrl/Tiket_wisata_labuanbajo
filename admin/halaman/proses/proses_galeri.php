<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul']; $kategori = $_POST['kategori']; $gambar = basename($_FILES['gambar']['name']); $upload_dir = '../../assets/images/';
    move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_dir . $gambar);
    $stmt = $konek->prepare("INSERT INTO galleries (judul, gambar, kategori) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $judul, $gambar, $kategori); $stmt->execute();
    $_SESSION['success_message'] = "Foto berhasil ditambahkan."; header("Location: ../index.php?page=kelola_galeri"); exit();
}
?>
<div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Tambah Foto</h5><a href="?page=kelola_galeri" class="btn-close"></a></div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Judul</label><input type="text" name="judul" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Kategori</label><input type="text" name="kategori" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Gambar</label><input type="file" name="gambar" class="form-control" accept="image/*" required></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button><a href="?page=kelola_galeri" class="btn btn-secondary">Batal</a></div>
            </form>
        </div>
    </div>
</div>