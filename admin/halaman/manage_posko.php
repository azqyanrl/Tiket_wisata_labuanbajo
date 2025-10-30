<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login/login.php');
    exit();
}
include '../../../database/konek.php';

 $stmt = $konek->prepare("SELECT id, username, email, nama_lengkap, lokasi FROM users WHERE role = 'posko' ORDER BY lokasi, nama_lengkap");
 $stmt->execute();
 $posko_admins = $stmt->get_result();
?>
<!-- (Letakkan kode ini di dalam template halaman admin-mu) -->
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Manajemen Admin Posko</h1>
    
    <a href="add_posko.php" class="btn btn-primary mb-3">+ Tambah Admin Posko</a>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr><th>Nama</th><th>Username</th><th>Email</th><th>Lokasi</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php while($admin = $posko_admins->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($admin['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($admin['username']) ?></td>
                            <td><?= htmlspecialchars($admin['email']) ?></td>
                            <td><?= htmlspecialchars($admin['lokasi']) ?></td>
                            <td>
                                <a href="edit_posko.php?id=<?= $admin['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                                <a href="proses_posko.php?delete=<?= $admin['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>