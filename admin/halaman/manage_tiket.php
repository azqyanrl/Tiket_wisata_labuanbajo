<?php if (isset($_SESSION['success_message'])) { echo '<div class="alert alert-success alert-dismissible fade show" role="alert">'.$_SESSION['success_message'].'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; unset($_SESSION['success_message']); } ?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"><h1 class="h2">Kelola Tiket</h1><a href="?page=kelola_tiket&action=add" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Tambah Tiket</a></div>
<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-light"><tr><th>Gambar</th><th>Nama Paket</th><th>Harga</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
            <?php $stmt = $konek->query("SELECT * FROM tiket ORDER BY created_at DESC"); 
            if ($stmt->num_rows > 0) { while($data = $stmt->fetch_assoc()) { 
                echo "<tr><td><img src='../../assets/images/{$data['gambar']}' width='60' class='rounded'></td><td>{$data['nama_paket']}</td><td>Rp " . number_format($data['harga'], 0, ',', '.') . "</td><td><span class='badge bg-" . (($data['status']=='aktif') ? 'success' : 'danger') . "'>" . ucfirst($data['status']) . "</span></td><td><a href='?page=kelola_tiket&action=edit&id={$data['id']}' class='btn btn-sm btn-warning'>Edit</a> <a href='proses/hapus_tiket.php?id={$data['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin ingin hapus?\")'>Hapus</a></td></tr>"; 
            } } else { echo "<tr><td colspan='5' class='text-center'>Tidak ada data.</td></tr>"; } ?>
        </tbody>
    </table>
</div>
<?php if (isset($_GET['action']) && in_array($_GET['action'], ['add', 'edit'])) { include 'proses/proses_tiket.php'; } ?>