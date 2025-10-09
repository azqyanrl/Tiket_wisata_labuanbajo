<?php
include '../../../database/konek.php';
include '../../../includes/boot.php';
include '../../../proses/stok_harian.php'; // Tambahkan ini

 $editing = isset($_GET['id']);
if ($editing) { 
    $query_tiket = $konek->prepare("SELECT * FROM tiket WHERE id = ?"); 
    $query_tiket->bind_param("i", $_GET['id']); 
    $query_tiket->execute(); 
    $tiket = $query_tiket->get_result()->fetch_assoc(); 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_paket = $_POST['nama_paket']; 
    $deskripsi = $_POST['deskripsi']; 
    $harga = $_POST['harga']; 
    $durasi = $_POST['durasi']; 
    $kategori = $_POST['kategori']; 
    $status = $_POST['status'];
    $stok = $_POST['stok']; // Tambahkan ini
    
    $gambar = $editing ? $tiket['gambar'] : '';
    if (!empty($_FILES['gambar']['name'])) { 
        $upload_dir = '../../../assets/images/'; 
        $gambar = basename($_FILES['gambar']['name']); 
        move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_dir . $gambar); 
    }
    
    if ($editing) {
        $query_update = $konek->prepare("UPDATE tiket SET nama_paket=?, deskripsi=?, harga=?, durasi=?, kategori=?, status=?, gambar=?, stok=? WHERE id=?");
        $query_update->bind_param("ssdssssii", $nama_paket, $deskripsi, $harga, $durasi, $kategori, $status, $gambar, $stok, $_GET['id']);
        $query_update->execute();
    } else {
        $query_insert = $konek->prepare("INSERT INTO tiket (nama_paket, deskripsi, harga, durasi, kategori, status, gambar, stok) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $query_insert->bind_param("ssdssssi", $nama_paket, $deskripsi, $harga, $durasi, $kategori, $status, $gambar, $stok);
        $query_insert->execute();
    }
    
    $_SESSION['success_message'] = "Tiket berhasil disimpan."; 
    header("Location: ../index.php?page=kelola_tiket"); 
    exit();
}
?>
<div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><?= $editing ? 'Edit' : 'Tambah' ?> Tiket</h5><a href="?page=kelola_tiket" class="btn-close"></a></div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Nama Paket</label><input type="text" name="nama_paket" class="form-control" value="<?= $tiket['nama_paket'] ?? '' ?>" required></div>
                    <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="deskripsi" class="form-control" required><?= $tiket['deskripsi'] ?? '' ?></textarea></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3"><label class="form-label">Harga</label><input type="number" name="harga" class="form-control" value="<?= $tiket['harga'] ?? '' ?>" required></div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3"><label class="form-label">Stok Harian</label><input type="number" name="stok" class="form-control" value="<?= $tiket['stok'] ?? '0' ?>" required></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3"><label class="form-label">Durasi</label><input type="text" name="durasi" class="form-control" value="<?= $tiket['durasi'] ?? '' ?>" required></div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3"><label class="form-label">Kategori</label><input type="text" name="kategori" class="form-control" value="<?= $tiket['kategori'] ?? '' ?>" required></div>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="aktif" <?= ($tiket['status'] ?? '') == 'aktif' ? 'selected' : '' ?>>Aktif</option><option value="nonaktif" <?= ($tiket['status'] ?? '') == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option></select></div>
                    <div class="mb-3"><label class="form-label">Gambar</label><input type="file" name="gambar" class="form-control" accept="image/*"></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button><a href="?page=kelola_tiket" class="btn btn-secondary">Batal</a></div>
            </form>
        </div>
    </div>
</div>