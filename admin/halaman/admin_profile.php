<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak!'); location.href='../login/login.php';</script>";
    exit;
}

include '../../database/konek.php';

$stmt = $konek->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$admin_data = $stmt->get_result()->fetch_assoc();
?>

<div class="container py-4">
    <div class="text-center mb-4">
        <h2 class="fw-bold">Profile Admin</h2>
        <p class="text-muted">Kelola informasi profil dan keamanan akun Anda</p>
        <hr>
    </div>
<div class="row g-4">
    <!-- Kartu kiri -->
    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center">
                <img src="../../assets/images/profile/<?= !empty($admin_data['profile_photo']) ? htmlspecialchars($admin_data['profile_photo']) : 'default_admin.png' ?>" 
                     class="rounded-circle mb-3" width="150" height="150" style="object-fit: cover;">
                <form method="POST" action="proses/proses_admin.php" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="profile_photo" class="form-label fw-semibold">Ganti Foto Profil</label>
                        <input type="file" class="form-control" id="profile_photo" name="profile_photo" accept="image/*">
                        <div class="form-text">Format: JPG, PNG, GIF. Maks. 2MB</div>
                    </div>
                    <button type="submit" name="upload_photo" class="btn btn-primary w-100">
                        <i class="bi bi-upload me-1"></i> Upload
                    </button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white fw-semibold">Informasi Akun</div>
            <div class="card-body">
                <p><strong>Username:</strong> <?= htmlspecialchars($admin_data['username']); ?></p>
                <p><strong>Role:</strong> <span class="badge bg-gradient bg-info">Admin</span></p>
                <p><strong>Bergabung:</strong> <?= date('d F Y', strtotime($admin_data['created_at'])); ?></p>
            </div>
        </div>
    </div>

    <!-- Kartu kanan -->
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabProfile" type="button">Data Profil</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabPassword" type="button">Ubah Password</button></li>
                </ul>
            </div>

            <div class="card-body tab-content">
                <!-- Tab Profile -->
                <div class="tab-pane fade show active" id="tabProfile">
                    <form method="POST" action="proses/proses_admin.php">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control" required
                                       value="<?= htmlspecialchars($admin_data['nama_lengkap']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" required
                                       value="<?= htmlspecialchars($admin_data['email']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nomor HP</label>
                                <input type="text" name="no_hp" class="form-control"
                                       value="<?= htmlspecialchars($admin_data['no_hp']); ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Alamat</label>
                                <textarea name="alamat" rows="3" class="form-control"><?= htmlspecialchars($admin_data['alamat'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="mt-3 text-end">
                            <button type="submit" name="update_profile" class="btn " style="background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);transform: translateY(-2px); box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); color: white;">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="tabPassword">
                    <form method="POST" action="proses/proses_admin.php">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password Saat Ini</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password Baru</label>
                            <input type="password" name="new_password" id="new_password" class="form-control" required>
                            <div class="progress mt-2" style="height:6px;">
                                <div id="password-strength" class="progress-bar"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="change_password" class="btn" style="background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);transform: translateY(-2px); box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); color: white;">
                            <i class="bi bi-shield-lock me-1"></i> Ubah Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<script>
// Cek kekuatan password
document.getElementById('new_password').addEventListener('input', e => {
    const p = e.target.value, bar = document.getElementById('password-strength');
    let val = 0;
    if (p.length >= 6) val += 25;
    if (/[a-z]/.test(p)) val += 25;
    if (/[A-Z]/.test(p)) val += 25;
    if (/[0-9]/.test(p)) val += 25;
    bar.style.width = val + '%';
    bar.className = 'progress-bar ' + (val < 50 ? 'bg-danger' : val < 75 ? 'bg-warning' : 'bg-success');
});
</script>
