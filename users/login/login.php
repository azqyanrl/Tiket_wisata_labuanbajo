<?php
// 🔧 Debug otomatis: aktif di localhost, mati di server publik
if (
    in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) ||
    str_contains($_SERVER['HTTP_HOST'], 'localhost')
) {
    // Mode pengembangan (development)
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // Mode produksi (production)
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// ✅ Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika user sudah login
if (isset($_SESSION['username']) && $_SESSION['role'] == 'user') {
    header('Location: ../halaman/index.php');
    exit;
}

if (isset($_POST['login'])) {
    include "../../database/konek.php";

    $username = $_POST['username'];
    $password = $_POST['password'];

    $log = $konek->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND role = 'user'");
    $log->bind_param("ss", $username, $username);
    $log->execute();
    $result = $log->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        if (password_verify($password, $data['password'])) {
            $_SESSION['user_id']  = $data['id'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['role']     = $data['role'];
            $_SESSION['success_message'] = "Login berhasil! Selamat datang, " . htmlspecialchars($data['nama_lengkap']);
            header('Location: ../halaman/index.php');
            exit;
        } else {
            $_SESSION['error_message'] = "Password salah!";
        }
    } else {
        $_SESSION['error_message'] = "Username atau Password tidak ditemukan!";
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

include '../../includes/boot.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Labuan Bajo Trip</title>
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('../../assets/images/bg/padar3.jpg') no-repeat center center;
            background-size: cover;
            min-height: 100vh;
        }
        .login-container {
            max-width: 900px;
        }
        .branding-section {
            background-color: rgba(13, 110, 253, 0.85); /* Aksen biru untuk User */
        }
        .btn-theme {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-theme:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
    <div class="container login-container">
        <div class="row shadow-lg rounded-3 overflow-hidden">
            <!-- Branding Section -->
            <div class="col-lg-6 branding-section p-5 d-flex flex-column justify-content-center text-white">
                <div class="text-center mb-4">
                    <i class="bi bi-person-heart fs-1"></i>
                    <h2 class="fw-bold mt-3">Portal Pengunjung</h2>
                    <p class="mb-0">Wisata Labuan Bajo</p>
                </div>
                <p class="text-center">Jelajahi keindahan dan pesona Labuan Bajo dengan akses penuh informasi.</p>
            </div>

            <!-- Form Section -->
            <div class="col-lg-6 bg-white p-5">
                <h3 class="fw-bold text-center mb-4">Login Pengguna</h3>
                
                <?php include '../../includes/alerts.php'; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username atau Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" class="form-control" placeholder="Masukkan username atau email" required autofocus>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-theme btn-lg text-white" name="login">Masuk</button>
                    </div>
                </form>

                <hr class="my-4">
                <div class="text-center">
                    <small class="text-muted">Belum punya akun?</small>
                    <br>
                    <a href="register.php" class="fw-bold text-decoration-none">Register Sekarang</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    </script>
</body>
</html>