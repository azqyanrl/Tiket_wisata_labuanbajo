<?php 
include '../../database/konek.php';
include '../boot.php';
if (isset($_SESSION['success_message'])) { 
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">'.$_SESSION['success_message'].'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; 
    unset($_SESSION['success_message']); 
} 

// Ambil ID admin yang sedang login
 $query_admin = $konek->prepare("SELECT id FROM users WHERE username = ?");
 $query_admin->bind_param("s", $_SESSION['username']);
 $query_admin->execute();
 $result_admin = $query_admin->get_result();
 $current_admin = $result_admin->fetch_assoc();
 $current_admin_id = $current_admin['id'];

// Proses pencarian
 $search = isset($_GET['search']) ? $_GET['search'] : '';
if (!empty($search)) {
    $query_users = $konek->prepare("SELECT * FROM users WHERE username LIKE ? OR nama_lengkap LIKE ? OR email LIKE ? ORDER BY created_at DESC");
    $search_param = "%$search%";
    $query_users->bind_param("sss", $search_param, $search_param, $search_param);
    $query_users->execute();
    $result_users = $query_users->get_result();
} else {
    $query_users = $konek->query("SELECT * FROM users ORDER BY created_at DESC");
    $result_users = $query_users;
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Kelola Pengguna</h1>
</div>

<!-- Form Pencarian -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-8">
                <label for="search" class="form-label">Cari Pengguna</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="Username, nama lengkap, atau email..." value="<?php echo $search; ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Cari</button>
                <a href="?page=kelola_user" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Username</th>
                <th>Nama Lengkap</th>
                <th>Email</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result_users->num_rows > 0) {
                while($data = $result_users->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$data['username']}</td>";
                    echo "<td>{$data['nama_lengkap']}</td>";
                    echo "<td>{$data['email']}</td>";
                    echo "<td><span class='badge bg-" . (($data['role']=='admin') ? 'danger' : 'primary') . "'>" . ucfirst($data['role']) . "</span></td>";
                    echo "<td>";
                    // Gunakan $current_admin_id yang sudah kita ambil dari database
                    if($data['id'] != $current_admin_id) {
                        echo "<a href='proses/hapus_pengguna.php?id={$data['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin ingin hapus user ini?\")'>Hapus</a> ";
                        echo "<a href='proses/cetak_user.php?id={$data['id']}' class='btn btn-sm btn-info' target='_blank'>Cetak</a>";
                    } else {
                        echo "<span class='text-muted'>Anda</span>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center'>Tidak ada data.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>