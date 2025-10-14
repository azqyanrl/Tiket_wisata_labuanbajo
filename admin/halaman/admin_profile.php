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

// Ambil data admin yang sedang login
 $query_admin = $konek->prepare("SELECT * FROM users WHERE username = ?");
 $query_admin->bind_param("s", $_SESSION['username']);
 $query_admin->execute();
 $result_admin = $query_admin->get_result();
 $admin_data = $result_admin->fetch_assoc();

// Proses update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nama_lengkap = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];
    
    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Format email tidak valid.";
        header("Location: admin_profile.php");
        exit;
    }
    
    // Cek apakah email sudah digunakan oleh user lain
    $cek_email = $konek->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $cek_email->bind_param("si", $email, $admin_data['id']);
    $cek_email->execute();
    $result_cek = $cek_email->get_result();
    
    if ($result_cek->num_rows > 0) {
        $_SESSION['error_message'] = "Email sudah digunakan oleh user lain.";
        header("Location: admin_profile.php");
        exit;
    }
    
    // Update data admin
    $update_admin = $konek->prepare("UPDATE users SET nama_lengkap = ?, email = ?, no_hp = ? WHERE id = ?");
    $update_admin->bind_param("sssi", $nama_lengkap, $email, $no_hp, $admin_data['id']);
    
    if ($update_admin->execute()) {
        $_SESSION['success_message'] = "Profile berhasil diperbarui.";
        header("Location: admin_profile.php");
        exit;
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui profile.";
    }
}

// Proses ganti password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi password
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error_message'] = "Semua field password harus diisi.";
        header("Location: admin_profile.php");
        exit;
    }
    
    if ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "Password baru dan konfirmasi tidak cocok.";
        header("Location: admin_profile.php");
        exit;
    }
    
    if (strlen($new_password) < 6) {
        $_SESSION['error_message'] = "Password baru minimal 6 karakter.";
        header("Location: admin_profile.php");
        exit;
    }
    
    // Verifikasi password saat ini
    if (password_verify($current_password, $admin_data['password'])) {
        // Hash password baru
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password
        $update_password = $konek->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_password->bind_param("si", $hashed_password, $admin_data['id']);
        
        if ($update_password->execute()) {
            $_SESSION['success_message'] = "Password berhasil diubah.";
            header("Location: admin_profile.php");
            exit;
        } else {
            $_SESSION['error_message'] = "Gagal mengubah password.";
        }
    } else {
        $_SESSION['error_message'] = "Password saat ini salah.";
    }
}

// Proses upload foto profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo'])) {
    if (!empty($_FILES['profile_photo']['name'])) {
        $upload_dir = '../../assets/images/profiles/';
        
        // Buat folder jika belum ada
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_types)) {
            if ($_FILES['profile_photo']['size'] <= 2097152) { // 2MB
                $new_filename = 'admin_' . $admin_data['id'] . '_' . time() . '.' . $file_extension;
                $target_file = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_file)) {
                    // Hapus foto lama jika ada
                    if (!empty($admin_data['profile_photo'])) {
                        $old_file = $upload_dir . $admin_data['profile_photo'];
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }
                    
                    // Update database
                    $update_photo = $konek->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
                    $update_photo->bind_param("si", $new_filename, $admin_data['id']);
                    
                    if ($update_photo->execute()) {
                        $_SESSION['success_message'] = "Foto profile berhasil diubah.";
                        header("Location: admin_profile.php");
                        exit;
                    } else {
                        $_SESSION['error_message'] = "Gagal mengupdate foto profile.";
                    }
                } else {
                    $_SESSION['error_message'] = "Gagal mengupload foto.";
                }
            } else {
                $_SESSION['error_message'] = "Ukuran foto terlalu besar. Maksimal 2MB.";
            }
        } else {
            $_SESSION['error_message'] = "Format foto tidak diizinkan. Gunakan JPG, JPEG, PNG, atau GIF.";
        }
    }
}

// Refresh data admin setelah update
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
                <img src="../../assets/images/profiles/<?php echo !empty($admin_data['profile_photo']) ? htmlspecialchars($admin_data['profile_photo']) : 'default_admin.png'; ?>" 
                     class="rounded-circle mb-3" width="150" height="150" alt="Profile Photo">
                
                <form method="POST" enctype="multipart/form-data">
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
                        <form method="POST">
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
                        <form method="POST">
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