<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak!'); document.location.href='../login/login.php';</script>";
    exit;
}

include '../../database/konek.php';
include '../boot.php';

// Tampilkan pesan sukses jika ada
if (isset($_SESSION['success_message'])) { 
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">'.$_SESSION['success_message'].'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; 
    unset($_SESSION['success_message']); 
} 
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Kelola Galeri</h1>
    <a href="?page=kelola_galeri&action=add" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Tambah Foto</a>
</div>

<div class="row g-3">
    <?php 
    $query_galeri = $konek->query("SELECT * FROM galleries ORDER BY created_at DESC"); 
    if ($query_galeri->num_rows > 0) { 
        while($data = $query_galeri->fetch_assoc()) { 
            echo "<div class='col-md-3'>
                <div class='card'>
                    <img src='../../assets/images/{$data['gambar']}' class='card-img-top' style='height:200px;object-fit:cover;' alt='{$data['judul']}'>
                    <div class='card-body'>
                        <h6 class='card-title'>{$data['judul']}</h6>
                        <p class='card-text'><small class='text-muted'>Kategori: {$data['kategori']}</small></p>
                        <a href='proses/hapus_galeri.php?id={$data['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin ingin menghapus foto ini?\")'>Hapus</a>
                    </div>
                </div>
            </div>"; 
        } 
    } else { 
        echo "<p class='col-12 text-center'>Tidak ada foto.</p>"; 
    } 
    ?>
</div>

<?php if (isset($_GET['action']) && $_GET['action'] == 'add') { include 'proses/proses_galeri.php'; } ?>