<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Labuan Bajo Trip</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
</head>
<body style="font-family:'Poppins', sans-serif;">

  <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top navbar-dark" 
        style="background:rgba(0, 0, 0, 0.48); padding:15px 0; font-size:17px;">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#" style="font-size:22px;">LabuanBajoTrip</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link active" href="#">Beranda</a></li>
            <li class="nav-item"><a class="nav-link" href="#destinasi">Destinasi</a></li>
            <li class="nav-item"><a class="nav-link" href="#testimoni">Testimoni</a></li>
            <li class="nav-item">
            <a class="nav-link btn btn-primary text-white px-4 ms-3" href="login/login.php" 
                style="border-radius:25px; padding:8px 18px; font-size:15px;">
            Login
            </a>
            </li>
        </ul>
        </div>
    </div>
    </nav>


  <!-- Hero -->
  <section style="height:100vh; background:url('../assets/images/hero/padarhd.avif') center/cover no-repeat; position:relative; color:white; text-align:center;">
    <div style="position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6);"></div>
    <div style="position:relative; z-index:2; top:50%; transform:translateY(-50%);">
      <h1 style="font-size:3rem; font-weight:bold;">Jelajahi Keindahan Labuan Bajo</h1>
      <p style="font-size:18px; margin:15px 0;">Pesan tiket wisata dengan mudah, aman, dan terpercaya</p>
      <a href="#destinasi" class="btn btn-primary btn-lg" 
         style="border-radius:25px; padding:12px 30px;">Mulai Pesan</a>
    </div>
  </section>

  <!-- Destinasi -->
  <section id="destinasi" style="padding:60px 0; background:#f8f9fa;">
    <div class="container">
      <h2 style="text-align:center; margin-bottom:40px; font-weight:bold;">Destinasi Populer</h2>
      <div class="row" id="destinasiCards"></div>
    </div>
  </section>

  <!-- Testimoni -->
  <section id="testimoni" style="padding:60px 0;">
    <div class="container">
      <h2 style="text-align:center; margin-bottom:40px; font-weight:bold;">Apa Kata Mereka</h2>
      <div id="testiCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner text-center">
          <div class="carousel-item active">
            <blockquote class="blockquote">
              <p style="font-size:18px;">"Liburan terbaik! Booking tiket super mudah."</p>
              <footer class="blockquote-footer">Andi, Jakarta</footer>
            </blockquote>
          </div>
          <div class="carousel-item">
            <blockquote class="blockquote">
              <p style="font-size:18px;">"Destinasi lengkap, harga transparan, recommended!"</p>
              <footer class="blockquote-footer">Sinta, Surabaya</footer>
            </blockquote>
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

  <!-- Script -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Data destinasi dinamis
    const destinasi = [
      { nama: "Pulau Padar", gambar: "img/padar.jpg", deskripsi: "Panorama bukit ikonik dengan pemandangan laut." },
      { nama: "Pulau Komodo", gambar: "img/komodo.jpg", deskripsi: "Habitat asli Komodo, satwa purba dunia." },
      { nama: "Pantai Pink", gambar: "img/pink.jpg", deskripsi: "Pantai dengan pasir berwarna merah muda unik." },
      { nama: "Gili Laba", gambar: "img/laba.jpg", deskripsi: "Pemandangan sunset yang menakjubkan." }
    ];

    const destinasiContainer = document.getElementById("destinasiCards");

    destinasi.forEach(d => {
      destinasiContainer.innerHTML += `
        <div class="col-md-6 col-lg-3 mb-4">
          <div class="card h-100 shadow-sm" style="transition:all 0.3s ease; border-radius:12px; overflow:hidden; cursor:pointer;">
            <img src="${d.gambar}" class="card-img-top" alt="${d.nama}" style="height:200px; object-fit:cover;">
            <div class="card-body" style="padding:20px;">
              <h5 class="card-title" style="font-weight:bold; color:#0d6efd;">${d.nama}</h5>
              <p class="card-text" style="font-size:14px; color:#555;">${d.deskripsi}</p>
              <a href="login.html" class="btn btn-primary" style="padding:8px 16px; border-radius:20px;">Pesan Tiket</a>
            </div>
          </div>
        </div>
      `;
    });
  </script>
</body>
</html>
