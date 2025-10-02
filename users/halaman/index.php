<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Labuan Bajo Ticketing</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color:#f8f9fa; color:#333;">

  <!-- Hero Section -->
  <div style="background:linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1558979158-65a1eaa08691?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80'); 
              background-size:cover; background-position:center; color:white; padding:100px 0; text-align:center;">
    <div class="container">
      <h1 style="font-size:3rem; font-weight:700; margin-bottom:20px;">Jelajahi Keindahan Labuan Bajo</h1>
      <p style="font-size:1.2rem; margin-bottom:30px;">Temukan pengalaman tak terlupakan dengan paket wisata terbaik kami</p>
      <a href="tickets.html" class="btn btn-primary btn-lg" style="border-radius:30px; font-weight:600; padding:10px 25px;"> Pesan Sekarang</a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container mt-5">
    <div class="row mb-5">
      <div class="col-md-12">
        <h2 style="font-weight:700; margin-bottom:40px; position:relative; padding-bottom:15px;">Paket Populer
          <span style="content:''; position:absolute; bottom:0; left:0; width:70px; height:4px; background-color:#0d6efd; display:block;"></span>
        </h2>
      </div>

      <!-- Card 1 -->
      <div class="col-md-4 mb-4">
        <div class="card" style="border:none; border-radius:15px; overflow:hidden; box-shadow:0 5px 15px rgba(0,0,0,0.1); height:100%;">
          <img src="https://images.unsplash.com/photo-1544551763-46a013bb70d5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60" 
               class="card-img-top" alt="Pulau Padar" style="height:200px; object-fit:cover;">
          <div class="card-body">
            <h5 class="card-title" style="font-weight:600; color:#212529;">Pulau Padar Trekking</h5>
            <p class="card-text">Nikmati keindahan panorama Pulau Padar dari puncak bukit dengan trekking yang menantang.</p>
            <div class="d-flex justify-content-between align-items-center">
              <span style="font-size:1.5rem; font-weight:700; color:#0d6efd;">Rp 850.000</span>
              <a href="ticket-detail.html?id=1" class="btn btn-primary" style="border-radius:30px; font-weight:600; padding:10px 25px;">Detail</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Card 2 -->
      <div class="col-md-4 mb-4">
        <div class="card" style="border:none; border-radius:15px; overflow:hidden; box-shadow:0 5px 15px rgba(0,0,0,0.1); height:100%;">
          <img src="https://images.unsplash.com/photo-1536244636800-a3f74db0f3cf?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60" 
               class="card-img-top" alt="Komodo Island" style="height:200px; object-fit:cover;">
          <div class="card-body">
            <h5 class="card-title" style="font-weight:600; color:#212529;">Komodo Island Adventure</h5>
            <p class="card-text">Temui komodo di habitat aslinya dan snorkeling di perairan kristal Pink Beach.</p>
            <div class="d-flex justify-content-between align-items-center">
              <span style="font-size:1.5rem; font-weight:700; color:#0d6efd;">Rp 1.200.000</span>
              <a href="ticket-detail.html?id=2" class="btn btn-primary" style="border-radius:30px; font-weight:600; padding:10px 25px;">Detail</a>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
  <?php include '../../includes/footer.php';
  include '../../includes/navbar.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
