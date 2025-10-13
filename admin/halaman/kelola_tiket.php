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
if (isset($_SESSION['success_message'])) { 
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">'.$_SESSION['success_message'].'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; 
    unset($_SESSION['success_message']); 
} 
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Kelola Tiket</h1>
    <a href="?page=kelola_tiket&action=add" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Tambah Tiket</a>
</div>
<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Gambar</th>
                <th>Nama Paket</th>
                <th>Harga</th>
                <th>Stok Harian</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $query_tiket = $konek->query("SELECT * FROM tiket ORDER BY created_at DESC"); 
            if ($query_tiket->num_rows > 0) { 
                while($data = $query_tiket->fetch_assoc()) { 
                    // Ambil stok hari ini
                    $tanggal_hari_ini = date('Y-m-d');
                    $query_stok = $konek->prepare("SELECT stok_tersisa FROM stok_harian WHERE tiket_id = ? AND tanggal = ?");
                    $query_stok->bind_param("is", $data['id'], $tanggal_hari_ini);
                    $query_stok->execute();
                    $result_stok = $query_stok->get_result();
                    
                    $stok_tersedia = 0;
                    if ($result_stok->num_rows > 0) {
                        $stok_data = $result_stok->fetch_assoc();
                        $stok_tersedia = $stok_data['stok_tersisa'];
                    } else {
                        // Jika tidak ada data stok untuk hari ini, gunakan stok default
                        $stok_tersedia = $data['stok'];
                    }
                    
                    echo "<tr>
                        <td><img src='../../assets/images/{$data['gambar']}' width='60' class='rounded'></td>
                        <td>{$data['nama_paket']}</td>
                        <td>Rp " . number_format($data['harga'], 0, ',', '.') . "</td>
                        <td>{$stok_tersedia}</td>
                        <td><span class='badge bg-" . (($data['status']=='aktif') ? 'success' : 'danger') . "'>" . ucfirst($data['status']) . "</span></td>
                        <td>
                            <a href='?page=kelola_tiket&action=edit&id={$data['id']}' class='btn btn-sm btn-warning'>Edit</a> 
                            <a href='proses/hapus_tiket.php?id={$data['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin ingin hapus?\")'>Hapus</a>
                        </td>
                    </tr>"; 
                } 
            } else { 
                echo "<tr><td colspan='6' class='text-center'>Tidak ada data.</td></tr>"; 
            } 
            ?>
        </tbody>
    </table>
</div>
<?php if (isset($_GET['action']) && in_array($_GET['action'], ['add', 'edit'])) { include 'proses/proses_tiket.php'; } ?>