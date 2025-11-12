<?php
require_once __DIR__ . '/../../includes/common.php';
require_once __DIR__ . '/../../includes/boot.php';

require_login();

 $userId = (int) $_SESSION['user_id'];
 $stmt = $konek->prepare('SELECT id, username, email, nama_lengkap, no_hp, profile_photo FROM users WHERE id = ? LIMIT 1');
 $stmt->bind_param('i', $userId);
 $stmt->execute();
 $result = $stmt->get_result();
 $user = $result->fetch_assoc();
 $stmt->close();

if (!$user) {
    session_destroy();
    header('Location: ../login/login.php');
    exit;
}

// Ambil data pemesanan pengguna
 $stmt = $konek->prepare('
    SELECT p.*, t.nama_paket, t.gambar as gambar_tiket, t.durasi, k.nama as nama_kategori 
    FROM pemesanan p 
    JOIN tiket t ON p.tiket_id = t.id 
    LEFT JOIN kategori k ON t.kategori_id = k.id 
    WHERE p.user_id = ? 
    ORDER BY p.created_at DESC
');
 $stmt->bind_param('i', $userId);
 $stmt->execute();
 $result = $stmt->get_result();
 $pemesanan = $result->fetch_all(MYSQLI_ASSOC);
 $stmt->close();

// Hitung statistik
 $stmt = $konek->prepare('SELECT COUNT(*) as total FROM pemesanan WHERE user_id = ?');
 $stmt->bind_param('i', $userId);
 $stmt->execute();
 $result = $stmt->get_result();
 $totalPemesanan = $result->fetch_assoc()['total'];
 $stmt->close();

 $stmt = $konek->prepare('SELECT SUM(total_harga) as total FROM pemesanan WHERE user_id = ? AND status = "selesai"');
 $stmt->bind_param('i', $userId);
 $stmt->execute();
 $result = $stmt->get_result();
 $totalPengeluaran = $result->fetch_assoc()['total'] ?: 0;
 $stmt->close();

 $success = flash('success');
 $error = flash('error');
 $csrf = generate_csrf();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profil Saya - LabuanBajoTrip</title>
  
  <style>
    :root {
      --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --ocean-gradient: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    }

    body {
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      padding-top: 80px;
    }

    .profile-header {
      background: var(--ocean-gradient);
      border-radius: 1rem;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .avatar-img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border: 4px solid rgba(255, 255, 255, 0.9);
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.2);
    }

    .nav-tabs .nav-link {
      border-radius: 0.5rem 0.5rem 0 0;
      border: none;
      margin-right: 0.25rem;
      transition: all 0.3s ease;
    }

    .nav-tabs .nav-link.active {
      background-color: #0d6efd;
      color: white;
    }

    .btn-primary {
      background: var(--primary-gradient);
      border: none;
    }

    .btn-primary:hover {
      background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
      transform: translateY(-2px);
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .form-control:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .card {
      border: none;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
      transition: transform 0.2s;
    }

    .card:hover {
      transform: translateY(-3px);
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .badge-custom {
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(10px);
    }

    .alert {
      border: none;
      border-radius: 0.75rem;
    }

    .alert-success {
      background-color: rgba(25, 135, 84, 0.1);
      color: #0f5132;
    }

    .alert-danger {
      background-color: rgba(220, 53, 69, 0.1);
      color: #842029;
    }

    .input-group-text {
      background-color: #f8f9fa;
      border-right: none;
    }

    .input-group .form-control {
      border-left: none;
    }

    .stats-card {
      transition: transform 0.2s;
    }

    .stats-card:hover {
      transform: translateY(-5px);
    }

    .booking-card {
      transition: all 0.3s ease;
    }

    .booking-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .status-badge {
      font-size: 0.75rem;
      padding: 0.25rem 0.5rem;
    }

    .booking-image {
      height: 180px;
      object-fit: cover;
    }

    /* Tab animation */
    .tab-pane {
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .tab-pane.show.active {
      opacity: 1;
    }

    /* Loading spinner */
    .spinner-container {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 200px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .avatar-img {
        width: 100px;
        height: 100px;
      }
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div class="container mb-5">
  <div class="row justify-content-center">
    <div class="col-lg-10">

      <!-- Alert Messages -->
      <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i>
          <?= e($success) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <?= e($error) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <!-- Profile Header Card -->
      <div class="card profile-header mb-4">
        <div class="card-body text-white p-4">
          <div class="row align-items-center">
            <div class="col-md-3 text-center mb-3 mb-md-0">
              <?php
                $photo = $user['profile_photo'] ?? '';
                $nama = $user['nama_lengkap'] ?: $user['username'] ?: 'U';
                $inisial = strtoupper(substr($nama, 0, 1));
                $path = __DIR__ . '/../../assets/images/profile/' . $photo;
              ?>

              <?php if (!empty($photo) && file_exists($path)): ?>
                <!-- Jika user punya foto profil -->
                <img src="<?= '../../assets/images/profile/' . e($photo) ?>" 
                    alt="Foto Profil" 
                    class="rounded-circle avatar-img">
              <?php else: ?>
                <!-- Jika tidak punya foto profil -->
                <div class="rounded-circle d-inline-flex justify-content-center align-items-center bg-light text-primary fw-bold border"
                    style="width: 120px; height: 120px; font-size: 40px;">
                  <?= e($inisial) ?>
                </div>
              <?php endif; ?>

            </div>
            <div class="col-md-6">
              <h3 class="mb-2"><?= e($user['nama_lengkap']) ?></h3>
              <p class="mb-1"><i class="bi bi-person-circle me-2"></i>@<?= e($user['username']) ?></p>
              <p class="mb-1"><i class="bi bi-envelope me-2"></i><?= e($user['email']) ?></p>
              <?php if ($user['no_hp']): ?>
                <p class="mb-2"><i class="bi bi-telephone me-2"></i><?= e($user['no_hp']) ?></p>
              <?php endif; ?>
              <div>
                <span class="badge badge-custom me-2">
                  <i class="bi bi-star-fill me-1"></i>Member Aktif
                </span>
                <span class="badge bg-success">
                  <i class="bi bi-patch-check-fill me-1"></i>Terverifikasi
                </span>
              </div>
            </div>
            <div class="col-md-3 text-center text-md-end mt-3 mt-md-0">
              <a href="../login/logout.php" class="btn btn-outline-light" onclick="switchToTab('riwayat')">
                <i class="bi bi-box-arrow-right"></i></i>Logout
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="row mb-4 g-3">
        <div class="col-md-4">
          <div class="card stats-card h-100">
            <div class="card-body text-center">
              <i class="bi bi-calendar-check text-primary fs-1 mb-2"></i>
              <h5 class="card-title">Total Pemesanan</h5>
              <p class="card-text text-muted fs-4 fw-bold"><?= $totalPemesanan ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card stats-card h-100">
            <div class="card-body text-center">
              <i class="bi bi-currency-exchange text-success fs-1 mb-2"></i>
              <h5 class="card-title">Total Pengeluaran</h5>
              <p class="card-text text-muted fs-4 fw-bold">Rp <?= number_format($totalPengeluaran, 0, ',', '.') ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card stats-card h-100">
            <div class="card-body text-center">
              <i class="bi bi-geo-alt text-danger fs-1 mb-2"></i>
              <h5 class="card-title">Destinasi Favorit</h5>
              <p class="card-text text-muted fs-4 fw-bold">Labuan Bajo</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Card -->
      <div class="card">
        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs px-3 pt-3" id="profileTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
              <i class="bi bi-person-fill me-2"></i>Informasi Profil
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="riwayat-tab" data-bs-toggle="tab" data-bs-target="#riwayat" type="button" role="tab">
              <i class="bi bi-clock-history me-2"></i>Riwayat Pemesanan
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
              <i class="bi bi-shield-lock me-2"></i>Keamanan
            </button>
          </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content p-4" id="profileTabsContent">
          <!-- Profile Tab -->
          <div class="tab-pane fade" id="profile" role="tabpanel">
            <h5 class="mb-4"><i class="bi bi-person-badge me-2"></i>Informasi Pribadi</h5>
            <form method="POST" action="profile_update.php" enctype="multipart/form-data">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
              
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="username" class="form-label">Username</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-at"></i></span>
                    <input type="text" 
                           class="form-control" 
                           id="username" 
                           name="username" 
                           value="<?= e($user['username']) ?>" 
                           required 
                           maxlength="50">
                  </div>
                </div>
                
                <div class="col-md-6">
                  <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" 
                           class="form-control" 
                           id="nama_lengkap" 
                           name="nama_lengkap" 
                           value="<?= e($user['nama_lengkap']) ?>" 
                           required 
                           maxlength="100">
                  </div>
                </div>

                <div class="col-md-6">
                  <label for="no_hp" class="form-label">Nomor HP</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                    <input type="tel" 
                           class="form-control" 
                           id="no_hp" 
                           name="no_hp" 
                           value="<?= e($user['no_hp']) ?>" 
                           maxlength="15">
                  </div>
                </div>
                
                <div class="col-md-6">
                  <label for="email" class="form-label">Email Login</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           value="<?= e($user['email']) ?>" 
                           disabled>
                  </div>
                  <div class="form-text">Email tidak dapat diubah. Hubungi admin untuk bantuan.</div>
                </div>

                <div class="col-12">
                  <label for="profile_photo" class="form-label">Foto Profil</label>
                  <input type="file" 
                         class="form-control" 
                         id="profile_photo" 
                         name="profile_photo" 
                         accept="image/png, image/jpeg">
                  <div class="form-text">Format: JPG/PNG, Maksimal: 2MB</div>
                </div>
              </div>

              <div class="mt-4 d-flex justify-content-end gap-2">
                <button type="reset" class="btn btn-secondary">
                  <i class="bi bi-arrow-clockwise me-2"></i>Reset
                </button>
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                </button>
              </div>
            </form>
          </div>

          <!-- Riwayat Tab -->
          <div class="tab-pane fade" id="riwayat" role="tabpanel">
            <h5 class="mb-4"><i class="bi bi-clock-history me-2"></i>Riwayat Pemesanan</h5>
            
            <?php if (empty($pemesanan)): ?>
              <div class="alert alert-info" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                Anda belum memiliki pemesanan tiket. <a href="../tiket/tiket.php" class="alert-link">Lihat paket wisata</a> untuk memesan tiket.
              </div>
            <?php else: ?>
              <div class="row g-4">
                <?php foreach ($pemesanan as $booking): ?>
                  <div class="col-md-6">
                    <div class="card booking-card h-100">
                      <img src="<?= $booking['gambar_tiket'] ? '../../assets/images/tiket/' . e($booking['gambar_tiket']) : 'https://picsum.photos/seed/tiket/400/200.jpg' ?>" 
                           class="card-img-top booking-image" 
                           alt="<?= e($booking['nama_paket']) ?>">
                      <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                          <h5 class="card-title"><?= e($booking['nama_paket']) ?></h5>
                          <span class="badge status-badge bg-<?= 
                            $booking['status'] == 'dibayar' ? 'success' : 
                            ($booking['status'] == 'pending' ? 'warning' : 
                            ($booking['status'] == 'selesai' ? 'info' : 'danger')) 
                          ?>">
                            <?= ucfirst($booking['status']) ?>
                          </span>
                        </div>
                        <p class="card-text">
                          <small class="text-muted">
                            <i class="bi bi-tag me-1"></i><?= e($booking['nama_kategori']) ?>
                            <span class="mx-2">•</span>
                            <i class="bi bi-clock me-1"></i><?= e($booking['durasi']) ?>
                          </small>
                        </p>
                        <p class="card-text">
                          <i class="bi bi-calendar-event me-1"></i> <?= date('d M Y', strtotime($booking['tanggal_kunjungan'])) ?>
                          <span class="mx-2">•</span>
                          <i class="bi bi-ticket-perforated me-1"></i> <?= $booking['jumlah_tiket'] ?> tiket
                        </p>
                        <p class="card-text fw-bold">
                          Total: Rp <?= number_format($booking['total_harga'], 0, ',', '.') ?>
                        </p>
                        <div class="d-flex justify-content-between">
                          <small class="text-muted">Kode: <?= e($booking['kode_booking']) ?></small>
                          <a href="detail_destinasi.php?id=<?= $booking['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye me-1"></i>Detail
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Security Tab -->
          <div class="tab-pane fade" id="security" role="tabpanel">
            <h5 class="mb-4"><i class="bi bi-shield-check me-2"></i>Ubah Kata Sandi</h5>
            <form method="POST" action="password.php">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
              
              <div class="mb-3">
                <label for="old_password" class="form-label">Kata Sandi Lama</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                  <input type="password" 
                         class="form-control" 
                         id="old_password" 
                         name="old_password" 
                         required>
                </div>
              </div>

              <div class="mb-3">
                <label for="new_password" class="form-label">Kata Sandi Baru</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-lock"></i></span>
                  <input type="password" 
                         class="form-control" 
                         id="new_password" 
                         name="new_password" 
                         required 
                         minlength="8">
                </div>
                <div class="form-text">Minimal 8 karakter</div>
              </div>

              <div class="mb-4">
                <label for="confirm_password" class="form-label">Konfirmasi Kata Sandi Baru</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                  <input type="password" 
                         class="form-control" 
                         id="confirm_password" 
                         name="confirm_password" 
                         required 
                         minlength="8">
                </div>
              </div>

              <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-warning">
                  <i class="bi bi-arrow-repeat me-2"></i>Ubah Kata Sandi
                </button>
              </div>
            </form>

            <hr class="my-4">

            <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Informasi Keamanan</h6>
            <div class="alert alert-info" role="alert">
              <i class="bi bi-shield-fill-check me-2"></i>
              <strong>Akun Anda dilindungi!</strong> Kami menggunakan enkripsi SSL untuk melindungi data pribadi Anda.
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>


<!-- Custom JavaScript for Tab Persistence -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fungsi untuk menyimpan tab aktif ke sessionStorage
    function saveActiveTab(tabId) {
        sessionStorage.setItem('activeProfileTab', tabId);
    }

    // Fungsi untuk mengaktifkan tab yang tersimpan
    function restoreActiveTab() {
        const savedTab = sessionStorage.getItem('activeProfileTab');
        if (savedTab) {
            const tabButton = document.querySelector(`[data-bs-target="#${savedTab}"]`);
            if (tabButton) {
                // Tunggu Bootstrap siap
                setTimeout(() => {
                    const tab = new bootstrap.Tab(tabButton);
                    tab.show();
                }, 100);
            }
        } else {
            // Jika tidak ada tab tersimpan, aktifkan tab pertama
            const firstTab = document.querySelector('#profile-tab');
            if (firstTab) {
                setTimeout(() => {
                    const tab = new bootstrap.Tab(firstTab);
                    tab.show();
                }, 100);
            }
        }
    }

    // Event listener untuk semua tab
    const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
    tabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', function (e) {
            const targetId = e.target.getAttribute('data-bs-target').replace('#', '');
            saveActiveTab(targetId);
        });
    });

    // Fungsi untuk switch ke tab tertentu (dipanggil dari link)
    window.switchToTab = function(tabId) {
        const tabButton = document.querySelector(`[data-bs-target="#${tabId}"]`);
        if (tabButton) {
            const tab = new bootstrap.Tab(tabButton);
            tab.show();
        }
    }

    // Restore tab aktif saat halaman dimuat
    restoreActiveTab();

    // Password confirmation validation
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (newPassword && confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            if (this.value !== newPassword.value) {
                this.setCustomValidity('Kata sandi tidak cocok!');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // File size validation
    const fileInput = document.getElementById('profile_photo');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.size > 2 * 1024 * 1024) {
                alert('Ukuran file terlalu besar! Maksimal 2MB.');
                this.value = '';
            }
        });
    }

    // Simpan scroll position
    let scrollPosition = sessionStorage.getItem('profileScrollPosition');
    if (scrollPosition) {
        window.scrollTo(0, parseInt(scrollPosition));
        sessionStorage.removeItem('profileScrollPosition');
    }

    // Simpan scroll position sebelum refresh
    window.addEventListener('beforeunload', function() {
        sessionStorage.setItem('profileScrollPosition', window.scrollY);
    });
});
</script>

</body>
</html>