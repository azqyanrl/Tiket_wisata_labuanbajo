<?php
session_start();
include '../../database/konek.php'; 

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $err = 'Masukkan username dan password.';
    } else {
        $sql = "SELECT * FROM users WHERE username = ? AND role = 'posko' LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                // set session
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = 'posko';
                $_SESSION['lokasi'] = $row['lokasi']; // gunakan kolom lokasi
                header('Location: ../posko_dashboard.php');
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
<html>
<head>
  <meta charset="utf-8">
  <title>Login Posko</title>
  <link rel="stylesheet" href="../../includes/bootstrap.css">
</head>
<body>
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card p-4">
        <h4 class="card-title mb-3">Login Posko</h4>
        <?php if($err): ?><div class="alert alert-danger"><?=htmlspecialchars($err)?></div><?php endif; ?>
        <form method="post" autocomplete="off">
          <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required autofocus>
          </div>
          <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control password-field" required>
          </div>
          <button class="btn btn-primary w-100" type="submit">Login</button>
        </form>
        <hr>
        <small><a href="../../admin/login/login.php">Login Admin</a> | <a href="../../users/login/login.php">Login User</a></small>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  var pwFields = document.querySelectorAll('.password-field');
  pwFields.forEach(function(f, idx){
    var wrap = document.createElement('div');
    wrap.style.marginTop = '6px';
    var cb = document.createElement('input');
    cb.type = 'checkbox'; cb.id = 'showpw_posko_'+idx;
    var lbl = document.createElement('label');
    lbl.htmlFor = cb.id; lbl.style.marginLeft='6px'; lbl.style.fontSize='0.9em';
    lbl.textContent = 'Tampilkan password';
    cb.addEventListener('change', function(){ f.type = this.checked ? 'text' : 'password'; });
    wrap.appendChild(cb); wrap.appendChild(lbl);
    f.parentNode.insertBefore(wrap, f.nextSibling);
  });
});
</script>
</body>
</html>
