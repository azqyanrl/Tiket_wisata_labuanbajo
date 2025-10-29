<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Jika admin sudah login, redirect ke dashboard
if (isset($_SESSION['username']) && $_SESSION['role'] == 'admin') {
    header('Location: ../halaman/index.php');
    exit;
}

// Proses login jika form dikirim
if (isset($_POST['login'])) {
    include "../../database/konek.php";

    $username = $_POST['username'];
    $password = $_POST['password'];

    $log = $konek->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND role = 'admin'");
    $log->bind_param("ss", $username, $username);
    $log->execute();
    $result = $log->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        if (password_verify($password, $data['password'])) {
            $_SESSION['user_id']  = $data['id'];
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
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Admin | Labuan Bajo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="card shadow">
          <div class="card-body p-4">
            <div class="text-center mb-4">
              <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:70px;height:70px;">
                <i class="bi bi-person-gear fs-2"></i>
              </div>
              <h4 class="mb-1">Login Admin</h4>
              <p class="text-muted small mb-0">Masuk ke akun admin Anda</p>
            </div>

            <!-- Alert (notifikasi) -->
            <?php include '../../includes/alerts.php'; ?>

            <form method="POST" action="">
              <div class="mb-3">
                <label for="username" class="form-label">Username atau Email</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-person"></i></span>
                  <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan username atau email" required autofocus>
                </div>
              </div>

              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-lock"></i></span>
                  <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required>
                  <button class="input-group-text" type="button" id="togglePassword">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
              </div>

              <div class="d-grid">
                <button type="submit" class="btn btn-danger" name="login">Login sebagai Admin</button>
              </div>
            </form>

            <hr class="my-4">
            <div class="text-center small">
              <p class="text-muted mb-2">Login sebagai pengguna lain?</p>
              <a href="../../users/login/login.php" class="text-decoration-none me-2">Login User</a> |
              <a href="../../posko/login/login.php" class="text-decoration-none ms-2">Login Posko</a>
            </div>

          </div>
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
