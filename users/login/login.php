<?php session_start(); ?>

<?php include '../../includes/boot.php'; ?>
<?php include '../../database/konek.php'; ?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login | Labuan Bajo</title>
</head>

<body style="background:url('../../assets/images/hero/padarhd.avif') no-repeat center center fixed; background-size:cover;">

    <!-- Card Login -->
    <div class="container" style="max-width:500px; margin-top:200px;">
        <div class="card" style="background:rgba(255,255,255,0.2); border-radius:15px; padding:25px; box-shadow:0 8px 16px rgba(0,0,0,0.3);align-items:center;backdrop-filter: blur(10px);-webkit-backdrop-filter: blur(10px);">
            <h3 class="text-center mb-4">Login Labuan Bajo</h3>

            <!-- card -->
            <div class="card" style="width: 23rem; backdrop-filter: blur(10px);-webkit-backdrop-filter: blur(10px);background:rgba(255,255,255,0.2);">
                <div class="card-body">

                    <!-- Form Login -->
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
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

<?php
if (isset($_POST['login'])) {
    include "../../database/konek.php";

    // Ambil data dari form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query untuk mendapatkan data user
    $log = $konek->query("SELECT * FROM users WHERE username = '$username' OR email = '$username'");
    $cek = $log->num_rows;

    if ($cek > 0) {
        // Ambil data user
        $data = $log->fetch_assoc();

        // Verifikasi password yang terenkripsi
        if (password_verify($password, $data['password'])) {

            // ✅ Tambahan: Simpan user_id ke session
            $_SESSION['user_id']  = $data['id'];  // <--- Tambahan penting agar tidak logout saat pesan tiket
            $_SESSION['username'] = $data['username'];
            $_SESSION['role']     = $data['role'];

            // Cek apakah user adalah admin atau user
            if ($data['role'] == 'admin') {
                echo "<script>alert('Login admin berhasil!'); window.location='../../admin/index.php';</script>";
            } elseif ($data['role'] == 'user') {
                echo "<script>alert('Login berhasil! Selamat datang'); window.location='../halaman/index.php';</script>";
            } else {
                echo "<script>alert('Role pengguna tidak dikenali!'); window.location='login.php';</script>";
            }

        } else {
            // Password salah
            echo "<script>alert('Password salah!'); window.location='login.php';</script>";
        }
    } else {
        // Username tidak ditemukan
        echo "<script>alert('Username tidak ditemukan!'); window.location='login.php';</script>";
    }
}
?>
