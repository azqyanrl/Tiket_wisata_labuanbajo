<?php
// Aktifkan pelaporan error untuk debugging (HAPUS BARIS INI DI PRODUKSI)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mulai session di paling atas, JANGAN ADA SPASI ATAU TEKS SEBELUMNYA
session_start();

// Jika user sudah login, redirect ke halaman user
if (isset($_SESSION['username']) && $_SESSION['role'] == 'user') {
    header('Location: ../halaman/index.php');
    exit;
}

// Proses login jika form dikirim
if (isset($_POST['login'])) {
    include "../../database/konek.php";

    // Ambil data dari form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query untuk mendapatkan data USER saja
    $log = $konek->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND role = 'user'");
    $log->bind_param("ss", $username, $username);
    $log->execute();
    $result = $log->get_result();
    $cek = $result->num_rows;

    if ($cek > 0) {
        // Ambil data user
        $data = $result->fetch_assoc();

        // Verifikasi password
        if (password_verify($password, $data['password'])) {
            // Set session
            $_SESSION['user_id']  = $data['id'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['role']     = $data['role'];

            // Set notifikasi sukses
            $_SESSION['success_message'] = "Login berhasil! Selamat datang, " . htmlspecialchars($data['nama_lengkap']);
            
            // Redirect ke halaman user
            header('Location: ../halaman/index.php');
            exit; // Hentikan eksekusi skrip

        } else {
            // Password salah
            $_SESSION['error_message'] = "Password salah!";
        }
    } else {
        // Username/email tidak ditemukan atau bukan user biasa
        $_SESSION['error_message'] = "Username atau Password tidak ditemukan!";
    }
    
    // Jika login gagal, redirect kembali ke halaman login user
    // Gunakan $_SERVER['PHP_SELF'] untuk redirect ke file itu sendiri
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit; // Hentikan eksekusi skrip
}

// Include file yang diperlukan untuk tampilan
include '../../includes/boot.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Labuan Bajo</title>
</head>
<body style="background:url('../../assets/images/hero/padarhd.avif') no-repeat center center fixed; background-size:cover;">

    <!-- Card Login -->
    <div class="container" style="max-width:500px; margin-top:200px;">
        <div class="card" style="background:rgba(149, 185, 252, 0.2); border-radius:15px; padding:25px; box-shadow:0 8px 16px rgba(0,0,0,0.3);align-items:center;backdrop-filter: blur(10px);-webkit-backdrop-filter: blur(10px);">
            <h3 class="text-center mb-4">Login Pengguna</h3>

            <!-- Tampilkan notifikasi di sini -->
            <?php include '../../includes/alerts.php'; ?>

            <!-- card -->
            <div class="card" style="width: 23rem; backdrop-filter: blur(10px);-webkit-backdrop-filter: blur(10px);background:rgba(255,255,255,0.2);">
                <div class="card-body">
                    <!-- Form Login -->
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Username atau Email</label>
                            <input type="text" name="username" class="form-control" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" name="login">Login</button>
                    </form>

                    <!-- Link ke Register -->
                    <p class="mt-3 text-center">Belum punya akun? <a href="register.php">Register</a></p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>