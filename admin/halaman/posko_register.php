<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Cek login admin pusat
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak!'); document.location.href='../login/login.php';</script>";
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';

// --- Proses Buat Posko Baru ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_posko'])) {
    $username = trim($_POST['username']);
    $password_plain = $_POST['password'];
    $nama = trim($_POST['nama']);
    $lokasi = trim($_POST['lokasi']);
    $email = trim($_POST['email']);
    $no_hp = trim($_POST['no_hp']);

    if (empty($username) || empty($password_plain) || empty($nama) || empty($lokasi) || empty($email)) {
        $error = "Semua field wajib diisi!";
    } else {
        $password_hash = password_hash($password_plain, PASSWORD_BCRYPT);
        $stmt = $konek->prepare("INSERT INTO users (username, password, email, nama_lengkap, no_hp, role, lokasi, created_at) 
                                 VALUES (?, ?, ?, ?, ?, 'posko', ?, NOW())");
        $stmt->bind_param('ssssss', $username, $password_hash, $email, $nama, $no_hp, $lokasi);
        if ($stmt->execute()) {
            $success = "Akun posko berhasil dibuat.";
        } else {
            $error = "Gagal membuat akun posko: " . $konek->error;
        }
    }
}

// --- Ambil daftar posko ---
 $res = $konek->query("SELECT id, username, nama_lengkap, email, no_hp, lokasi, created_at FROM users WHERE role='posko' ORDER BY created_at DESC");
?>

<div class="container mt-4">
  <h3 class="mb-4"><i class="bi bi-building-gear me-2"></i>Kelola Admin Posko</h3>

  <?php if(!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if(!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <!-- ✅ Form Tambah Posko -->
  <form method="post" class="row g-3 align-items-end mb-4">
    <input type="hidden" name="create_posko" value="1">

    <div class="col-md-2">
      <label class="form-label">Username</label>
      <input name="username" class="form-control" placeholder="Username" required>
    </div>

    <div class="col-md-2">
      <label class="form-label">Password</label>
      <div class="input-group">
        <input name="password" type="password" class="form-control" placeholder="Password" id="passwordInput" required>
        <button type="button" class="btn btn-outline-secondary" id="togglePassword">
          <i class="bi bi-eye" id="toggleIcon"></i>
        </button>
      </div>
    </div>

    <div class="col-md-2">
      <label class="form-label">Nama</label>
      <input name="nama" class="form-control" placeholder="Nama Lengkap" required>
    </div>

    <div class="col-md-2">
      <label class="form-label">Email</label>
      <input name="email" type="email" class="form-control" placeholder="Email Posko" required>
    </div>

    <div class="col-md-2">
      <label class="form-label">No HP</label>
      <input name="no_hp" type="text" class="form-control" placeholder="08xxxxxxxxxx">
    </div>

    <div class="col-md-2">
      <label class="form-label">Lokasi</label>
      <input name="lokasi" class="form-control" placeholder="Contoh: Labuan Bajo" required>
    </div>

    <div class="col-12 d-grid mt-3">
      <button class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Tambah Posko</button>
    </div>
  </form>

  <!-- ✅ Tabel Daftar Posko -->
  <div class="card shadow-sm">
    <div class="card-body">
      <table class="table table-striped align-middle">
        <thead class="table-dark text-center">
          <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Nama Lengkap</th>
            <th>Email</th>
            <th>No HP</th>
            <th>Lokasi</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody class="text-center">
          <?php while($r = $res->fetch_assoc()): ?>
            <tr>
              <td><?= $r['id'] ?></td>
              <td><?= htmlspecialchars($r['username']) ?></td>
              <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
              <td><?= htmlspecialchars($r['email']) ?></td>
              <td><?= htmlspecialchars($r['no_hp']) ?></td>
              <td><?= htmlspecialchars($r['lokasi']) ?></td>
              <td><?= htmlspecialchars($r['created_at']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- 👁️ Script Tampilkan/Sembunyikan Password -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('passwordInput');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (togglePassword && passwordInput && toggleIcon) {
        togglePassword.addEventListener('click', function() {
            // Toggle tipe input antara password dan text
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle ikon
            if (type === 'password') {
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            } else {
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            }
        });
    }
});
</script>