<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    // Redirect ke login jika bukan admin
    header('Location: ../../login/login.php');
    exit();
}

// 2. Koneksi ke database
include '../../../database/konek.php';

// 3. Proses data jika form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Ambil data dari form
    $username = $_POST['username'];
    $email = $_POST['email'];
    $userId = $_SESSION['user_id']; // Asumsi Anda menyimpan ID user di session

    // Validasi data (sangat disarankan)
    if (empty($username) || empty($email)) {
        // Simpan pesan error di session untuk ditampilkan nanti
        $_SESSION['error'] = "Username dan Email tidak boleh kosong.";
        header('Location: index.php?page=admin_profile');
        exit();
    }

    // 4. Query untuk update data di database
    // Ganti 'users' dengan nama tabel Anda
    $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$username, $email, $userId])) {
        // Jika berhasil, update session username
        $_SESSION['username'] = $username;
        $_SESSION['success'] = "Profile berhasil diperbarui!";
    } else {
        $_SESSION['error'] = "Gagal memperbarui profile.";
    }

    // 5. Redirect kembali ke halaman profile
    // Baris ini akan berhasil karena belum ada output sebelumnya
    header('Location: index.php?page=admin_profile');
    exit();
}