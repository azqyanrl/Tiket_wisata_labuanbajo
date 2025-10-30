<?php
session_start();

// Jika user sudah login, redirect ke dashboard
if (isset($_SESSION['username']) && $_SESSION['role'] === 'posko') {
    header('Location: halman/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem Posko - Labuan Bajo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <style>
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #0d6efd 0%, #6f42c1 100%, #5900ffff 100%);
    }
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="card shadow-lg border-0 mx-auto" style="max-width: 420px;">
      <div class="card-header text-white text-center" style="background: #65b3fc8f;">
        <div class="d-flex flex-column align-items-center">
          <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mb-2" style="width:70px; height:70px;">
            <i class="bi bi-building text-primary fs-2"></i>
          </div>
          <h4 class="fw-bold mb-0">Sistem Posko</h4>
          <small>Labuan Bajo</small>
        </div>
      </div>

      <div class="card-body text-center">
        <p class="text-muted mb-4">Silakan pilih login sesuai Posko Anda</p>

        <div class="d-grid gap-3 mb-4">
          <a href="halaman/login/login.php" class="btn btn-primary">
            <i class="bi bi-person-badge me-2"></i>Login Posko
          </a>
        </div>

        <div class="row text-center mb-4">
          <div class="col">
            <div class="bg-light rounded-circle p-3 mx-auto mb-2">
              <i class="bi bi-ticket-perforated text-primary fs-5"></i>
            </div>
            <small>Verifikasi</small>
          </div>
          <div class="col">
            <div class="bg-light rounded-circle p-3 mx-auto mb-2">
              <i class="bi bi-search text-primary fs-5"></i>
            </div>
            <small>Cari Tiket</small>
          </div>
          <div class="col">
            <div class="bg-light rounded-circle p-3 mx-auto mb-2">
              <i class="bi bi-graph-up text-primary fs-5"></i>
            </div>
            <small>Laporan</small>
          </div>
        </div>

        <p class="text-muted small mb-1">Butuh bantuan? Hubungi admin pusat</p>
        <div class="d-flex justify-content-center gap-3">
          <a href="#" class="text-muted small text-decoration-none">FAQ</a>
          <a href="#" class="text-muted small text-decoration-none">Support</a>
          <a href="#" class="text-muted small text-decoration-none">Contact</a>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
