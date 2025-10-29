<?php
// 🔧 Debug (hapus di produksi)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
  <title>Login Pengguna | Labuan Bajo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>

<body class="bg-light d-flex align-items-center justify-content-center vh-100">

  <div class="container">
    <div class="card shadow-lg border-0 mx-auto" style="max-width: 420px;">
      <div class="card-body p-4">
        <div class="text-center mb-4">
          <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
            <i class="bi bi-person-fill fs-2"></i>
          </div>
          <h4 class="fw-bold">Login Pengguna</h4>
          <p class="text-muted mb-0">Masuk ke akun Labuan Bajo Anda</p>
        </div>

        <?php include '../../includes/alerts.php'; ?>

        <form method="POST" autocomplete="off">
          <div class="mb-3">
            <label class="form-label fw-semibold">Username atau Email</label>
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
              <input type="text" name="username" class="form-control" placeholder="Masukkan username atau email" required autofocus>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Password</label>
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
              <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter" required minlength="6">
              <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>

          <button type="submit" class="btn btn-primary w-100 fw-semibold" name="login">Login</button>
        </form>

        <div class="text-center mt-3">
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
