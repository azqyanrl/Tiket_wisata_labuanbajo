<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login/login.php');
    exit;
}

include '../../../database/konek.php';

$query_admin = $konek->prepare("SELECT * FROM users WHERE username = ?");
$query_admin->bind_param("s", $_SESSION['username']);
$query_admin->execute();
$result_admin = $query_admin->get_result();
$admin_data = $result_admin->fetch_assoc();

// Update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nama_lengkap = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Format email tidak valid.";
    } else {
        $cek_email = $konek->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $cek_email->bind_param("si", $email, $admin_data['id']);
        $cek_email->execute();
        if ($cek_email->get_result()->num_rows > 0) {
            $_SESSION['error_message'] = "Email sudah digunakan oleh user lain.";
        } else {
            $update_admin = $konek->prepare("UPDATE users SET nama_lengkap = ?, email = ?, no_hp = ? WHERE id = ?");
            $update_admin->bind_param("sssi", $nama_lengkap, $email, $no_hp, $admin_data['id']);
            if ($update_admin->execute()) {
                $_SESSION['success_message'] = "Profile berhasil diperbarui.";
            } else {
                $_SESSION['error_message'] = "Gagal memperbarui profile.";
            }
        }
    }
}

// Ganti password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error_message'] = "Semua field password harus diisi.";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "Password baru dan konfirmasi tidak cocok.";
    } elseif (strlen($new_password) < 6) {
        $_SESSION['error_message'] = "Password baru minimal 6 karakter.";
    } elseif (!password_verify($current_password, $admin_data['password'])) {
        $_SESSION['error_message'] = "Password saat ini salah.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_password = $konek->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_password->bind_param("si", $hashed_password, $admin_data['id']);
        if ($update_password->execute()) {
            $_SESSION['success_message'] = "Password berhasil diubah.";
        } else {
            $_SESSION['error_message'] = "Gagal mengubah password.";
        }
    }
}

// Upload foto profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo'])) {
    if (!empty($_FILES['profile_photo']['name'])) {
        $upload_dir = '../../../assets/images/profile/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));

        if (in_array($file_extension, $allowed_types)) {
            if ($_FILES['profile_photo']['size'] <= 2097152) {
                $new_filename = 'admin_' . $admin_data['id'] . '_' . time() . '.' . $file_extension;
                $target_file = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_file)) {
                    if (!empty($admin_data['profile_photo'])) {
                        $old_file = $upload_dir . $admin_data['profile_photo'];
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }

                    $update_photo = $konek->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
                    $update_photo->bind_param("si", $new_filename, $admin_data['id']);
                    if ($update_photo->execute()) {
                        $_SESSION['profile_photo'] = $new_filename;
                        $_SESSION['success_message'] = "Foto profile berhasil diubah.";
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

// Redirect kembali ke halaman profil
header("Location: ../index.php?page=admin_profile");
exit;
?>
