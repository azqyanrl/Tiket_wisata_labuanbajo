<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Cek login admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Akses ditolak! Anda harus login sebagai admin.";
    header('location: ../login/login.php');
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';
include '../../includes/alerts.php';
include '../../includes/stok_otomatis.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Kelola Tiket</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tiketModal">
        <i class="bi bi-plus-circle me-1"></i> Tambah Tiket
    </button>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Gambar</th>
                <th>Nama Paket</th>
                <th>Harga</th>
                <th>Stok Total</th>
                <th>Stok Tersisa (Hari Ini)</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $query_tiket = $konek->query("SELECT * FROM tiket ORDER BY created_at DESC"); 
            if ($query_tiket->num_rows > 0) { 
                while($data = $query_tiket->fetch_assoc()) { 
                    // 🔹 Ambil stok otomatis dari helper
                    $stok_tersisa = getStokTersisa($konek, $data['id']);
                    echo "<tr>
                        <td><img src='../../assets/images/tiket/".htmlspecialchars($data['gambar'])."' width='60' class='rounded'></td>
                        <td>".htmlspecialchars($data['nama_paket'])."</td>
                        <td>Rp " . number_format($data['harga'], 0, ',', '.') . "</td>
                        <td>" . htmlspecialchars($data['stok']) . "</td>
                        <td>" . htmlspecialchars($stok_tersisa) . "</td>
                        <td><span class='badge bg-" . (($data['status']=='aktif') ? 'success' : 'danger') . "'>" . ucfirst(htmlspecialchars($data['status'])) . "</span></td>
                        <td>
                            <a href='?page=kelola_tiket&action=edit&id=" . htmlspecialchars($data['id']) . "' class='btn btn-sm btn-warning'>
                                <i class='bi bi-pencil-square'></i> Edit
                            </a> 
                            <a href='proses/hapus_tiket.php?id=" . htmlspecialchars($data['id']) . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin ingin hapus?\")'>
                                <i class='bi bi-trash'></i> Hapus
                            </a>
                        </td>
                    </tr>"; 
                } 
            } else { 
                echo "<tr><td colspan='7' class='text-center'>Tidak ada data.</td></tr>"; 
            } 
            ?>
        </tbody>
    </table>
</div>

<?php 
if (isset($_GET['action']) && in_array($_GET['action'], ['add', 'edit'])) { 
    include 'form_tiket.php'; 
} 
?>