<?php
// admin/admin_posko.php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = 'Akses ditolak! Harus login sebagai admin pusat.';
    header('Location: login/login.php'); // sesuaikan path jika perlu
    exit;
}

include '../database/konek.php'; // sesuai struktur: /database/konek.php

// handle create posko
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_posko'])) {
    $username = trim($_POST['username']);
    $password_plain = $_POST['password'];
    $nama = trim($_POST['nama']);
    $lokasi = trim($_POST['lokasi']);

    if (empty($username) || empty($password_plain) || empty($nama) || empty($lokasi)) {
        $error = "Semua field harus diisi.";
    } else {
        // hash password
        $password_hash = password_hash($password_plain, PASSWORD_BCRYPT);
        // insert into users with role = 'posko'
        $ins = $conn->prepare("INSERT INTO users (username, password, email, nama_lengkap, no_hp, role, lokasi, created_at) VALUES (?, ?, ?, ?, ?, 'posko', ?, NOW())");
        // email & no_hp optional
        $email = $_POST['email'] ?? null;
        $no_hp = $_POST['no_hp'] ?? null;
        $ins->bind_param('ssssss', $username, $password_hash, $email, $nama, $no_hp, $lokasi);
        if ($ins->execute()) {
            $success = "Akun posko berhasil dibuat.";
        } else {
            $error = "Gagal membuat akun posko: " . $conn->error;
        }
    }
}

// fetch daftar posko
$res = $conn->query("SELECT id, username, nama_lengkap, lokasi, created_at FROM users WHERE role='posko' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Kelola Admin Posko</title>
  <link rel="stylesheet" href="../includes/bootstrap.css">
</head>
<body>
<div class="container mt-4">
  <h2>Kelola Admin Posko</h2>
  <?php if(!empty($error)): ?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>
  <?php if(!empty($success)): ?><div class="alert alert-success"><?=htmlspecialchars($success)?></div><?php endif; ?>

  <form method="post" class="row g-2 mb-3">
    <input type="hidden" name="create_posko" value="1">
    <div class="col-md-2"><input name="username" class="form-control" placeholder="username" required></div>
    <div class="col-md-2"><input name="password" type="password" class="form-control password-field" placeholder="password" required></div>
    <div class="col-md-3"><input name="nama" class="form-control" placeholder="nama lengkap" required></div>
    <div class="col-md-3"><input name="lokasi" class="form-control" placeholder="lokasi (ex: Labuan Bajo)" required></div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Buat Posko</button></div>
  </form>

  <table class="table table-striped">
    <thead><tr><th>ID</th><th>Username</th><th>Nama</th><th>Lokasi</th><th>Created</th></tr></thead>
    <tbody>
    <?php while($r = $res->fetch_assoc()): ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td><?= htmlspecialchars($r['username']) ?></td>
        <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
        <td><?= htmlspecialchars($r['lokasi']) ?></td>
        <td><?= htmlspecialchars($r['created_at']) ?></td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>

<script>
// JS: toggle tampilkan password untuk elemen dengan class 'password-field'
document.addEventListener('DOMContentLoaded', function(){
  var pwFields = document.querySelectorAll('.password-field');
  pwFields.forEach(function(f, idx){
    var wrap = document.createElement('div');
    wrap.style.marginTop = '6px';
    var cb = document.createElement('input');
    cb.type = 'checkbox'; cb.id = 'showpw_admin_'+idx;
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
