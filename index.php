<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Labuan Bajo Trip - Petualangan Tak Terlupakan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
  <link rel="stylesheet" href="includes/style.css">
</head>
<body>
  <!-- Enhanced Navbar -->
  <nav class="navbar navbar-expand-lg fixed-top" style="background-color: #11111138;">
    <div class="container">
      <a class="navbar-brand" href="#">
        <i class="fas fa-ship me-2"></i>LabuanBajoTrip
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="#">Beranda</a></li>
          <li class="nav-item"><a class="nav-link" href="#about">Tentang</a></li>
          <li class="nav-item"><a class="nav-link" href="#destinations">Paket</a></li>
          <li class="nav-item">
            <a class="nav-link btn-login" href="users/login/login.php">
              <i class="fas fa-user me-2"></i>Login
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Enhanced Hero Section -->
  <section class="hero-section">
    <img src="assets/images/bg/padar3.jpg" alt="Labuan Bajo" class="hero-bg">
    <div class="hero-overlay"></div>
    <div class="container position-relative">
      <div class="row">
        <div class="col-lg-7">
          <div class="hero-content text-white" data-aos="fade-up">
            <h1>Petualangan Luar Biasa di Labuan Bajo</h1>
            <p>Jelajahi surga tersembunyi Indonesia dengan paket wisata terbaik. Nikmati komodo, pantai pink, dan pengalaman tak terlupakan!</p>
            <div class="hero-buttons">
              <a href="#destinations" class="btn btn-hero">
                <i class="fas fa-search me-2"></i>Jelajahi Paket
              </a>
            </div>
            <div class="trust-badges">
          </div>
        </div>
      </div>
    </div>
  </section>


  <!-- Enhanced About Section -->
  <section id="about" class="about-section">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6 mb-4" data-aos="fade-right">
          <div class="about-card">
            <img src="assets/uploads/komodo.jpg" class="about-img w-100" alt="Labuan Bajo">
          </div>
        </div>
        <div class="col-lg-6 mb-4" data-aos="fade-left">
          <div class="section-content">
            <h2 class="fw-bold mb-4">Mengapa Labuan Bajo Menjadi Destinasi Wajib Kunjung?</h2>
            <p class="text-muted mb-4">Labuan Bajo adalah gerbang menuju Taman Nasional Komodo, rumah bagi komodo - reptil terbesar di dunia. Dengan lebih dari 30 pulau eksotis, spot diving kelas dunia, dan pantai-pantai memukau, Labuan Bajo menawarkan petualangan tak terlupakan.</p>
            
            <div class="feature-list mb-4">
              <div class="d-flex align-items-center mb-3">
                <div class="feature-icon-sm me-3">
                  <i class="fas fa-check"></i>
                </div>
                <div>
                  <h6 class="mb-0">Komodo di Habitat Asli</h6>
                  <small class="text-muted">Lihat komodo di alam liar</small>
                </div>
              </div>
              <div class="d-flex align-items-center mb-3">
                <div class="feature-icon-sm me-3">
                  <i class="fas fa-check"></i>
                </div>
                <div>
                  <h6 class="mb-0">Pink Beach Eksklusif</h6>
                  <small class="text-muted">Salah satu dari 7 pink beach di dunia</small>
                </div>
              </div>
              <div class="d-flex align-items-center mb-3">
                <div class="feature-icon-sm me-3">
                  <i class="fas fa-check"></i>
                </div>
                <div>
                  <h6 class="mb-0">Diving Spot Kelas Dunia</h6>
                  <small class="text-muted">Manta Point & Crystal Rock</small>
                </div>
              </div>
            </div>
            
            <a href="#destinations" class="btn btn-primary btn-lg rounded-pill">
              Lihat Paket Wisata <i class="fas fa-arrow-right ms-2"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section class="features-section">
    <div class="container">
      <div class="text-center mb-5" data-aos="fade-up">
        <h2 class="fw-bold mb-3">Mengapa Memilih LabuanBajoTrip?</h2>
        <p class="text-muted fs-5">Kami memberikan pengalaman terbaik untuk liburan impian Anda</p>
      </div>
      <div class="row">
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-ship"></i>
            </div>
            <h4 class="fw-semibold mb-3">Kapal Phinisi Mewah</h4>
            <p class="text-muted">Nikmati kemewahan kapal phinisi berfasilitas lengkap dengan kabin AC, toilet pribadi, dan deck area yang luas.</p>
          </div>
        </div>
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-user-tie"></i>
            </div>
            <h4 class="fw-semibold mb-3">Pemandu Profesional</h4>
            <p class="text-muted">Tim pemandu berpengalaman yang siap membagikan informasi menarik dan memastikan keselamatan Anda.</p>
          </div>
        </div>
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-shield-alt"></i>
            </div>
            <h4 class="fw-semibold mb-3">Garansi Keamanan</h4>
            <p class="text-muted">Standar keselamatan internasional, perlengkapan lengkap, dan asuransi perjalanan untuk setiap tamu.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Wildlife Section -->
  <section class="wildlife-section">
    <div class="container">
      <div class="text-center mb-5" data-aos="fade-up">
        <h2 class="fw-bold mb-3 text-uppercase text-primary">Satwa Taman Nasional Komodo</h2>
        <p class="text-muted fs-5">Keanekaragaman fauna yang dilindungi di kawasan Taman Nasional Komodo</p>
      </div>

      <div class="row justify-content-center g-4">
        <!-- Biawak Komodo -->
        <div class="col-md-6 col-lg-5" data-aos="fade-up" data-aos-delay="100">
          <div class="wildlife-card">
            <img src="assets/images/bg/komodo.jpg" class="wildlife-img w-100" alt="Biawak Komodo">
            <div class="card-body p-4">
              <h4 class="text-center fw-bold wildlife-title">Biawak Komodo</h4>
              <h6 class="text-center fst-italic wildlife-subtitle mb-3">Varanus komodoensis</h6>
              <p class="text-justify">
                <strong>Biawak Komodo</strong> merupakan kadal terbesar yang ada di dunia. Reptil raksasa karnivora ini hidup alami di 
                Taman Nasional Komodo, Pulau Longos, dan sebagian lembah Pulau Flores. 
                Keberadaan dan kelestarian populasi biawak komodo di Taman Nasional Komodo dijaga ketat oleh 
                Balai Taman Nasional Komodo dengan melibatkan masyarakat setempat. 
                Wisatawan yang beruntung dapat mengamati biawak komodo secara langsung hanya dari beberapa resort 
                seperti Resort Loh Buaya (Pulau Rinca), Resort Loh Liang (Pulau Komodo), dan Resort Padar Selatan (Pulau Padar).
              </p>
            </div>
          </div>
        </div>

        <!-- Kakatua Kecil Jambul Kuning -->
        <div class="col-md-6 col-lg-5" data-aos="fade-up" data-aos-delay="200">
          <div class="wildlife-card">
            <img src="assets/images/bg/kakatua2.jpg" class="wildlife-img w-100" alt="Kakatua Kecil Jambul Kuning">
            <div class="card-body p-4">
              <h4 class="text-center fw-bold wildlife-title">Kakatua Kecil Jambul Kuning</h4>
              <h6 class="text-center fst-italic wildlife-subtitle mb-3">Cacatua sulphurea</h6>
              <p class="text-justify">
                <strong>Kakatua kecil jambul kuning</strong> hidup alami di Taman Nasional Komodo, 
                utamanya di Pulau Rinca, Pulau Bero, dan Pulau Komodo. 
                Burung ini hidup berkoloni dan memiliki populasi yang cukup banyak di dalam kawasan taman nasional. 
                Penggunaan tempat tinggal yang sama dengan biawak komodo membuat populasinya stabil dari tahun ke tahun.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Enhanced Destinations Section -->
  <section id="destinations" class="destinations-section">
    <div class="container">
      <div class="text-center mb-5" data-aos="fade-up">
        <h2 class="fw-bold mb-3">Paket Wisata </h2>
        <p class="text-muted fs-5">Pilih paket terbaik untuk petualangan impian Anda</p>
      </div>
      <div class="row">
        <?php
        include 'database/konek.php';
        $query = "SELECT t.*, k.nama as kategori_nama, l.nama_lokasi 
                 FROM tiket t 
                 LEFT JOIN kategori k ON t.kategori_id = k.id 
                 LEFT JOIN lokasi l ON t.lokasi = l.nama_lokasi
                 WHERE t.status='aktif' 
                 ORDER BY t.created_at DESC 
                 LIMIT 6";
        $result = $konek->query($query);

        if ($result && $result->num_rows > 0) {
            while ($data = $result->fetch_assoc()) {
                // Ambil fasilitas dari database dan tampilkan maksimal 3 item
                $fasilitasArray = explode(',', $data['fasilitas']);
                $fasilitasToShow = array_slice($fasilitasArray, 0, 3);
        ?>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo rand(100, 300); ?>">
                    <div class="destination-card">
                        <div class="position-relative overflow-hidden">
                            <img src="assets/images/tiket/<?= htmlspecialchars($data['gambar']); ?>" class="destination-img w-100" alt="<?= htmlspecialchars($data['nama_paket']); ?>">
                            <div class="position-absolute top-0 end-0 p-2">
                                <span class="badge bg-primary rounded-pill">
                                    <?= htmlspecialchars($data['kategori_nama']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="fw-semibold mb-2"><?= htmlspecialchars($data['nama_paket']); ?></h5>
                            <p class="text-muted mb-2"><i class="fas fa-clock me-2"></i><?= htmlspecialchars($data['durasi']); ?></p>
                            <p class="text-muted mb-3"><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($data['lokasi']); ?></p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="destination-price">
                                    Rp <?= number_format($data['harga'], 0, ',', '.'); ?>
                                </div>
                                <div class="text-muted">
                                    <small><i class="fas fa-users me-1"></i><?= $data['stok']; ?> tersedia</small>
                                </div>
                            </div>
                            
                            <div class="features-list mb-3">
                                <?php foreach ($fasilitasToShow as $fasilitas) { ?>
                                    <small class="text-muted"><i class="fas fa-check text-success me-1"></i> <?= htmlspecialchars(trim($fasilitas)); ?></small><br>
                                <?php } ?>
                            </div>
                            
                            <a href="users/login/login.php?id=<?= $data['id']; ?>" class="btn btn-destination w-100 text-light">
                                <i class="fas fa-shopping-cart me-2"></i>Login
                            </a>
                        </div>
                    </div>
                </div>
        <?php
            }
        } else {
            echo "<div class='col-12 text-center text-muted'>Belum ada paket wisata tersedia.</div>";
        }
        ?>
      </div>
      
      <div class="text-center mt-5" data-aos="fade-up">
        <a href="#" class="btn btn-outline-primary btn-lg rounded-pill px-5">
          Lihat Semua Paket <i class="fas fa-arrow-right ms-2"></i>
        </a>
      </div>
    </div>
  </section>

  <!-- Enhanced CTA Section -->
  <section class="cta-section">
    <div class="container">
      <div class="cta-content" data-aos="zoom-in">
        <div class="row">
          <div class="col-lg-8 mx-auto">
            <h2 class="fw-bold mb-4">Siap Untuk Petualangan Tak Terlupakan?</h2>
            <p class="fs-5 mb-4">Dapatkan penawaran spesial untuk pemesanan sebelum 31 Desember!</p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
              <a href="#destinations" class="btn btn-cta btn-lg">
                <i class="fas fa-ticket-alt me-2"></i>Pesan Sekarang
              </a>
            </div>
            <div class="mt-4">
              <small><i class="fas fa-lock me-2"></i>Pemesanan aman & terjamin</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer style="background:#111; color:white; text-align:center; padding:20px;">
    <p style="margin-bottom:10px;">&copy; 2025 LabuanBajoTrip. All Rights Reserved.</p>
    <div>
      <a href="#" style="color:white; margin:0 10px;"><i class="fab fa-facebook fa-lg"></i></a>
      <a href="#" style="color:white; margin:0 10px;"><i class="fab fa-instagram fa-lg"></i></a>
      <a href="#" style="color:white; margin:0 10px;"><i class="fab fa-twitter fa-lg"></i></a>
    </div>
  </footer>

  <!-- WhatsApp Floating Button -->
  <a href="https://wa.me/6281234567890?text=Halo,%20saya%20tertarik%20dengan%20paket%20wisata%20Labuan%20Bajo" 
     class="whatsapp-float" target="_blank">
    <i class="fab fa-whatsapp"></i>
  </a>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    // Initialize AOS
    AOS.init({
      duration: 1000,
      once: true
    });

    // Navbar scroll effect
    window.addEventListener('scroll', function() {
      const navbar = document.querySelector('.navbar-custom');
      if (window.scrollY > 50) {
        navbar.style.background = 'rgba(30, 41, 59, 0.98)';
        navbar.style.padding = '10px 0';
      } else {
        navbar.style.background = 'rgba(30, 41, 59, 0.95)';
        navbar.style.padding = '15px 0';
      }
    });

    // Smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });

    // Animated counter
    const counters = document.querySelectorAll('.stat-number');
    const speed = 200;

    const countUp = () => {
      counters.forEach(counter => {
        const target = +counter.getAttribute('data-count');
        const count = +counter.innerText;
        const increment = target / speed;

        if (count < target) {
          counter.innerText = Math.ceil(count + increment);
          setTimeout(countUp, 10);
        } else {
          counter.innerText = target.toLocaleString();
        }
      });
    };

    // Trigger counter animation when in viewport
    const observerOptions = {
      threshold: 0.5
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          countUp();
          observer.unobserve(entry.target);
        }
      });
    }, observerOptions);

    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
      observer.observe(statsSection);
    }
  </script>
</body>
</html>