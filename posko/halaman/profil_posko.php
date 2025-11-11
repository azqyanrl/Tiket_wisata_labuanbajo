<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../../database/konek.php';

// Cek login dan role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'posko') {
    $_SESSION['error_message'] = 'Akses ditolak!';
    header('Location: login/login.php');
    exit;
}

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $user_id = $_SESSION['id_user'];
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $no_hp = trim($_POST['no_hp']);
    $lokasi = trim($_POST['lokasi']);
    
    // Ambil foto lama
    $stmt = $konek->prepare("SELECT profile_photo FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $profile_photo = $user['profile_photo'];

    // Upload foto baru
    if (!empty($_FILES['profile_photo']['name'])) {
        $file_ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_ext)) {
            $file_name = 'posko_' . $user_id . '_' . time() . '.' . $file_ext;
            $target = '../../assets/images/profile/' . $file_name;

            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target)) {
                // Hapus foto lama jika ada
                if (!empty($user['profile_photo']) && file_exists('../../assets/images/profile/' . $user['profile_photo'])) {
                    unlink('../../assets/images/profile/' . $user['profile_photo']);
                }
                $profile_photo = $file_name;
            }
        }
    }

    // Update data
    $stmt = $konek->prepare("UPDATE users SET nama_lengkap=?, email=?, no_hp=?, lokasi=?, profile_photo=? WHERE id=?");
    $stmt->bind_param('sssssi', $nama_lengkap, $email, $no_hp, $lokasi, $profile_photo, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profil berhasil diperbarui.";
        $_SESSION['nama_lengkap'] = $nama_lengkap;
        $_SESSION['profile_photo'] = $profile_photo; // ✅ Foto baru langsung tersimpan di session
    } else {
        $_SESSION['error'] = "Gagal memperbarui profil: " . $stmt->error;
    }

    // Arahkan balik ke halaman profil
    header("Location: index.php?page=profil_posko");
    exit;
}

// Ambil pesan dan hapus dari session
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Ambil data user
$user_id = $_SESSION['id_user'];
$stmt = $konek->prepare("SELECT username, email, nama_lengkap, no_hp, profile_photo, lokasi FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<div class="container py-4">
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="bi bi-person-badge me-2"></i> Informasi Profil
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <img src="../../assets/images/profile/<?= htmlspecialchars($user['profile_photo'] ?? 'default.png') ?>"
                                     class="rounded-circle border border-3 border-white shadow"
                                     width="150" height="150" alt="Foto Profil" id="previewImage">
                                <label for="profile_photo"
                                       class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2"
                                       style="cursor: pointer; margin-right: 10px; margin-bottom: 10px;">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="nama_lengkap" class="form-control" id="nama_lengkap"
                                           value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required>
                                    <label for="nama_lengkap">Nama Lengkap</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" name="email" class="form-control" id="email"
                                           value="<?= htmlspecialchars($user['email']) ?>" required>
                                    <label for="email">Email</label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="no_hp" class="form-control" id="no_hp"
                                           value="<?= htmlspecialchars($user['no_hp']) ?>" required>
                                    <label for="no_hp">Nomor HP</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="lokasi" class="form-control" id="lokasi"
                                           value="<?= htmlspecialchars($user['lokasi']) ?>" required>
                                    <label for="lokasi">Lokasi</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mt-3">
                            <input type="file" name="profile_photo" class="form-control" id="profile_photo"
                                   accept="image/*" style="height: auto; padding: 0.75rem 1rem;">
                            <label for="profile_photo">Foto Profil (opsional)</label>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" name="update_profile" class="btn btn-primary btn-lg">
                                <i class="bi bi-save me-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>

                    <div class="alert alert-info mt-4 d-flex align-items-center" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <div>Perubahan password hanya dapat dilakukan oleh Admin Pusat.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('profile_photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('previewImage').src = ev.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>
