<?php
include '../../../database/konek.php';
include '../../boot.php';

// Hapus tiket
if (isset($_GET['delete'])) {
    $id_tiket = $_GET['delete'];
    $query_hapus = $pdo->prepare("DELETE FROM tiket WHERE id = ?");
    $query_hapus->execute([$id_tiket]);
    header('Location: index.php');
    exit();
}

// Ambil data tiket
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Tiket - Labuan Bajo</title>
</head>

<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="mb-0 text-gray-800">Manajemen Tiket</h5>
                            </div>
                            <div class="col-auto">
                                <a href="add.php" class="btn btn-success">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Tiket
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Gambar</th>
                                        <th>Nama Paket</th>
                                        <th>Harga</th>
                                        <th>Kategori</th>
                                        <th>Durasi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $tampil=$konek->query("SELECT *FROM tiket");
                                    foreach ($tampil as $tiket){
                                    $id++ ;
                                    ?>
                                        <tr>
                                            <th scope="row"><?=$id?></th>
                                            <td>
                                                <?php if (!empty($tiket['gambar'])): ?>
                                                    <img src="../../assets/images/tiket/<?= htmlspecialchars($tiket['gambar']) ?>"
                                                        style="width:60px; height:60px; object-fit:cover; border-radius:5px;" alt="Gambar Tiket">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center"
                                                        style="width:60px; height:60px; border-radius:5px;">
                                                        <i class="bi bi-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($tiket['nama_paket']) ?></td>
                                            <td>Rp <?= number_format($tiket['harga'], 2, ',', '.') ?></td>
                                            <td><?= htmlspecialchars($tiket['kategori']) ?></td>
                                            <td><?= htmlspecialchars($tiket['durasi']) ?></td>
                                            <td>
                                                <a href="edit.php?id=<?= $tiket['id'] ?>"
                                                    class="btn btn-primary btn-sm me-1"
                                                    style="padding:0.25rem 0.5rem; font-size:0.875rem; border-radius:0.25rem;">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <a href="tiket.php?delete=<?= $tiket['id'] ?>"
                                                    onclick="return confirm('Yakin ingin menghapus tiket ini?')"
                                                    class="btn btn-danger btn-sm"
                                                    style="padding:0.25rem 0.5rem; font-size:0.875rem; border-radius:0.25rem;">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>