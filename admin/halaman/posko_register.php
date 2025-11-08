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

// ✅ Fungsi redirect yang aman
function safeRedirect($filename) {
    if (!headers_sent()) {
        header("Location: $filename");
        exit;
    } else {
        echo "<script>window.location.href='$filename';</script>";
        exit;
    }
}

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

    // ✅ Ambil nama lokasi berdasarkan ID
    $lokasi_stmt = $konek->prepare("SELECT nama_lokasi FROM lokasi WHERE id = ?");
    $lokasi_stmt->bind_param('i', $lokasi_id);
    $lokasi_stmt->execute();
    $lokasi_data = $lokasi_stmt->get_result()->fetch_assoc();
    $nama_lokasi = $lokasi_data['nama_lokasi'] ?? NULL;

    if (empty($username) || empty($password_plain) || empty($nama) || empty($email) || empty($lokasi_id)) {
        $_SESSION['error'] = "Semua field wajib diisi!";
    } else {
        $password_hash = password_hash($password_plain, PASSWORD_BCRYPT);
        $stmt = $konek->prepare("
            INSERT INTO users (username, password, email, nama_lengkap, no_hp, role, lokasi_id, lokasi, created_at) 
            VALUES (?, ?, ?, ?, ?, 'posko', ?, ?, NOW())
        ");
        $stmt->bind_param('sssssis', $username, $password_hash, $email, $nama, $no_hp, $lokasi_id, $nama_lokasi);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Akun posko berhasil dibuat.";
        } else {
            $_SESSION['error'] = "Gagal membuat akun posko: " . $konek->error;
        }
    }
    safeRedirect('index.php?page=posko_register');
}

// --- Update Data Posko ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_posko'])) {
    $id = intval($_POST['id']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $no_hp = trim($_POST['no_hp']);
    $lokasi_id = intval($_POST['lokasi_id']);

    $lokasi_stmt = $konek->prepare("SELECT nama_lokasi FROM lokasi WHERE id = ?");
    $lokasi_stmt->bind_param('i', $lokasi_id);
    $lokasi_stmt->execute();
    $lokasi_data = $lokasi_stmt->get_result()->fetch_assoc();
    $nama_lokasi = $lokasi_data['nama_lokasi'] ?? NULL;

    $stmt = $konek->prepare("UPDATE users SET nama_lengkap=?, email=?, no_hp=?, lokasi_id=?, lokasi=? WHERE id=? AND role='posko'");
    $stmt->bind_param('sssisi', $nama, $email, $no_hp, $lokasi_id, $nama_lokasi, $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Data posko berhasil diperbarui.";
    } else {
        $_SESSION['error'] = "Gagal memperbarui data: " . $konek->error;
    }
    safeRedirect('index.php?page=posko_register');
}

// --- Ganti Password Posko ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $id = intval($_POST['id']);
    $new_pass = $_POST['new_password'];
    if (empty($new_pass)) {
        $_SESSION['error'] = "Password baru tidak boleh kosong.";
    } else {
        $hash = password_hash($new_pass, PASSWORD_BCRYPT);
        $stmt = $konek->prepare("UPDATE users SET password=? WHERE id=? AND role='posko'");
        $stmt->bind_param('si', $hash, $id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Password berhasil diganti.";
        } else {
            $_SESSION['error'] = "Gagal mengganti password: " . $konek->error;
        }
    }
    safeRedirect('index.php?page=posko_register');
}

// --- Reset Password ke Default ---
if (isset($_GET['reset_pass'])) {
    $id = intval($_GET['reset_pass']);
    $default_hash = password_hash('posko123', PASSWORD_BCRYPT);
    $stmt = $konek->prepare("UPDATE users SET password=? WHERE id=? AND role='posko'");
    $stmt->bind_param('si', $default_hash, $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Password berhasil direset ke 'posko123'.";
    } else {
        $_SESSION['error'] = "Gagal reset password.";
    }
    safeRedirect('index.php?page=posko_register');
}

// --- Ambil daftar posko ---
$res = $konek->query("
    SELECT u.id, u.username, u.nama_lengkap, u.email, u.no_hp, u.lokasi, l.nama_lokasi as lokasi_name, u.created_at 
    FROM users u 
    LEFT JOIN lokasi l ON u.lokasi_id = l.id 
    WHERE u.role='posko' 
    ORDER BY u.created_at DESC
");

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="fw-bold text-primary"><i class="bi bi-building-gear me-2"></i>Kelola Admin Posko</h3>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#addPoskoForm">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Posko Baru
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="collapse <?php echo ($error || $success) ? 'show' : ''; ?>" id="addPoskoForm">
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Tambah Posko Baru</h5>
            </div>
            <div class="card-body">
                <form method="post" class="row g-3">
                    <input type="hidden" name="create_posko" value="1">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Username</label>
                        <input name="username" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Password</label>
                        <div class="input-group">
                            <input name="password" type="password" class="form-control" id="passwordInput" required>
                            <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Lengkap</label>
                        <input name="nama" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email</label>
                        <input name="email" type="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">No HP</label>
                        <input name="no_hp" type="text" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Lokasi</label>
                        <select name="lokasi_id" class="form-select" required>
                            <option value="">-- Pilih Lokasi --</option>
                            <?php $lokasi_res->data_seek(0);
                            while($l = $lokasi_res->fetch_assoc()): ?>
                                <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['nama_lokasi']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-12 d-grid mt-3">
                        <button class="btn btn-primary btn-lg"><i class="bi bi-person-plus me-1"></i> Tambah Posko</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ✅ Tabel Posko -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-people me-2"></i>Daftar Admin Posko</h5>
            <span class="badge bg-light text-dark"><?= $res->num_rows ?> Posko</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>No HP</th>
                            <th>Lokasi</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php $no = 1; while($r = $res->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($r['username']) ?></td>
                            <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($r['email']) ?></td>
                            <td><?= htmlspecialchars($r['no_hp']) ?></td>
                            <td><?= htmlspecialchars($r['lokasi'] ?? $r['lokasi_name'] ?? '-') ?></td>
                            <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $r['id'] ?>"><i class="bi bi-pencil-square"></i></button>
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#passModal<?= $r['id'] ?>"><i class="bi bi-key"></i></button>
                                    <a href="?reset_pass=<?= $r['id'] ?>" onclick="return confirm('Reset password ke posko123?')" class="btn btn-sm btn-danger"><i class="bi bi-arrow-repeat"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<?php 
$res->data_seek(0);
while($r = $res->fetch_assoc()): ?>
<div class="modal fade" id="editModal<?= $r['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Posko</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <input type="hidden" name="update_posko" value="1">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Lengkap</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($r['nama_lengkap']) ?>" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($r['email']) ?>" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">No HP</label>
            <input type="text" name="no_hp" value="<?= htmlspecialchars($r['no_hp']) ?>" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Lokasi</label>
            <select name="lokasi_id" class="form-select" required>
              <option value="">-- Pilih Lokasi --</option>
              <?php 
              $lokasi_res->data_seek(0);
              while($l = $lokasi_res->fetch_assoc()): ?>
                <option value="<?= $l['id'] ?>" <?= ($r['lokasi'] == $l['nama_lokasi']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($l['nama_lokasi']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endwhile; ?>

<!-- Modal Ganti Password -->
<?php 
$res->data_seek(0);
while($r = $res->fetch_assoc()): ?>
<div class="modal fade" id="passModal<?= $r['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="bi bi-key me-2"></i>Ganti Password Posko</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <input type="hidden" name="change_password" value="1">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        <div class="modal-body">
          <label class="form-label fw-semibold">Password Baru</label>
          <div class="input-group">
            <input type="password" name="new_password" class="form-control" required>
            <button type="button" class="btn btn-outline-secondary togglePass">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-info text-white"><i class="bi bi-check-circle me-1"></i> Ganti</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endwhile; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // toggle password di form tambah
  const toggle = document.getElementById('togglePassword');
  const pass = document.getElementById('passwordInput');
  const icon = document.getElementById('toggleIcon');
  toggle.addEventListener('click', () => {
    const type = pass.type === 'password' ? 'text' : 'password';
    pass.type = type;
    icon.classList.toggle('bi-eye');
    icon.classList.toggle('bi-eye-slash');
  });

  // toggle password di modal ganti password
  document.querySelectorAll('.togglePass').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.closest('.input-group').querySelector('input');
      const icon = btn.querySelector('i');
      input.type = input.type === 'password' ? 'text' : 'password';
      icon.classList.toggle('bi-eye');
      icon.classList.toggle('bi-eye-slash');
    });
  });
});
</script>
