<?php
// File: login/lupa_password.php
session_start();
include '../database/konek.php';

 $error_message = '';
 $success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Cek apakah email ada di database
    $query = $konek->prepare("SELECT id, username FROM users WHERE email = ? AND role = 'admin'");
    $query->bind_param("s", $email);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        
        // 1. Generate token yang aman
        $token = bin2hex(random_bytes(32));
        
        // 2. Set waktu kadaluarsa (1 jam dari sekarang)
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // 3. Simpan token dan expiry ke database
        $update = $konek->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE id = ?");
        $update->bind_param("ssi", $token, $expiry, $admin['id']);
        $update->execute();
        
        // 4. Kirim email (lihat penjelasan di bawah)
        $reset_link = "http://localhost/Tiket_wisata_labuanbajo/admin/login/reset_password.php?token=" . $token;
        
        // --- KODE EMAIL DI SINI ---
        // Untuk saat ini, kita tampilkan linknya saja untuk keperluan testing
        $success_message = "Link reset telah dibuat. Untuk testing, klik link ini: <a href='$reset_link'>$reset_link</a>";
        // Di produksi, Anda harus mengirim email ini. Lihat bagian "Pengiriman Email".
        
    } else {
        $error_message = "Email tidak ditemukan atau terdaftar sebagai admin.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Admin Labuan Bajo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 mt-5">
                <div class="card">
                    <div class="card-header">
                        <h4>Lupa Password Admin</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        <p>Masukkan email Anda yang terdaftar untuk menerima link reset password.</p>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Kirim Link Reset</button>
                            <a href="login.php" class="btn btn-secondary">Kembali ke Login</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>