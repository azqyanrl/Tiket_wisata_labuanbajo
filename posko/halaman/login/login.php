<?php
// 🔧 Debug otomatis: aktif di localhost, mati di server publik
if (
    in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) ||
    str_contains($_SERVER['HTTP_HOST'], 'localhost')
) {
    // Mode pengembangan (development)
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // Mode produksi (production)
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}


// ✅ Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proses login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    include '../../../database/konek.php';
    include '../../../includes/boot.php';
    
    $stmt = $konek->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['id_user'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] === 'posko') {
                if (!empty($user['lokasi_id'])) {
                    $lokasi_stmt = $konek->prepare("SELECT nama_lokasi FROM lokasi WHERE id = ?");
                    $lokasi_stmt->bind_param("i", $user['lokasi_id']);
                    $lokasi_stmt->execute();
                    $lokasi_result = $lokasi_stmt->get_result();
                    
                    if ($lokasi_result->num_rows > 0) {
                        $lokasi = $lokasi_result->fetch_assoc();
                        $_SESSION['lokasi'] = $lokasi['nama_lokasi'];
                    }
                } else if (!empty($user['lokasi'])) {
                    $_SESSION['lokasi'] = $user['lokasi'];
                }
                
                header('Location: ../index.php?page=posko_dashboard');
                exit;
            } else if ($user['role'] === 'admin') {
                header('Location: ../../admin/index.php?page=dashboard');
                exit;
            } else {
                header('Location: ../../user/index.php');
                exit;
            }
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Posko | Labuan Bajo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('../../../assets/images/bg/pdr1.jpg') no-repeat center center;
            background-size: cover;
            min-height: 100vh;
        }
        .login-container {
            max-width: 900px;
        }
        .branding-section {
            background-color: rgba(102, 126, 234, 0.85);
        }
        .btn-theme {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
        }
        .btn-theme:hover {
            background: linear-gradient(135deg, #5a6fd8, #6a4190);
            border: none;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
    <div class="container login-container">
        <div class="row shadow-lg rounded-3 overflow-hidden">
            <!-- Branding Section -->
            <div class="col-lg-6 branding-section p-5 d-flex flex-column justify-content-center text-white">
                <div class="text-center mb-4">
                    <i class="bi bi-geo-alt-fill fs-1"></i>
                    <h2 class="fw-bold mt-3">Portal Posko</h2>
                    <p class="mb-0">Wisata Labuan Bajo</p>
                </div>
                <p class="text-center">Akses cepat untuk melaporkan dan mengelola informasi di lapangan.</p>
            </div>

            <!-- Form Section -->
            <div class="col-lg-6 bg-white p-5">
                <h3 class="fw-bold text-center mb-4">Login sebagai Petugas Posko</h3>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div><?= $error ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-theme btn-lg text-white" name="login">Masuk</button>
                    </div>
                </form>

                <footer class="text-center mt-4">
                    <hr class="border-white">
                    <small class="text-muted">
                        Lupa password?
                        <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#contactModal">Hubungi Admin</a>
                    </small>
                </footer>
            </div>
        </div>
    </div>

    <!-- Modal Kontak -->
    <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                    <h5 class="modal-title"><i class="bi bi-headset me-2"></i>Pusat Bantuan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-center text-muted mb-4">Hubungi admin melalui kontak berikut:</p>

                    <div class="contact-item bg-light rounded-3 p-3 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-telephone-fill" style="color: #667eea;" class="fs-4 me-3"></i>
                            <span>+62 812-3456-7890</span>
                        </div>
                    </div>

                    <div class="contact-item bg-light rounded-3 p-3 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-whatsapp" style="color: #667eea;" class="fs-4 me-3"></i>
                            <span>+62 812-3456-7890</span>
                        </div>
                    </div>

                    <div class="contact-item bg-light rounded-3 p-3 mb-2">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-envelope-fill text-danger fs-4 me-3"></i>
                            <span>admin@labuanbajo.id</span>
                        </div>
                    </div>

                    <div class="contact-item bg-light rounded-3 p-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-instagram text-warning fs-4 me-3"></i>
                            <span>@labuanbajo.official</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    </script>
</body>
</html>