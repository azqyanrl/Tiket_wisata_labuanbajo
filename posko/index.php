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
      background: #0a0e27;
    }
    
    .hero-section {
      min-height: 100vh;
      background: linear-gradient(135deg, #0a0e27 0%, #1a1f3a 50%, #2d1b69 100%);
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .stars {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
    }
    
    .star {
      position: absolute;
      width: 2px;
      height: 2px;
      background: white;
      border-radius: 50%;
      animation: twinkle 3s infinite;
    }
    
    @keyframes twinkle {
      0%, 100% { opacity: 0; }
      50% { opacity: 1; }
    }
    
    .floating-orb {
      position: absolute;
      border-radius: 50%;
      filter: blur(80px);
      opacity: 0.4;
      animation: float 20s infinite ease-in-out;
    }
    
    .orb-1 {
      width: 400px;
      height: 400px;
      background: linear-gradient(45deg, #667eea, #764ba2);
      top: -200px;
      right: -200px;
    }
    
    .orb-2 {
      width: 300px;
      height: 300px;
      background: linear-gradient(45deg, #f093fb, #f5576c);
      bottom: -150px;
      left: -150px;
      animation-delay: 5s;
    }
    
    .orb-3 {
      width: 250px;
      height: 250px;
      background: linear-gradient(45deg, #4facfe, #00f2fe);
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      animation-delay: 10s;
    }
    
    @keyframes float {
      0%, 100% { transform: translate(0, 0) scale(1); }
      25% { transform: translate(30px, -50px) scale(1.05); }
      50% { transform: translate(-20px, 30px) scale(0.95); }
      75% { transform: translate(40px, 20px) scale(1.02); }
    }
    
    .content-wrapper {
      position: relative;
      z-index: 10;
      text-align: center;
      max-width: 800px;
      padding: 0 20px;
    }
    
    .logo-container {
      margin-bottom: 2rem;
      position: relative;
      display: inline-block;
    }
    
    .logo-bg {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 150px;
      height: 150px;
      background: linear-gradient(45deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
      border-radius: 50%;
      filter: blur(20px);
    }
    
    .logo-icon {
      position: relative;
      z-index: 2;
      width: 100px;
      height: 100px;
      background: linear-gradient(135deg, #667eea, #764ba2);
      border-radius: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto;
      box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
      transition: all 0.3s ease;
    }
    
    .logo-icon:hover {
      transform: translateY(-5px) rotate(5deg);
      box-shadow: 0 25px 50px rgba(102, 126, 234, 0.4);
    }
    
    .title {
      font-size: 3rem;
      font-weight: 800;
      margin-bottom: 0.5rem;
      background: linear-gradient(135deg, #ffffff 0%, #a8b2d1 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .subtitle {
      font-size: 1.2rem;
      color: #8892b0;
      margin-bottom: 3rem;
    }
    
    .login-card {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 20px;
      padding: 2.5rem;
      margin-bottom: 3rem;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    }
    
    .login-btn {
      background: linear-gradient(135deg, #667eea, #764ba2);
      border: none;
      color: white;
      padding: 1rem 3rem;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 50px;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      transition: all 0.3s ease;
      text-decoration: none;
      box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    }
    
    .login-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
      color: white;
    }
    
    .features {
      display: flex;
      justify-content: center;
      gap: 2rem;
      margin-bottom: 3rem;
      flex-wrap: wrap;
    }
    
    .feature-item {
      text-align: center;
      transition: all 0.3s ease;
    }
    
    .feature-item:hover {
      transform: translateY(-5px);
    }
    
    .feature-icon-wrapper {
      width: 70px;
      height: 70px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
      transition: all 0.3s ease;
    }
    
    .feature-item:hover .feature-icon-wrapper {
      background: linear-gradient(135deg, #667eea, #764ba2);
      border-color: transparent;
      transform: rotate(5deg) scale(1.1);
    }
    
    .feature-icon {
      font-size: 1.5rem;
      color: #a8b2d0;
      transition: all 0.3s ease;
    }
    
    .feature-item:hover .feature-icon {
      color: white;
    }
    
    .feature-label {
      color: #8892b0;
      font-size: 0.9rem;
      font-weight: 500;
    }
    
    .footer-links {
      display: flex;
      justify-content: center;
      gap: 2rem;
      color: #64748b;
      font-size: 0.9rem;
    }
    
    .footer-links a {
      color: #64748b;
      text-decoration: none;
      transition: all 0.3s ease;
      position: relative;
    }
    
    .footer-links a::after {
      content: '';
      position: absolute;
      bottom: -2px;
      left: 0;
      width: 0;
      height: 2px;
      background: linear-gradient(135deg, #667eea, #764ba2);
      transition: width 0.3s ease;
    }
    
    .footer-links a:hover {
      color: #a8b2d0;
    }
    
    .footer-links a:hover::after {
      width: 100%;
    }
    
    @media (max-width: 768px) {
      .title {
        font-size: 2rem;
      }
      
      .features {
        gap: 1rem;
      }
      
      .footer-links {
        flex-direction: column;
        gap: 0.5rem;
      }
    }
  </style>
</head>
<body>
  <section class="hero-section">
    <!-- Animated Background -->
    <div class="stars" id="stars"></div>
    <div class="floating-orb orb-1"></div>
    <div class="floating-orb orb-2"></div>
    <div class="floating-orb orb-3"></div>
    
    <!-- Main Content -->
    <div class="content-wrapper">
      <!-- Logo -->
      <div class="logo-container" data-aos="fade-down">
        <div class="logo-bg"></div>
        <div class="logo-icon">
          <i class="bi bi-building-gear text-white fs-1"></i>
        </div>
      </div>
      
      <!-- Title -->
      <h1 class="title" data-aos="fade-up" data-aos-delay="100">Sistem Posko</h1>
      <p class="subtitle" data-aos="fade-up" data-aos-delay="200">Labuan Bajo • Digital Management System</p>
      
      <!-- Login Card -->
      <div class="login-card" data-aos="fade-up" data-aos-delay="300">
        <p class="text-light mb-4">Pilih akses sesuai dengan posko Anda</p>
        <a href="halaman/login/login.php" class="login-btn">
          <i class="bi bi-shield-lock-fill"></i>
          <span>Masuk ke Sistem</span>
        </a>
      </div>
      
      <!-- Features -->
      <div class="features" data-aos="fade-up" data-aos-delay="400">
        <div class="feature-item">
          <div class="feature-icon-wrapper">
            <i class="bi bi-patch-check-fill feature-icon"></i>
          </div>
          <div class="feature-label">Verifikasi</div>
        </div>
        <div class="feature-item">
          <div class="feature-icon-wrapper">
            <i class="bi bi-search feature-icon"></i>
          </div>
          <div class="feature-label">Pencarian</div>
        </div>
        <div class="feature-item">
          <div class="feature-icon-wrapper">
            <i class="bi bi-graph-up-arrow feature-icon"></i>
          </div>
          <div class="feature-label">Laporan</div>
        </div>
      </div>
      
      <!-- Footer Links -->
      <div class="footer-links" data-aos="fade-up" data-aos-delay="500">
        <a href="#">Bantuan</a>
        <a href="#">Kebijakan</a>
        <a href="#">Kontak</a>
      </div>
    </div>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    // Initialize AOS
    AOS.init({
      duration: 1000,
      once: true
    });
    
    // Generate random stars
    const starsContainer = document.getElementById('stars');
    const numberOfStars = 100;
    
    for (let i = 0; i < numberOfStars; i++) {
      const star = document.createElement('div');
      star.className = 'star';
      star.style.left = `${Math.random() * 100}%`;
      star.style.top = `${Math.random() * 100}%`;
      star.style.animationDelay = `${Math.random() * 3}s`;
      starsContainer.appendChild(star);
    }
  </script>
</body>
</html>