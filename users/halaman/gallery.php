<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Galeri Wisata - Labuan Bajo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    .fade-in { opacity:0; transform:translateY(20px); transition:opacity .8s ease, transform .8s ease; }
    .fade-in.show { opacity:1; transform:translateY(0); }
    .gallery-item { overflow:hidden; border-radius:12px; transition:all .3s ease; }
    .gallery-item img { transition:transform .5s ease; }
    .gallery-item:hover img { transform:scale(1.1); }
  </style>
</head>
<body style="font-family:'Poppins', sans-serif; background:#f8f9fa;">

<?php include '../../includes/navbar.php'; ?>
<?php include '../../includes/hero.php'; ?>
<?php heroSection("Galeri Wisata","Album Keindahan Labuan Bajo","../../assets/images/hero/padarhd.avif","40vh"); ?>

<section class="container py-5">
  <div class="row g-4">
    <div class="col-md-4 fade-in">
      <div class="gallery-item"><img src="https://source.unsplash.com/600x400/?padar,island" class="img-fluid"></div>
    </div>
    <div class="col-md-4 fade-in">
      <div class="gallery-item"><img src="https://source.unsplash.com/600x400/?pink,beach" class="img-fluid"></div>
    </div>
    <div class="col-md-4 fade-in">
      <div class="gallery-item"><img src="https://source.unsplash.com/600x400/?komodo" class="img-fluid"></div>
    </div>
  </div>
</section>

<?php include '../../includes/footer.php'; ?>
</body>
</html>
