<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak!'); document.location.href='../login/login.php';</script>";
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';

if (isset($_SESSION['success_message'])) { 
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">'
        . htmlspecialchars($_SESSION['success_message']) .
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; 
    unset($_SESSION['success_message']); 
} 


// Ambil ID admin yang sedang login
$query_admin = $konek->prepare("SELECT id FROM users WHERE username = ?");
$query_admin->bind_param("s", $_SESSION['username']);
$query_admin->execute();
$result_admin = $query_admin->get_result();
$current_admin = $result_admin->fetch_assoc();
$current_admin_id = $current_admin['id'];

// Hitung total pengguna
$query_total_admin = $konek->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
$query_total_admin->execute();
$result_total_admin = $query_total_admin->get_result();
$totalAdmin = $result_total_admin->fetch_assoc()['total'];

$query_total_posko = $konek->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'posko'");
$query_total_posko->execute();
$result_total_posko = $query_total_posko->get_result();
$totalposko = $result_total_posko->fetch_assoc()['total'];

$query_total_user = $konek->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$query_total_user->execute();
$result_total_user = $query_total_user->get_result();
$totalUser = $result_total_user->fetch_assoc()['total'];

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

<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="fw-bold text-primary">
                    <i class="bi bi-people-fill me-2"></i>Kelola Pengguna
                </h2>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Pencarian -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-search me-2"></i>Pencarian Pengguna</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="page" value="kelola_user">
                
                <div class="col-md-8">
                    <label for="search" class="form-label fw-semibold">Cari Pengguna</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="search" name="search"
                               placeholder="Username, nama lengkap, atau email..."
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Cari
                    </button>
                    <a href="?page=kelola_user" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card bg-danger text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1"><?= $totalAdmin ?></h3>
                            <p class="mb-0">Total Admin</p>
                        </div>
                        <div class="rounded-circle bg-white bg-opacity-25 p-3">
                            <i class="bi bi-person-badge-fill fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-md-6">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1"><?= $totalposko ?></h3>
                            <p class="mb-0">Total Admin Posko</p>
                        </div>
                        <div class="rounded-circle bg-white bg-opacity-25 p-3">
                            <i class="bi bi-geo-alt-fill fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-md-6">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1"><?= $totalUser ?></h3>
                            <p class="mb-0">Total Pengguna</p>
                        </div>
                        <div class="rounded-circle bg-white bg-opacity-25 p-3">
                            <i class="bi bi-people-fill fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Pengguna -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-table me-2"></i>Daftar Pengguna
                </h5>
                <span class="badge bg-light text-dark">
                    <?= $result_users->num_rows ?> Data
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="20%">Username</th>
                            <th width="20%">Nama Lengkap</th>
                            <th width="25%">Email</th>
                            <th width="15%">Role</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if ($result_users->num_rows > 0):
                            while ($data = $result_users->fetch_assoc()):
                                $roleClass = $data['role'] == 'admin' ? 'danger' : ($data['role'] == 'posko' ? 'primary' : 'success');
                                $roleIcon = $data['role'] == 'admin' ? 'person-badge' : ($data['role'] == 'posko' ? 'geo-alt' : 'person');

                                $basePath = '../../assets/images/profile/';
                                $fotoFile = !empty($data['profile_photo']) ? htmlspecialchars($data['profile_photo']) : '';
                                $fotoPath = $basePath . $fotoFile;
                                $useImage = (!empty($fotoFile) && file_exists($fotoPath));
                                $initial = strtoupper(substr($data['username'], 0, 1));
                        ?>
                        <tr>
                            <td class="text-center"><span class="badge bg-secondary"><?= $no ?></span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if ($useImage): ?>
                                        <img src="<?= htmlspecialchars($fotoPath) ?>" alt="Foto Profil"
                                            class="rounded-circle me-2 border border-light shadow-sm"
                                            width="40" height="40" style="object-fit:cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-<?= $roleClass ?> text-white d-flex align-items-center justify-content-center me-2 shadow-sm"
                                            style="width:40px;height:40px;font-weight:bold;">
                                            <?= $initial ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-semibold"><?= htmlspecialchars($data['username']) ?></div>
                                        <small class="text-muted">ID: <?= $data['id'] ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($data['nama_lengkap']) ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-envelope-fill text-muted me-2"></i>
                                    <?= htmlspecialchars($data['email']) ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?= $roleClass ?> text-white px-3 py-2">
                                    <i class="bi bi-<?= $roleIcon ?> me-1"></i>
                                    <?= ucfirst(htmlspecialchars($data['role'])) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($data['id'] != $current_admin_id): ?>
                                    <div class="btn-group" role="group">
                                        <a href="proses/hapus_pengguna.php?id=<?= $data['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Yakin ingin menghapus user ini?')"
                                           title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <a href="proses/cetak_user.php?id=<?= $data['id'] ?>" 
                                           class="btn btn-sm btn-outline-info" 
                                           target="_blank"
                                           title="Cetak">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-person-check me-1"></i>Anda
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                                $no++;
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Tidak ada data yang tersedia
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        this.form.submit();
    }
});
</script>
