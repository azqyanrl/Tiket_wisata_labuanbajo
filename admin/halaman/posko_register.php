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

// --- Ambil daftar lokasi untuk dropdown ---
$lokasi_res = $konek->query("SELECT id, nama_lokasi FROM lokasi ORDER BY nama_lokasi ASC");

// --- Tambah Posko Baru ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_posko'])) {
    $username = trim($_POST['username']);
    $password_plain = $_POST['password'];
    $nama = trim($_POST['nama']);
    $lokasi_id = intval($_POST['lokasi_id']);
    $email = trim($_POST['email']);
    $no_hp = trim($_POST['no_hp']);

    if (empty($username) || empty($password_plain) || empty($nama) || empty($email) || empty($lokasi_id)) {
        $error = "Semua field wajib diisi!";
    } else {
        $password_hash = password_hash($password_plain, PASSWORD_BCRYPT);
        $stmt = $konek->prepare("
            INSERT INTO users (username, password, email, nama_lengkap, no_hp, role, lokasi_id, created_at) 
            VALUES (?, ?, ?, ?, ?, 'posko', ?, NOW())
        ");
        $stmt->bind_param('sssssi', $username, $password_hash, $email, $nama, $no_hp, $lokasi_id);
        if ($stmt->execute()) {
            $success = "Akun posko berhasil dibuat.";
        } else {
            $error = "Gagal membuat akun posko: " . $konek->error;
        }
    }
}

// --- Update Data Posko ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_posko'])) {
    $id = intval($_POST['id']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $no_hp = trim($_POST['no_hp']);
    $lokasi_id = intval($_POST['lokasi_id']);

    $stmt = $konek->prepare("UPDATE users SET nama_lengkap=?, email=?, no_hp=?, lokasi_id=? WHERE id=? AND role='posko'");
    $stmt->bind_param('sssii', $nama, $email, $no_hp, $lokasi_id, $id);
    if ($stmt->execute()) {
        $success = "Data posko berhasil diperbarui.";
    } else {
        $error = "Gagal memperbarui data: " . $konek->error;
    }
}

// --- Ganti Password Posko ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $id = intval($_POST['id']);
    $new_pass = $_POST['new_password'];
    if (empty($new_pass)) {
        $error = "Password baru tidak boleh kosong.";
    } else {
        $hash = password_hash($new_pass, PASSWORD_BCRYPT);
        $stmt = $konek->prepare("UPDATE users SET password=? WHERE id=? AND role='posko'");
        $stmt->bind_param('si', $hash, $id);
        if ($stmt->execute()) {
            $success = "Password berhasil diganti.";
        } else {
            $error = "Gagal mengganti password: " . $konek->error;
        }
    }
}

// --- Reset Password ke Default ---
if (isset($_GET['reset_pass'])) {
    $id = intval($_GET['reset_pass']);
    $default_hash = password_hash('posko123', PASSWORD_BCRYPT);
    $stmt = $konek->prepare("UPDATE users SET password=? WHERE id=? AND role='posko'");
    $stmt->bind_param('si', $default_hash, $id);
    if ($stmt->execute()) {
        $success = "Password berhasil direset ke 'posko123'.";
    } else {
        $error = "Gagal reset password.";
    }
}

// --- Ambil daftar posko ---
$res = $konek->query("
    SELECT u.id, u.username, u.nama_lengkap, u.email, u.no_hp, l.nama_lokasi, u.created_at 
    FROM users u 
    LEFT JOIN lokasi l ON u.lokasi_id = l.id 
    WHERE u.role='posko' 
    ORDER BY u.created_at DESC
");
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
      <label class="form-label">Nama Lengkap</label>
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
      <select name="lokasi_id" class="form-select" required>
        <option value="">-- Pilih Lokasi --</option>
        <?php
        $lokasi_res->data_seek(0);
        while($l = $lokasi_res->fetch_assoc()): ?>
          <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['nama_lokasi']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="col-12 d-grid mt-3">
      <button class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Tambah Posko</button>
    </div>
  </form>

  <!-- ✅ Tabel Daftar Posko -->
  <div class="card shadow-sm">
    <div class="card-body">
      <table class="table table-striped align-middle text-center">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Nama Lengkap</th>
            <th>Email</th>
            <th>No HP</th>
            <th>Lokasi</th>
            <th>Dibuat</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php while($r = $res->fetch_assoc()): ?>
            <tr>
              <td><?= $r['id'] ?></td>
              <td><?= htmlspecialchars($r['username']) ?></td>
              <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
              <td><?= htmlspecialchars($r['email']) ?></td>
              <td><?= htmlspecialchars($r['no_hp']) ?></td>
              <td><?= htmlspecialchars($r['nama_lokasi'] ?? '-') ?></td>
              <td><?= htmlspecialchars($r['created_at']) ?></td>
              <td>
                <!-- Tombol Edit -->
                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $r['id'] ?>"><i class="bi bi-pencil-square"></i></button>

                <!-- Tombol Ganti Password -->
                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#passModal<?= $r['id'] ?>"><i class="bi bi-key"></i></button>

                <!-- Reset Password -->
                <a href="?reset_pass=<?= $r['id'] ?>" onclick="return confirm('Reset password ke posko123?')" class="btn btn-sm btn-danger"><i class="bi bi-arrow-repeat"></i></a>
              </td>
            </tr>

            <!-- Modal Edit -->
            <div class="modal fade" id="editModal<?= $r['id'] ?>" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="post">
                    <input type="hidden" name="update_posko" value="1">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <div class="modal-header">
                      <h5 class="modal-title">Edit Posko</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3">
                        <label>Nama Lengkap</label>
                        <input name="nama" class="form-control" value="<?= htmlspecialchars($r['nama_lengkap']) ?>" required>
                      </div>
                      <div class="mb-3">
                        <label>Email</label>
                        <input name="email" type="email" class="form-control" value="<?= htmlspecialchars($r['email']) ?>" required>
                      </div>
                      <div class="mb-3">
                        <label>No HP</label>
                        <input name="no_hp" class="form-control" value="<?= htmlspecialchars($r['no_hp']) ?>">
                      </div>
                      <div class="mb-3">
                        <label>Lokasi</label>
                        <select name="lokasi_id" class="form-select" required>
                          <?php
                          $lokasi_res->data_seek(0);
                          while($l = $lokasi_res->fetch_assoc()): ?>
                            <option value="<?= $l['id'] ?>" <?= ($r['nama_lokasi'] == $l['nama_lokasi']) ? 'selected' : '' ?>>
                              <?= htmlspecialchars($l['nama_lokasi']) ?>
                            </option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="submit" class="btn btn-success">Simpan</button>
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Modal Ganti Password -->
            <div class="modal fade" id="passModal<?= $r['id'] ?>" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="post">
                    <input type="hidden" name="change_password" value="1">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <div class="modal-header">
                      <h5 class="modal-title">Ganti Password</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <label>Password Baru</label>
                      <input name="new_password" type="password" class="form-control" placeholder="Masukkan password baru" required>
                    </div>
                    <div class="modal-footer">
                      <button type="submit" class="btn btn-success">Ganti Password</button>
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
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
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      toggleIcon.classList.toggle('bi-eye');
      toggleIcon.classList.toggle('bi-eye-slash');
    });
  }
});
</script>
