<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../../../database/konek.php'; 
include '../../../includes/boot.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usernameOrEmail === '' || $password === '') {
        $err = 'Masukkan username atau email dan password.';
    } else {
        $sql = "SELECT * FROM users WHERE (username = ? OR email = ?) AND role = 'posko' LIMIT 1";
        $stmt = $konek->prepare($sql);
        $stmt->bind_param('ss', $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = 'posko';
                $_SESSION['lokasi'] = $row['lokasi'];
                $_SESSION['user_id'] = $row['id'];
                header('Location: ../index.php');
                exit;
            } else {
                $err = 'Password salah.';
            }
        } else {
            $err = 'Akun posko tidak ditemukan.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Posko</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="container">
  <div class="card mx-auto shadow-lg border-0" style="max-width: 420px;">
    <div class="card-body p-4">
      <div class="text-center mb-4">
        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
          <i class="bi bi-building fs-2"></i>
        </div>
        <h4 class="fw-bold">Login Posko</h4>
        <p class="text-muted mb-0">Masuk ke akun posko Anda</p>
      </div>

      <?php if ($err): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($err) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <form method="post" autocomplete="off">
        <div class="mb-3">
          <label for="username" class="form-label fw-semibold">Username atau Email</label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
            <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username atau email" required autofocus>
          </div>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label fw-semibold">Password</label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
            <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
            <button type="button" class="btn btn-outline-secondary" id="togglePassword">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-semibold">Login</button>
      </form>

      <div class="text-center mt-4">
        <small class="text-muted d-block mb-2">Butuh bantuan? Hubungi admin pusat</small>
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
