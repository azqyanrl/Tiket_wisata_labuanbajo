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
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      overflow-x: hidden;
      background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .login-container {
      width: 100%;
      max-width: 900px;
      padding: 20px;
    }
    
    .login-card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-direction: row;
      min-height: 500px;
    }
    
    .left-section {
      flex: 1;
      background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
      padding: 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      color: white;
      text-align: center;
    }
    
    .right-section {
      flex: 1;
      padding: 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    
    .logo-icon {
      width: 80px;
      height: 80px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 20px;
    }
    
    .logo-icon i {
      font-size: 40px;
      color: white;
    }
    
    .title {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 10px;
    }
    
    .subtitle {
      font-size: 1rem;
      opacity: 0.9;
      margin-bottom: 30px;
    }
    
    .features {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-top: 20px;
    }
    
    .feature-item {
      text-align: center;
    }
    
    .feature-icon {
      width: 50px;
      height: 50px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 10px;
    }
    
    .feature-icon i {
      font-size: 20px;
      color: white;
    }
    
    .feature-label {
      font-size: 0.8rem;
      opacity: 0.8;
    }
    
    .form-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: #333;
      margin-bottom: 10px;
    }
    
    .form-subtitle {
      color: #666;
      margin-bottom: 30px;
    }
    
    .login-btn {
      background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
      border: none;
      color: white;
      padding: 12px 30px;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 50px;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      transition: all 0.3s ease;
      text-decoration: none;
      box-shadow: 0 5px 15px rgba(106, 17, 203, 0.3);
      width: 100%;
      justify-content: center;
    }
    
    .login-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(106, 17, 203, 0.4);
      color: white;
    }
    
    .footer-links {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-top: 30px;
    }
    
    .footer-links a {
      color: #666;
      text-decoration: none;
      font-size: 0.9rem;
      transition: color 0.3s ease;
    }
    
    .footer-links a:hover {
      color: #6a11cb;
    }
    
    @media (max-width: 768px) {
      .login-card {
        flex-direction: column;
        min-height: auto;
      }
      
      .left-section {
        padding: 30px 20px;
      }
      
      .right-section {
        padding: 30px 20px;
      }
      
      .features {
        gap: 15px;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-card" data-aos="fade-up">
      <!-- Left Section -->
      <div class="left-section">
        <div class="logo-icon">
          <i class="bi bi-building-gear"></i>
        </div>
        <h1 class="title">Sistem Posko</h1>
        <p class="subtitle">Labuan Bajo • Digital Management System</p>
        
        <div class="features">
          <div class="feature-item">
            <div class="feature-icon">
              <i class="bi bi-patch-check-fill"></i>
            </div>
            <div class="feature-label">Verifikasi</div>
          </div>
          <div class="feature-item">
            <div class="feature-icon">
              <i class="bi bi-search"></i>
            </div>
            <div class="feature-label">Pencarian</div>
          </div>
          <div class="feature-item">
            <div class="feature-icon">
              <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div class="feature-label">Laporan</div>
          </div>
        </div>
      </div>
      
      <!-- Right Section -->
      <div class="right-section">
        <h2 class="form-title">Selamat Datang</h2>
        <p class="form-subtitle">Pilih akses sesuai dengan posko Anda</p>
        
        <a href="halaman/login/login.php" class="login-btn">
          <i class="bi bi-shield-lock-fill"></i>
          <span>Masuk ke Sistem</span>
        </a>
        
        <div class="footer-links">
          <a href="#">Bantuan</a>
          <a href="#">Kebijakan</a>
          <a href="#">Kontak</a>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    // Initialize AOS
    AOS.init({
      duration: 800,
      once: true
    });
  </script>
</body>
</html>