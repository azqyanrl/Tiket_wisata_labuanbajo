<?php
session_start();

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

if (isset($_POST['register'])) {
    include "../../database/konek.php";

    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $no_hp = $_POST['no_hp'];
    $username = explode('@', $email)[0];

    // Cek apakah username sudah ada
    $cek_user = $konek->prepare("SELECT * FROM users WHERE username = ?");
    $cek_user->bind_param("s", $username);
    $cek_user->execute();
    if ($cek_user->get_result()->num_rows > 0) {
        $username .= rand(100, 999);
    }

    // Cek apakah email sudah terdaftar
    $cek_email = $konek->prepare("SELECT * FROM users WHERE email = ?");
    $cek_email->bind_param("s", $email);
    $cek_email->execute();
    if ($cek_email->get_result()->num_rows > 0) {
        $_SESSION['error_message'] = "Email sudah terdaftar!";
        header('Location: register.php');
        exit;
    } else {
        $simpan = $konek->prepare("INSERT INTO users (username, password, email, nama_lengkap, no_hp, role) 
                                   VALUES (?, ?, ?, ?, ?, 'user')");
        $simpan->bind_param("sssss", $username, $password, $email, $nama, $no_hp);
        if ($simpan->execute()) {
            $_SESSION['success_message'] = "Registrasi berhasil! Silakan login.";
            header('Location: login.php');
            exit;
        } else {
            $_SESSION['error_message'] = "Registrasi gagal! Coba lagi.";
            header('Location: register.php');
            exit;
        }
    }
}

include '../../includes/boot.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Wisata Labuan Bajo</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: url('../../assets/images/bg/padar3.jpg') no-repeat center center fixed;
            background-size: cover;
            position: relative;
            min-height: 100vh;
        }

        body::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.56);
            z-index: 0;
        }

        .container {
            position: relative;
            z-index: 1;
        }

        /* 👁️ Biar tombol mata jelas */
        .btn-toggle-password {
            background-color: #fff !important;
            color: #212529 !important;
            border: 1px solid #ced4da !important;
        }

        .btn-toggle-password:hover {
            background-color: #e9ecef !important;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">

    <div class="container" style="max-width: 500px;">
        <div class="card text-white border-0 shadow-lg" style="background: #cfe8f852;">
            <div class="card-body p-4 p-md-5">
                
                <header class="text-center mb-4">
                    <i class="bi bi-person-plus display-4"></i>
                    <h3 class="fw-bold mt-3">Daftar Akun Baru</h3>
                    <p class="mb-0 opacity-75">Bergabunglah dengan Wisata Labuan Bajo</p>
                </header>

                <?php include '../../includes/alerts.php'; ?>

                <form action="register.php" method="post">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="nama" class="form-control bg-light text-dark" placeholder="Masukkan nama lengkap" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control bg-light text-dark" placeholder="nama@email.com" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="password" class="form-control bg-light text-dark" placeholder="Minimal 6 karakter" required minlength="6">
                            <button class="btn btn-toggle-password" type="button" id="togglePassword" title="Tampilkan / sembunyikan password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="no_hp" class="form-label">No. HP</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-phone"></i></span>
                            <input type="text" name="no_hp" class="form-control bg-light text-dark" placeholder="08xx-xxxx-xxxx" required>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" name="register" class="btn btn-primary btn-lg">
                            <i class="bi bi-person-plus me-2"></i>Daftar Sekarang
                        </button>
                    </div>
                </form>

                <footer class="text-center mt-4">
                    <hr>
                    <p class="mb-0">Sudah punya akun? 
                        <a href="login.php" class="link-light text-decoration-none fw-bold">Login di sini</a>
                    </p>
                </footer>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
