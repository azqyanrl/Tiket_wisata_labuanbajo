<?php
require_once __DIR__ . '/../../includes/common.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php'); 
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf($token)) {
    flash('error', 'Token CSRF tidak valid');
    header('Location: profile.php'); 
    exit;
}

$userId = (int) $_SESSION['user_id'];
$username = trim($_POST['username'] ?? '');
$nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
$no_hp = trim($_POST['no_hp'] ?? '');

if ($username === '' || strlen($username) > 50) {
    flash('error', 'Username tidak valid');
    header('Location: profile.php'); 
    exit;
}
if ($nama_lengkap === '' || strlen($nama_lengkap) > 100) {
    flash('error', 'Nama lengkap tidak valid');
    header('Location: profile.php'); 
    exit;
}

// Cek username unik
$stmt = $konek->prepare('SELECT id FROM users WHERE username = ? AND id != ? LIMIT 1');
$stmt->bind_param('si', $username, $userId);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    flash('error', 'Username sudah digunakan oleh pengguna lain');
    header('Location: profile.php'); 
    exit;
}
$stmt->close();

$uploadedFilename = null;
if (!empty($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['profile_photo'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        flash('error', 'Terjadi kesalahan saat upload file');
        header('Location: profile.php'); 
        exit;
    }
    if ($file['size'] > 2 * 1024 * 1024) { // Maks 2MB
        flash('error', 'File terlalu besar (max 2MB)');
        header('Location: profile.php'); 
        exit;
    }

    // Validasi MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed = ['image/jpeg' => '.jpg', 'image/png' => '.png'];
    if (!array_key_exists($mime, $allowed)) {
        flash('error', 'Format file tidak didukung (hanya JPG/PNG)');
        header('Location: profile.php'); 
        exit;
    }

    $ext = $allowed[$mime];
    $uploadedFilename = bin2hex(random_bytes(16)) . $ext;

    // Folder tujuan upload ke assets/images/profile/
    $uploadDir = __DIR__ . '/../../assets/images/profile/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $dest = $uploadDir . $uploadedFilename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        flash('error', 'Gagal menyimpan file');
        header('Location: profile.php'); 
        exit;
    }

    // Hapus foto lama jika ada
    $stmt = $konek->prepare('SELECT profile_photo FROM users WHERE id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $old = $result->fetch_assoc()['profile_photo'] ?? null;
    $stmt->close();

    if ($old) {
        $oldPath = $uploadDir . $old;
        if (is_file($oldPath)) {
            @unlink($oldPath);
        }
    }
}

// Simpan perubahan profil ke database
if ($uploadedFilename) {
    $stmt = $konek->prepare('UPDATE users SET username = ?, nama_lengkap = ?, no_hp = ?, profile_photo = ? WHERE id = ?');
    $stmt->bind_param('ssssi', $username, $nama_lengkap, $no_hp, $uploadedFilename, $userId);
} else {
    $stmt = $konek->prepare('UPDATE users SET username = ?, nama_lengkap = ?, no_hp = ? WHERE id = ?');
    $stmt->bind_param('sssi', $username, $nama_lengkap, $no_hp, $userId);
}

$stmt->execute();
$stmt->close();

// ✅ Update session agar navbar langsung tampil data baru
$_SESSION['username'] = $username;
if ($uploadedFilename) {
    $_SESSION['profile_photo'] = $uploadedFilename;
}

flash('success', 'Profil berhasil diperbarui');
header('Location: profile.php');
exit;
?>
