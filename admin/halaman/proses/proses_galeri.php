<?php
include '../../../database/konek.php';
include '../../../includes/boot.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul']; 
    $kategori = $_POST['kategori']; 
    
    // Proses upload gambar
    $gambar = basename($_FILES['gambar']['name']); 
    $upload_dir = '../../../assets/images/';
    $target_file = $upload_dir . $gambar;
    
    // Validasi sederhana, pastikan file adalah gambar
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        $_SESSION['error_message'] = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
        header("Location: ../index.php?page=kelola_galeri");
        exit();
    }
    
    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
        $query_insert = $konek->prepare("INSERT INTO galleries (judul, gambar, kategori) VALUES (?, ?, ?)");
        $query_insert->bind_param("sss", $judul, $gambar, $kategori); 
        $query_insert->execute();
        $_SESSION['success_message'] = "Foto berhasil ditambahkan."; 
    } else {
        $_SESSION['error_message'] = "Maaf, terjadi kesalahan saat mengupload file.";
    }
    
    header("Location: ../index.php?page=kelola_galeri"); 
    exit();
}
?>

<div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Foto</h5>
                <a href="?page=kelola_galeri" class="btn-close"></a>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul</label>
                        <input type="text" name="judul" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <input type="text" name="kategori" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar</label>
                        <input type="file" name="gambar" class="form-control" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="?page=kelola_galeri" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>