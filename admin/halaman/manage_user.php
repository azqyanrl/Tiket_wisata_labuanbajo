<?php if (isset($_SESSION['success_message'])) { echo '<div class="alert alert-success alert-dismissible fade show" role="alert">'.$_SESSION['success_message'].'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; unset($_SESSION['success_message']); } ?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"><h1 class="h2">Kelola Pengguna</h1></div>
<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-light"><tr><th>Username</th><th>Nama Lengkap</th><th>Email</th><th>Role</th><th>Aksi</th></tr></thead>
        <tbody>
            <?php $stmt = $konek->query("SELECT * FROM users ORDER BY created_at DESC"); 
            if ($stmt->num_rows > 0) { while($data = $stmt->fetch_assoc()) { 
                echo "<tr><td>{$data['username']}</td><td>{$data['nama_lengkap']}</td><td>{$data['email']}</td><td><span class='badge bg-" . (($data['role']=='admin') ? 'danger' : 'primary') . "'>" . ucfirst($data['role']) . "</span></td><td>"; 
                if($data['id'] != $_SESSION['user_id']) { 
                    echo "<a href='proses/hapus_pengguna.php?id={$data['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin ingin hapus user ini?\")'>Hapus</a>"; 
                } else { 
                    echo "<span class='text-muted'>Anda</span>"; 
                } 
                echo "</td></tr>"; 
            } } else { 
                echo "<tr><td colspan='5' class='text-center'>Tidak ada data.</td></tr>"; 
            } ?>
        </tbody>
    </table>
</div>