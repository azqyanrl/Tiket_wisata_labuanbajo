<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tiket - Labuan Bajo Ticketing</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color:#f8f9fa; color:#333;">

  <!-- Load Navbar -->
  <?php include '../../includes/navbar.php'; ?>
  <!-- Hero Section -->
<div style="
  background:linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
             url('https://images.unsplash.com/photo-1558979158-65a1eaa08691?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80'); 
  background-size:cover; 
  background-position:center; 
  color:white; 
  padding:100px 0; 
  text-align:center;">
  
  <div class="container">
    <h1 style="font-size:3rem; font-weight:700; margin-bottom:20px;">
      Jelajahi Keindahan Labuan Bajo
    </h1>
    <p style="font-size:1.2rem; margin-bottom:30px;">
      Temukan pengalaman tak terlupakan dengan paket wisata terbaik kami
    </p>
    <a href="tickets.html" class="btn btn-primary btn-lg" 
       style="border:none; border-radius:30px; font-weight:600; padding:10px 25px;">
      Lihat Paket Tiket
    </a>
  </div>
</div>

  <!-- Main Content -->
  <div class="container mt-5">
    <div class="row mb-4">
      <div class="col-md-12">
        <h2 style="font-weight:700; margin-bottom:40px; position:relative; padding-bottom:15px;">
          Semua Paket Tiket
          <span style="content:''; position:absolute; bottom:0; left:0; width:70px; height:4px; background-color:#0d6efd; display:block;"></span>
        </h2>
      </div>
      <div class="col-md-3 mb-3">
        <select class="form-select" id="categoryFilter">
          <option value="">Semua Kategori</option>
          <option value="Adventure">Adventure</option>
          <option value="Snorkeling">Snorkeling</option>
          <option value="Trekking">Trekking</option>
          <option value="Cultural">Cultural</option>
        </select>
      </div>
      <div class="col-md-3 mb-3">
        <select class="form-select" id="durationFilter">
          <option value="">Semua Durasi</option>
          <option value="1 Hari">1 Hari</option>
          <option value="2 Hari 1 Malam">2 Hari 1 Malam</option>
          <option value="3 Hari 2 Malam">3 Hari 2 Malam</option>
        </select>
      </div>
      <div class="col-md-3 mb-3">
        <select class="form-select" id="priceFilter">
          <option value="">Semua Harga</option>
          <option value="0-500000">Di bawah Rp 500.000</option>
          <option value="500000-1000000">Rp 500.000 - Rp 1.000.000</option>
          <option value="1000000-2000000">Rp 1.000.000 - Rp 2.000.000</option>
          <option value="2000000-9999999">Di atas Rp 2.000.000</option>
        </select>
      </div>
      <div class="col-md-3 mb-3">
        <button class="btn btn-primary w-100" onclick="filterTickets()" 
                style="border:none; border-radius:30px; padding:10px 25px; font-weight:600;">
          Terapkan Filter
        </button>
      </div>
    </div>

    <div class="row" id="ticketsList">
      <!-- Tickets will be loaded here -->
    </div>
  </div>

  <!-- Load Footer -->
  <div id="footer-container"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Sample ticket data
    const tickets = [
      {
        id: 1,
        name: "Pulau Padar Trekking",
        description: "Nikmati keindahan panorama Pulau Padar dari puncak bukit dengan trekking yang menantang.",
        price: 850000,
        duration: "1 Hari",
        category: "Trekking",
        image: "https://images.unsplash.com/photo-1544551763-46a013bb70d5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60"
      },
      {
        id: 2,
        name: "Komodo Island Adventure",
        description: "Temui komodo di habitat aslinya dan snorkeling di perairan kristal Pink Beach.",
        price: 1200000,
        duration: "2 Hari 1 Malam",
        category: "Adventure",
        image: "https://images.unsplash.com/photo-1536244636800-a3f74db0f3cf?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60"
      },
      {
        id: 3,
        name: "Kelor Island & Manjarite",
        description: "Jelajahi pulau kecil dengan pasir putih dan air biru jernih yang memukau.",
        price: 750000,
        duration: "1 Hari",
        category: "Snorkeling",
        image: "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60"
      },
      {
        id: 4,
        name: "Pink Beach Snorkeling",
        description: "Nikmati keindahan pantai dengan pasir pink yang unik dan snorkeling dengan ikan-ikan tropis.",
        price: 650000,
        duration: "1 Hari",
        category: "Snorkeling",
        image: "https://images.unsplash.com/photo-1544551763-46a013bb70d5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60"
      },
      {
        id: 5,
        name: "Gua Batu Cermin Tour",
        description: "Jelajahi gua dengan formasi batu stalaktit dan stalagmit yang memukau.",
        price: 350000,
        duration: "Setengah Hari",
        category: "Cultural",
        image: "https://images.unsplash.com/photo-1516483638261-f4dbaf036963?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60"
      },
      {
        id: 6,
        name: "Kanawa Island Day Trip",
        description: "Nikmati keindahan pulau kecil dengan pantai berpasir putih dan terumbu karang yang indah.",
        price: 550000,
        duration: "1 Hari",
        category: "Snorkeling",
        image: "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60"
      }
    ];

    // Load components
    document.addEventListener('DOMContentLoaded', function() {
      // Load navbar
      fetch('navbar.html')
        .then(response => response.text())
        .then(data => { document.getElementById('navbar-container').innerHTML = data; })
        .catch(error => console.error('Error loading navbar:', error));

      // Load footer
      fetch('footer.html')
        .then(response => response.text())
        .then(data => { document.getElementById('footer-container').innerHTML = data; })
        .catch(error => console.error('Error loading footer:', error));

      // Load tickets
      loadTickets();
    });

    // Load tickets
    function loadTickets() {
      const ticketsList = document.getElementById('ticketsList');
      ticketsList.innerHTML = '';
      
      tickets.forEach(ticket => {
        const ticketCard = `
          <div class="col-md-4 mb-4">
            <div class="card" style="border:none; border-radius:15px; overflow:hidden; box-shadow:0 5px 15px rgba(0,0,0,0.1); height:100%;">
              <img src="${ticket.image}" alt="${ticket.name}" 
                   style="height:200px; object-fit:cover;" class="card-img-top">
              <div class="card-body">
                <h5 class="card-title" style="font-weight:600; color:#212529;">${ticket.name}</h5>
                <p class="card-text">${ticket.description}</p>
                <div class="d-flex justify-content-between align-items-center">
                  <span style="font-size:1.5rem; font-weight:700; color:#0d6efd;">
                    Rp ${ticket.price.toLocaleString('id-ID')}
                  </span>
                  <a href="ticket-detail.html?id=${ticket.id}" 
                     class="btn btn-primary" 
                     style="border:none; border-radius:30px; padding:10px 25px; font-weight:600;">
                    Detail
                  </a>
                </div>
              </div>
            </div>
          </div>
        `;
        ticketsList.innerHTML += ticketCard;
      });
    }

    // Filter tickets
    function filterTickets() {
      const categoryFilter = document.getElementById('categoryFilter').value;
      const durationFilter = document.getElementById('durationFilter').value;
      const priceFilter = document.getElementById('priceFilter').value;
      
      alert(`Filter diterapkan: Kategori=${categoryFilter}, Durasi=${durationFilter}, Harga=${priceFilter}`);
    }
  </script>
  <?php include '../../includes/footer.php'; ?>
</body>
</html>
