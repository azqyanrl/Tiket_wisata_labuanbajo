<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login/login.php');
    exit();
}
include '../../../database/konek.php';

 $stmt = $konek->prepare("SELECT * FROM users WHERE id = ? AND role = 'posko'");
 $stmt->bind_param("i", $_GET['id']);
 $stmt->execute();
 $admin = $stmt->get_result()->fetch_assoc();

if (!$admin) {
    header('Location: manage_posko.php');
    exit();
}
?>
<!-- (Letakkan kode ini di dalam template halaman admin-mu) -->
<div class="container-fluid">
    <h1 class="h3 mb-4">Edit Admin Posko</h1>
    
    <div class="card shadow">
        <div class="card-body">
            <form action="proses_posko.php" method="POST">
                <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($admin['nama_lengkap']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="lokasi" class="form-label">Lokasi Posko</label>
                    <select class="form-select" name="lokasi" required>
                        <?php 
                        $lokasi_result = $konek->query("SELECT DISTINCT lokasi FROM tiket ORDER BY lokasi");
                        while($lok = $lokasi_result->fetch_assoc()){
                            $selected = ($admin['lokasi'] == $lok['lokasi']) ? 'selected' : '';
                            echo '<option value="'.htmlspecialchars($lok['lokasi']).'" '.$selected.'>'.htmlspecialchars($lok['lokasi']).'</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="resetPassword" name="reset_password">
                    <label class="form-check-label" for="resetPassword">Reset Password (akan menjadi '123456')</label>
                </div>
                <button type="submit" name="edit_posko" class="btn btn-success">Update</button>
                <a href="manage_posko.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>