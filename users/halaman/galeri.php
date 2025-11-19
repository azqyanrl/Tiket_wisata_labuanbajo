<?php
include '../../database/konek.php';
include "session_cek.php";
include '../../includes/navbar.php';
include '../../includes/boot.php'; // Bootstrap CSS & JS
?>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
<!-- Hero Section -->
<section class="text-center text-white bg-dark py-5" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('../../assets/images/bg/pantai.jpg'); background-size: cover; background-position: center;">
    <div class="container py-5">
        <h1 class="display-4 fw-bold">Galeri Labuan Bajo</h1>
        <p class="lead">Lihat momen-momen indah dan keindahan destinasi yang kami tawarkan.</p>
    </div>
</section>

<!-- Gallery Section -->
<main class="container my-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Galeri Perjalanan</h2>
        <p class="text-muted">Temukan inspirasi dari foto-foto liburan impian Anda</p>
    </div>

    <!-- Filter Buttons -->
    <div class="d-flex justify-content-center flex-wrap gap-2 mb-4">
        <button class="btn btn-outline-primary active" data-filter="all">Semua</button>
        <button class="btn btn-outline-primary" data-filter="pantai">Pantai</button>
        <button class="btn btn-outline-primary" data-filter="adventure">Adventure</button>
        <button class="btn btn-outline-primary" data-filter="snorkeling">Snorkeling</button>
        <button class="btn btn-outline-primary" data-filter="trekking">Trekking</button>
    </div>

    <!-- Gallery Grid -->
    <div class="row g-4" id="gallery-container">
        <?php
        // Ambil semua data galeri beserta kategori
        $result = $konek->query("
            SELECT g.*, k.nama AS nama_kategori
            FROM galleries g
            LEFT JOIN kategori k ON g.kategori_id = k.id
            ORDER BY g.created_at DESC
        ");

        if ($result && $result->num_rows > 0) {
            while ($data = $result->fetch_assoc()) {
                // Pastikan ada fallback jika nama_kategori null
                $kategori = strtolower(htmlspecialchars($data['nama_kategori'] ?? 'lainnya'));
        ?>
                <div class="col-lg-3 col-md-4 col-sm-6 gallery-item" data-category="<?= $kategori; ?>">
                    <div class="card h-100 shadow-sm gallery-card" style="cursor: pointer;" 
                         data-bs-toggle="modal" 
                         data-bs-target="#lightboxModal"
                         data-img-src="../../assets/images/galery/<?= htmlspecialchars($data['gambar']); ?>"
                         data-title="<?= htmlspecialchars($data['judul']); ?>"
                         data-caption="<?= htmlspecialchars(ucfirst($data['nama_kategori'] ?? '')); ?>">
                        
                        <img src="../../assets/images/galery/<?= htmlspecialchars($data['gambar']); ?>" class="card-img-top gallery-img" alt="<?= htmlspecialchars($data['judul']); ?>">
                        <div class="card-img-overlay d-flex flex-column justify-content-end text-white p-2">
                             <h5 class="card-title"><?= htmlspecialchars($data['judul']); ?></h5>
                             <p class="card-text small"><?= htmlspecialchars(ucfirst($data['nama_kategori'] ?? '')); ?></p>
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

    <!-- CTA Section -->
    <section class="text-center bg-primary text-white rounded-3 p-5 mt-5">
        <h2>Siap untuk Petualangan Anda Sendiri?</h2>
        <p>Jangan hanya lihat, rasakan sendiri keindahannya! Temukan dan pesan tiket wisata impian Anda sekarang.</p>
        <a href="destinasi.php" class="btn btn-light btn-lg">Cari Tiket Wisata Sekarang</a>
    </section>
</main>

<!-- Bootstrap Modal untuk Lightbox -->
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-labelledby="lightboxModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white" id="lightboxModalLabel">Judul Foto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="lightbox-img" src="" class="img-fluid" alt="">
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <p id="lightbox-caption" class="text-light mb-0"></p>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS -->
<style>
    .gallery-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }
    .gallery-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
    }
    .gallery-card .card-img-overlay {
        background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .gallery-card:hover .card-img-overlay {
        opacity: 1;
    }
    .gallery-img {
        transition: transform 0.5s ease;
        height: 250px;
        object-fit: cover;
    }
    .gallery-card:hover .gallery-img {
        transform: scale(1.1);
    }
</style>

<!-- JS Filter & Lightbox -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('[data-filter]');
    const galleryItems = document.querySelectorAll('.gallery-item');

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            const filterValue = button.getAttribute('data-filter');
            galleryItems.forEach(item => {
                const itemCategory = item.getAttribute('data-category');
                if (filterValue === 'all' || itemCategory === filterValue) {
                    item.classList.remove('d-none');
                } else {
                    item.classList.add('d-none');
                }
            });
        });
    });

    const lightboxModal = document.getElementById('lightboxModal');
    const lightboxImg = document.getElementById('lightbox-img');
    const lightboxTitle = lightboxModal.querySelector('.modal-title');
    const lightboxCaption = document.getElementById('lightbox-caption');

    if (lightboxModal) {
        lightboxModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const imgSrc = button.getAttribute('data-img-src');
            const title = button.getAttribute('data-title');
            const caption = button.getAttribute('data-caption');

            lightboxImg.src = imgSrc;
            lightboxImg.alt = title;
            lightboxTitle.textContent = title;
            lightboxCaption.textContent = caption;
        });
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
