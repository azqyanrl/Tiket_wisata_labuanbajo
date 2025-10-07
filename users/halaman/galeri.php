<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri - WisataKu (Bootstrap)</title>
    
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <!-- Font Awesome untuk Ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* --- Custom CSS untuk melengkapi Bootstrap --- */
        body {
            font-family: 'Poppins', sans-serif;
        }

        /* Hero Section */
        .hero {
            height: 60vh;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://picsum.photos/seed/hero-wisata-bs/1920/800.jpg') no-repeat center center/cover;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        /* Gallery Card Hover Effect */
        .gallery-item {
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
        }

        .gallery-item .card {
            overflow: hidden;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .gallery-item img {
            transition: transform 0.5s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-item .card-img-overlay {
            background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .gallery-item:hover .card-img-overlay {
            opacity: 1;
        }
        
        /* Filter Buttons */
        .filter-buttons .btn {
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }

        /* Modal Image */
        .modal-body img {
            width: 100%;
            border-radius: 8px;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: #fff;
            padding: 4rem 0;
            text-align: center;
        }
        
        .cta-btn {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 1rem 2.5rem;
            background: #fff;
            color: #007bff;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .cta-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            color: #0056b3;
        }

        /* Footer */
        .main-footer {
            background-color: #2c3e50;
            color: #fff;
            padding: 3rem 0 1rem;
        }

        .social-links a {
            color: #fff;
            font-size: 1.5rem;
            margin: 0 0.75rem;
            transition: color 0.3s ease;
        }

        .social-links a:hover {
            color: #007bff;
        }
    </style>
</head>
<body>
<?php include '../../includes/navbar.php'?>
    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <h1 class="display-3 fw-bold">Jelajahi Keindahan Indonesia</h1>
                <p class="lead">Lihat momen-momen indah dari para wisatawan yang telah menjelajahi Nusantara bersama kami.</p>
            </div>
        </section>
        <?php
include '../../database/konek.php';
include "session_cek.php";
include '../../includes/navbar.php';
include '../../includes/boot.php';

// Ambil semua data galeri
$result = $konek->query("SELECT * FROM galeri ORDER BY created_at DESC");
?>

<div class="container my-5">
  <h3 class="text-center mb-4">Galeri Labuan Bajo</h3>
  <div class="row">
    <?php
    if ($result && $result->num_rows > 0) {
      while ($data = $result->fetch_assoc()) {
        ?>
        <div class="col-md-4 mb-4">
          <div class="card h-100 shadow-sm border-0">
            <img src="../../assets/images/<?= htmlspecialchars($data['gambar']); ?>" class="card-img-top" alt="<?= htmlspecialchars($data['judul']); ?>">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($data['judul']); ?></h5>
              <p class="text-muted mb-0"><?= htmlspecialchars($data['kategori']); ?></p>
            </div>
          </div>
        </div>
        <?php
      }
    } else {
      echo "<div class='col-12 text-center text-muted'>Belum ada foto galeri tersedia.</div>";
    }
    ?>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>
</body>
</html>