<?php
session_start();

if (isset($_POST['register'])) {
    include "../../database/konek.php";

    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $no_hp = $_POST['no_hp'];
    $username = explode('@', $email)[0];

    $cek_user = $konek->prepare("SELECT * FROM users WHERE username = ?");
    $cek_user->bind_param("s", $username);
    $cek_user->execute();
    if ($cek_user->get_result()->num_rows > 0) {
        $username .= rand(100, 999);
    }

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
    <title>Register | Labuan Bajo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

    <div class="container" style="max-width: 450px;">
        <div class="card shadow border-0 rounded-4 bg-white bg-opacity-75">
            <div class="card-body p-4">
                <h3 class="text-center mb-4 fw-bold text-primary">
                    <i class="bi bi-person-plus me-2"></i>Daftar Akun Baru
                </h3>

                <?php include '../../includes/alerts.php'; ?>

                <form action="register.php" method="post">
                    <!-- Nama -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text bg-body-secondary"><i class="bi bi-person"></i></span>
                            <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-body-secondary"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-body-secondary"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter" required minlength="6">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- No HP -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">No HP</label>
                        <div class="input-group">
                            <span class="input-group-text bg-body-secondary"><i class="bi bi-phone"></i></span>
                            <input type="text" name="no_hp" class="form-control" placeholder="08xx-xxxx-xxxx" required>
                        </div>
                    </div>

                    <!-- Tombol -->
                    <button type="submit" name="register" class="btn btn-primary w-100 fw-bold">
                        <i class="bi bi-person-plus me-2"></i>Daftar Sekarang
                    </button>
                </form>

                <!-- Login link -->
                <p class="text-center mt-3 mb-0">
                    Sudah punya akun? <a href="login.php" class="fw-semibold text-decoration-none text-primary">Login di sini</a>
                </p>
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
