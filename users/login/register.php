<?php include '../../includes/boot.php'; ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100" 
      style="background: url('../../assets/images/hero/padarhd.avif') no-repeat center center fixed; background-size: cover;">
    <div class="card shadow-lg border-0" 
         style="max-width: 450px; width: 100%; background: rgba(255,255,255,0.85); backdrop-filter: blur(8px); border-radius: 15px;">
        <div class="card-body p-4">
            
            <h3 class="text-center mb-4 fw-bold">
                <i class="fas fa-user-plus me-2 text-primary"></i>Daftar Akun Baru
            </h3>

            <!-- Pesan sukses -->
            <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Pendaftaran berhasil! Silakan login.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

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
                    <label class="form-label fw-semibold">No HP (Opsional)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="text" name="no_hp" class="form-control" placeholder="08xx-xxxx-xxxx">
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

<?php
if (isset($_POST['register'])) {
    include "../../database/konek.php";

    // Ambil data dari form (sesuai dengan name di HTML)
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $no_hp = $_POST['no_hp'];

    // Generate username dari email
    $username = explode('@', $email)[0];

    // Cek username unik
    $cek_user = $konek->query("SELECT * FROM users WHERE username = '$username'");
    if ($cek_user->num_rows > 0) {
        $username = $username . rand(100, 999);
    }

    // Cek email sudah terdaftar
    $cek_email = $konek->query("SELECT * FROM users WHERE email = '$email'");
    if ($cek_email->num_rows > 0) {
        echo "<script>alert('Email sudah terdaftar!'); window.location='register.php';</script>";
    } else {
        // Simpan ke database dengan role 'admin'
        $simpan = $konek->query("INSERT INTO users (username, password, email, nama_lengkap, no_hp, role) 
                                VALUES ('$username', '$password', '$email', '$nama', '$no_hp', 'user')");

        if ($simpan) {
            echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
        } else {
            echo "Error: " . $konek->error;
            echo "<script>alert('Registrasi gagal!'); window.location='register.php';</script>";
        }
    }
}
?>