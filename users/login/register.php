
<?php
// Mulai session di paling atas
session_start();

// Proses registrasi jika form dikirim
if (isset($_POST['register'])) {
    include "../../database/konek.php";

    // Ambil data dari form
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $no_hp = $_POST['no_hp'];

    // Generate username dari email
    $username = explode('@', $email)[0];

    // Cek username unik dengan prepared statement
    $cek_user = $konek->prepare("SELECT * FROM users WHERE username = ?");
    $cek_user->bind_param("s", $username);
    $cek_user->execute();
    if ($cek_user->get_result()->num_rows > 0) {
        $username = $username . rand(100, 999);
    }

    // Cek email sudah terdaftar dengan prepared statement
    $cek_email = $konek->prepare("SELECT * FROM users WHERE email = ?");
    $cek_email->bind_param("s", $email);
    $cek_email->execute();
    if ($cek_email->get_result()->num_rows > 0) {
        $_SESSION['error_message'] = "Email sudah terdaftar!";
        header('Location: register.php');
        exit;
    } else {
        // Simpan ke database dengan prepared statement
        $simpan = $konek->prepare("INSERT INTO users (username, password, email, nama_lengkap, no_hp, role) 
                                   VALUES (?, ?, ?, ?, ?, 'user')");
        $simpan->bind_param("sssss", $username, $password, $email, $nama, $no_hp);

        if ($simpan->execute()) {
            // Set notifikasi sukses dan redirect ke login
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
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100" 
      style="background: url('../../assets/images/bg/padar4.jpg') no-repeat center center fixed; background-size: cover;">
    <div class="card shadow-lg border-0" 
         style="max-width: 450px; width: 100%; background: hsla(199, 100%, 89%, 0.63); backdrop-filter: blur(8px); border-radius: 15px;">
        <div class="card-body p-4">
            
            <h3 class="text-center mb-4 fw-bold">
                <i class="fas fa-user-plus me-2 text-primary"></i>Daftar Akun Baru
            </h3>

            <!-- Tampilkan notifikasi di sini -->
            <?php include '../../includes/alerts.php'; ?>

            <!-- Form Registrasi -->
            <form action="register.php" method="post">
                <!-- Nama -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Lengkap</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required autofocus>
                    </div>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter" required minlength="6">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- No HP -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">No HP</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="text" name="no_hp" class="form-control" placeholder="08xx-xxxx-xxxx" required>
                    </div>
                </div>

                <!-- Tombol -->
                <button type="submit" name="register" class="btn btn-primary w-100 fw-bold">
                    <i class="fas fa-user-plus me-2"></i> Daftar Sekarang
                </button>
            </form>

            <!-- Login link -->
            <p class="text-center mt-3 mb-0">
                Sudah punya akun? <a href="login.php" class="fw-semibold text-decoration-none">Login di sini</a>
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>