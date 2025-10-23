<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Akses ditolak!";
    header('location: ../login/login.php');
    exit;
}

// Path ke database dan includes dari folder /halaman/
include '../../database/konek.php';
include '../../includes/boot.php';
include '../../includes/alerts.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Kelola Kategori</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kategoriModal">
        <i class="bi bi-plus-circle me-1"></i> Tambah Kategori
    </button>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Nama Kategori</th>
                <th>Deskripsi</th>
                <th>Jumlah Tiket</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $query_kategori = $konek->query("
                SELECT 
                    k.*, 
                    (SELECT COUNT(*) FROM tiket WHERE kategori_id = k.id) as jumlah_tiket 
                FROM kategori k 
                ORDER BY k.nama ASC
            "); 
            
            if ($query_kategori->num_rows > 0) { 
                while($data = $query_kategori->fetch_assoc()) { 
                    echo "<tr>
                        <td>".htmlspecialchars($data['id'])."</td>
                        <td><strong>".htmlspecialchars($data['nama'])."</strong></td>
                        <td>".htmlspecialchars($data['deskripsi'] ?? '-')."</td>
                        <td><span class='badge bg-info'>".htmlspecialchars($data['jumlah_tiket'])."</span></td>
                        <td>
                            <a href='?page=kelola_kategori&action=edit&id=" . htmlspecialchars($data['id']) . "' class='btn btn-sm btn-warning'>
                                <i class='bi bi-pencil-square'></i> Edit
                            </a> 
                            <!-- Path hapus menunjuk ke folder proses yang selevel -->
                            <a href='proses/hapus_kategori.php?id=" . htmlspecialchars($data['id']) . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin ingin hapus kategori ini?\")' ". ($data['jumlah_tiket'] > 0 ? 'disabled' : '') . ">
                                <i class='bi bi-trash'></i> Hapus
                            </a>
                        </td>
                    </tr>"; 
                } 
            } else { 
                echo "<tr><td colspan='5' class='text-center'>Tidak ada data kategori.</td></tr>"; 
            } 
            ?>
        </tbody>
    </table>
</div>

<!-- Include modal form dari folder /proses/ -->
<?php include 'proses/form_kategori.php'; ?>