<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login/login.php');
    exit();
}
?>
<!-- (Letakkan kode ini di dalam template halaman admin-mu) -->
<div class="container-fluid">
    <h1 class="h3 mb-4">Tambah Admin Posko Baru</h1>
    
    <div class="card shadow">
        <div class="card-body">
            <form action="proses_posko.php" method="POST">
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" name="nama" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="lokasi" class="form-label">Lokasi Posko</label>
                    <select class="form-select" name="lokasi" required>
                        <option value="">-- Pilih Lokasi --</option>
                        <?php 
                        include '../../../database/konek.php';
                        $lokasi_result = $konek->query("SELECT DISTINCT lokasi FROM tiket ORDER BY lokasi");
                        while($lok = $lokasi_result->fetch_assoc()){
                            echo '<option value="'.htmlspecialchars($lok['lokasi']).'">'.htmlspecialchars($lok['lokasi']).'</option>';
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" name="add_posko" class="btn btn-success">Simpan</button>
                <a href="manage_posko.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>