<?php
session_start();

include_once __DIR__ . '/../database/konek.php';

// jika belum login / bukan posko -> redirect ke posko login
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'posko') {
    header('Location: halaman/login/login.php');
    exit;
}

// baca username & lokasi dari session
$username = $_SESSION['username'];
$lokasi = $_SESSION['lokasi'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Posko — <?= htmlspecialchars($lokasi ?: 'Dashboard') ?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="../includes/bootstrap.css">
  <style>
    /* Simple sidebar layout */
    body { min-height:100vh; }
    .sidebar {
      width: 240px;
      position: fixed;
      top: 0; left: 0;
      height: 100%;
      background: #0d6efd; /* primary */
      color: white;
      padding-top: 1rem;
    }
    .sidebar a { color: rgba(255,255,255,0.95); text-decoration: none; display:block; padding:10px 16px; }
    .sidebar a:hover { background: rgba(255,255,255,0.08); color: #fff; }
    .sidebar .brand { font-weight:700; font-size:1.1rem; padding:0 16px 12px 16px; }
    .content {
      margin-left: 240px;
      padding: 1.25rem;
    }
    @media (max-width: 767px) {
      .sidebar { position:relative; width:100%; height:auto; }
      .content { margin-left:0; padding:0.75rem; }
    }
    .topbar {
      display:flex; justify-content:space-between; align-items:center;
      margin-bottom:1rem;
    }
  </style>
</head>
<body>
  <div class="sidebar d-none d-md-block">
    <div class="brand px-3">Posko Panel</div>
    <div class="px-3 small mb-2">Lokasi: <strong><?= htmlspecialchars($lokasi) ?></strong></div>
    <nav>
      <a href="posko_dashboard.php">🏠 Dashboard</a>
      <a href="verifikasi_tiket.php">🧾 Verifikasi (Cari Kode)</a>
      <a href="posko_reports.php">📊 Laporan (Opsional)</a>
      <a href="login/logout.php">⎋ Logout</a>
    </nav>
    <div style="position:absolute;bottom:1rem;padding-left:16px;font-size:0.85rem;color:rgba(255,255,255,0.8);">
      Login: <?= htmlspecialchars($username) ?>
    </div>
  </div>

  <!-- mobile topbar -->
  <nav class="navbar navbar-expand-md navbar-light bg-light d-md-none">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Posko — <?= htmlspecialchars($lokasi) ?></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#poskoNav" aria-controls="poskoNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="poskoNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="posko_dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="verifikasi_tiket.php">Verifikasi</a></li>
          <li class="nav-item"><a class="nav-link" href="login/logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="content">
    <div class="topbar">
      <div>
        <button class="btn btn-sm btn-outline-secondary d-md-none" onclick="window.location='posko_dashboard.php'">Dashboard</button>
      </div>
      <div>
        <small>Hi, <strong><?= htmlspecialchars($username) ?></strong></small>
      </div>
    </div>
<!-- HALAMAN CONTENT DIMULAI DI SINI -->
