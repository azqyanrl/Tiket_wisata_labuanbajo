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

// Ambil data admin yang sedang login untuk ditampilkan di form
 $query_admin = $konek->prepare("SELECT * FROM users WHERE username = ?");
 $query_admin->bind_param("s", $_SESSION['username']);
 $query_admin->execute();
 $result_admin = $query_admin->get_result();
 $admin_data = $result_admin->fetch_assoc();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Profile Admin</h1>
</div>

<?php include '../../includes/alerts.php'; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Foto Profile</h5>
            </div>
            <div class="card-body text-center">
                <img src="../../assets/images/profile/<?php echo !empty($admin_data['profile_photo']) ? htmlspecialchars($admin_data['profile_photo']) : 'default_admin.png'; ?>" 
                     class="rounded-circle mb-3" width="150" height="150" alt="Profile Photo">
                
                <!-- PERUBAHAN: action diubah ke proses_admin.php -->
                <form method="POST" action="proses/proses_admin.php" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="profile_photo" class="form-label">Ganti Foto</label>
                        <input type="file" class="form-control" id="profile_photo" name="profile_photo" accept="image/*">
                        <div class="form-text">Format: JPG, JPEG, PNG, GIF. Maksimal: 2MB</div>
                    </div>
                    <button type="submit" name="upload_photo" class="btn btn-primary btn-sm">Upload Foto</button>
                </form>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Informasi Akun</h5>
            </div>
            <div class="card-body">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($admin_data['username']); ?></p>
                <p><strong>Role:</strong> <span class="badge bg-danger">Admin</span></p>
                <p><strong>Bergabung:</strong> <?php echo date('d/m/Y', strtotime($admin_data['created_at'])); ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#profile" role="tab">Data Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#password" role="tab">Ubah Password</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="profile" role="tabpanel">
                        <!-- PERUBAHAN: action diubah ke proses_admin.php -->
                        <form method="POST" action="proses/proses_admin.php">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                               value="<?php echo htmlspecialchars($admin_data['nama_lengkap']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($admin_data['email']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="no_hp" class="form-label">No. HP</label>
                                <input type="tel" class="form-control" id="no_hp" name="no_hp" 
                                       value="<?php echo htmlspecialchars($admin_data['no_hp']); ?>">
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                    
                    <div class="tab-pane fade" id="password" role="tabpanel">
                        <!-- PERUBAHAN: action diubah ke proses_admin.php -->
                        <form method="POST" action="proses/proses_admin.php">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Password Saat Ini</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Password Baru</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <div class="form-text">Minimal 6 karakter</div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-primary">Ubah Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>