<?php include '../boot.php'; ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>

<body style="background: url('../../assets/images/bg/padar4.jpg') no-repeat center center fixed; background-size: cover; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; min-height: 100vh;">
    <div class="container" style="max-width: 500px; margin: 100px auto;">
        <div style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border-radius: 20px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3); border: 1px solid rgba(255, 255, 255, 0.18); padding: 30px;">
            <h3 style="color: #fff; font-weight: 700; margin-bottom: 25px; text-align: center; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);">
                <i class="fas fa-user-plus me-2"></i>Daftar Akun Baru
            </h3>

            <!-- Pesan Sukses -->
            <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <div style="background-color: #d4edda; border-color: #c3e6cb; color: #155724; border-radius: 10px; padding: 15px; margin-bottom: 20px; position: relative;">
                    <i class="fas fa-check-circle me-2"></i>Pendaftaran berhasil! Silakan login.
                    <button type="button" style="position: absolute; top: 10px; right: 10px; background: transparent; border: none; font-size: 1.2rem; cursor: pointer;" onclick="this.parentElement.style.display='none';">&times;</button>
                </div>
            <?php endif; ?>

            <!-- Form Registrasi -->
            <form action="register.php" method="post">
                <div style="margin-bottom: 15px;">
                    <label style="font-weight: 600; color: #fff; margin-bottom: 5px; display: block;">Nama Lengkap</label>
                    <div style="display: flex; align-items: center;">
                        <span style="background: rgba(255, 255, 255, 0.2); border: none; color: #fff; padding: 10px 15px; border-radius: 10px 0 0 10px;"><i class="fas fa-user"></i></span>
                        <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required
                            style="background: rgba(255, 255, 255, 0.7); border: none; border-radius: 0 10px 10px 0; padding: 12px 15px; width: 100%;">
                    </div>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="font-weight: 600; color: #fff; margin-bottom: 5px; display: block;">Email</label>
                    <div style="display: flex; align-items: center;">
                        <span style="background: rgba(255, 255, 255, 0.2); border: none; color: #fff; padding: 10px 15px; border-radius: 10px 0 0 10px;"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="nama@email.com" required
                            style="background: rgba(255, 255, 255, 0.7); border: none; border-radius: 0 10px 10px 0; padding: 12px 15px; width: 100%;">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-white">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
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
                            toggleInput.classList.remove('fa-eye-slash');
                            toggleIcon.classList.add('fa-eye');
                        }
                    }
                </script>

                <div style="margin-bottom: 20px;">
                    <label style="font-weight: 600; color: #fff; margin-bottom: 5px; display: block;">No HP (Opsional)</label>
                    <div style="display: flex; align-items: center;">
                        <span style="background: rgba(255, 255, 255, 0.2); border: none; color: #fff; padding: 10px 15px; border-radius: 10px 0 0 10px;"><i class="fas fa-phone"></i></span>
                        <input type="text" name="no_hp" class="form-control" placeholder="08xx-xxxx-xxxx"
                            style="background: rgba(255, 255, 255, 0.7); border: none; border-radius: 0 10px 10px 0; padding: 12px 15px; width: 100%;">
                    </div>
                </div>

                <button type="submit" name="register" style="background: linear-gradient(45deg, #4e73df, #36b9cc); border: none; border-radius: 10px; padding: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: white; width: 100%; margin-bottom: 15px; cursor: pointer; transition: all 0.3s;">
                    <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                </button>
            </form>

            <div style="text-align: center; margin-top: 20px;">
                <p style="color: #fff; margin: 0;">Sudah punya akun?
                    <a href="login.php" style="color: #fff; text-decoration: none; font-weight: 600;">Login di sini</a>
                </p>
            </div>
        </div>
    </div>
</body>

</html>

<?php
if (isset($_POST['register'])) {
    include "../../database/konek.php";

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
                                VALUES ('$username', '$password', '$email', '$nama', '$no_hp', 'admin')");

        if ($simpan) {
            echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
        } else {
            echo "Error: " . $konek->error;
            echo "<script>alert('Registrasi gagal!'); window.location='register.php';</script>";
        }
    }
}
?>