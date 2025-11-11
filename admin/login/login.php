<?php
// 🔧 Mode debug otomatis
if (
    in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) ||
    str_contains($_SERVER['HTTP_HOST'], 'localhost')
) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// ✅ Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika admin sudah login, arahkan ke dashboard
if (isset($_SESSION['username']) && $_SESSION['role'] === 'admin') {
    header('Location: ../halaman/index.php');
    exit;
}

// Proses login
if (isset($_POST['login'])) {
    include "../../database/konek.php";

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $log = $konek->prepare("
        SELECT * FROM users 
        WHERE (username = ? OR email = ?) AND role = 'admin'
    ");
    $log->bind_param("ss", $username, $username);
    $log->execute();
    $result = $log->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();

        if (password_verify($password, $data['password'])) {
            // ✅ Simpan session dengan key konsisten
            $_SESSION['id']       = $data['id'];        // ⚡ perbaikan penting
            $_SESSION['username'] = $data['username'];
            $_SESSION['role']     = $data['role'];

            $_SESSION['success_message'] = "Login berhasil! Selamat datang, Admin.";
            header('Location: ../halaman/index.php');
            exit;
        } else {
            $_SESSION['error_message'] = "Password Admin salah!";
        }
    } else {
        $_SESSION['error_message'] = "Kredensial Admin tidak valid!";
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
    <title>Login Admin | Labuan Bajo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                        url('../../assets/images/bg/padar3.jpg') no-repeat center center;
            background-size: cover;
            min-height: 100vh;
        }
        .login-container { max-width: 900px; }
        .branding-section {
            background-color: rgba(220,53,69,0.85);
        }
        .btn-theme {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-theme:hover {
            background-color: #bb2d3b;
            border-color: #b02a37;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
    <div class="container login-container">
        <div class="row shadow-lg rounded-3 overflow-hidden">
            <!-- Branding -->
            <div class="col-lg-6 branding-section p-5 d-flex flex-column justify-content-center text-white">
                <div class="text-center mb-4">
                    <i class="bi bi-person-gear fs-1"></i>
                    <h2 class="fw-bold mt-3">Portal Admin</h2>
                    <p class="mb-0">Wisata Labuan Bajo</p>
                </div>
                <p class="text-center">Pusat kendali utama untuk mengelola seluruh sistem pariwisata.</p>
            </div>

            <!-- Form -->
            <div class="col-lg-6 bg-white p-5">
                <h3 class="fw-bold text-center mb-4">Login sebagai Admin</h3>

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
                <div class="text-center small">
                    <p class="text-muted mb-2">Login sebagai pengguna lain?</p>
                    <a href="../../users/login/login.php" class="text-decoration-none me-2">Login User</a> |
                    <a href="../../posko/halaman/login/login.php" class="text-decoration-none ms-2">Login Posko</a>
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
