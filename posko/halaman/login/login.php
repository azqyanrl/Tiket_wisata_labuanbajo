<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proses login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    include '../../../database/konek.php';
    
    $stmt = $konek->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['id_user'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            
            // Set lokasi untuk admin posko
            if ($user['role'] === 'posko') {
                // Jika menggunakan lokasi_id
                if (!empty($user['lokasi_id'])) {
                    $lokasi_stmt = $konek->prepare("SELECT nama_lokasi FROM lokasi WHERE id = ?");
                    $lokasi_stmt->bind_param("i", $user['lokasi_id']);
                    $lokasi_stmt->execute();
                    $lokasi_result = $lokasi_stmt->get_result();
                    
                    if ($lokasi_result->num_rows > 0) {
                        $lokasi = $lokasi_result->fetch_assoc();
                        $_SESSION['lokasi'] = $lokasi['nama_lokasi'];
                    }
                } 
                // Jika menggunakan field lokasi langsung
                else if (!empty($user['lokasi'])) {
                    $_SESSION['lokasi'] = $user['lokasi'];
                }
                
                // Redirect ke dashboard posko
                header('Location: ../index.php?page=posko_dashboard');
                exit;
            } 
            // Jika admin biasa
            else if ($user['role'] === 'admin') {
                header('Location: ../../admin/index.php?page=dashboard');
                exit;
            }
            // Jika user biasa
            else {
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

// Proses kirim pesan ke admin
if (isset($_POST['kirim_pesan'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $subjek = $_POST['subjek'];
    $pesan = $_POST['pesan'];
    
    // Simpan ke database atau kirim email
    // Contoh: simpan ke tabel kontak
    include '../../../database/konek.php';
    $stmt = $konek->prepare("INSERT INTO kontak_admin (nama, email, subjek, pesan, tanggal) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $nama, $email, $subjek, $pesan);
    
    if ($stmt->execute()) {
        $sukses = "Pesan berhasil dikirim! Admin akan segera merespon.";
    } else {
        $error_msg = "Gagal mengirim pesan. Silakan coba lagi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Labuan Bajo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        /* Minimal CSS hanya untuk background gradient */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        /* Style untuk tombol toggle password */
        .password-toggle {
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .password-toggle:hover {
            color: #667eea !important;
        }
        
        /* Style untuk modal */
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .contact-item {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .contact-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .tab-content {
            padding: 20px 0;
        }
        
        .nav-pills .nav-link {
            border-radius: 10px;
            margin-bottom: 10px;
        }
        
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body>
    <div class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
        <div class="row g-0 shadow-lg rounded-3 overflow-hidden" style="max-width: 900px; width: 100%;">
            <!-- Left Side - Info -->
            <div class="col-lg-6 bg-primary bg-gradient p-5 d-flex flex-column justify-content-center text-white">
                <div class="text-center mb-4">
                    <div class="bg-white bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-building-gear fs-1"></i>
                    </div>
                    <h2 class="fw-bold mb-2">Labuan Bajo</h2>
                    <p class="mb-0 opacity-75">Sistem Manajemen Posko</p>
                </div>
                
                <div class="mb-4">
                    <h4 class="fw-semibold mb-3">Selamat Datang Kembali!</h4>
                    <p class="opacity-90">Masuk ke akun Anda untuk mengakses dashboard dan kelola data posko dengan mudah dan aman.</p>
                </div>
                
                <div class="row g-3">
                    <div class="col-4">
                        <div class="bg-white bg-opacity-10 rounded-3 p-3 text-center">
                            <i class="bi bi-shield-check fs-4 d-block mb-2"></i>
                            <small>Aman</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-white bg-opacity-10 rounded-3 p-3 text-center">
                            <i class="bi bi-lightning-charge fs-4 d-block mb-2"></i>
                            <small>Cepat</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-white bg-opacity-10 rounded-3 p-3 text-center">
                            <i class="bi bi-graph-up fs-4 d-block mb-2"></i>
                            <small>Analitik</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Login Form -->
            <div class="col-lg-6 bg-white p-5">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-dark">Masuk ke Akun</h3>
                    <p class="text-muted">Silakan masukkan kredensial Anda</p>
                </div>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div><?= $error ?></div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-person text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" id="username" 
                                   name="username" placeholder="Masukkan username" required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-lock text-muted"></i>
                            </span>
                            <input type="password" class="form-control border-start-0 border-end-0" id="password" 
                                   name="password" placeholder="Masukkan password" required>
                            <span class="input-group-text bg-light password-toggle" onclick="togglePassword()">
                                <i class="bi bi-eye text-muted" id="toggleIcon"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="login" class="btn btn-primary btn-lg fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Login
                        </button>
                    </div>
                    
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            Lupa password? 
                            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#contactModal">Hubungi Admin Pusat</a>
                        </small>
                    </div>
                </form>
                
                <div class="mt-4 pt-4 border-top text-center">
                    <div class="d-flex justify-content-center gap-3">
                        <a href="#" class="text-muted text-decoration-none" data-bs-toggle="modal" data-bs-target="#faqModal">
                            <i class="bi bi-question-circle me-1"></i> Bantuan
                        </a>
                        <span class="text-muted">•</span>
                        <a href="#" class="text-muted text-decoration-none" data-bs-toggle="modal" data-bs-target="#policyModal">
                            <i class="bi bi-shield-check me-1"></i> Kebijakan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Hubungi Admin Pusat -->
    <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactModalLabel">
                        <i class="bi bi-headset me-2"></i>Pusat Bantuan Admin
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact" type="button" role="tab" aria-controls="pills-contact" aria-selected="true">
                                <i class="bi bi-telephone me-2"></i>Kontak Langsung
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-message-tab" data-bs-toggle="pill" data-bs-target="#pills-message" type="button" role="tab" aria-controls="pills-message" aria-selected="false">
                                <i class="bi bi-envelope me-2"></i>Kirim Pesan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-emergency-tab" data-bs-toggle="pill" data-bs-target="#pills-emergency" type="button" role="tab" aria-controls="pills-emergency" aria-selected="false">
                                <i class="bi bi-exclamation-triangle me-2"></i>Darurat
                            </button>
                        </li>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content" id="pills-tabContent">
                        <!-- Tab Kontak Langsung -->
                        <div class="tab-pane fade show active" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">
                            <p class="mb-4">Hubungi admin pusat melalui kontak berikut:</p>
                            
                            <div class="contact-item bg-light rounded-3 p-3 mb-3" onclick="makeCall('6281234567890')">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                                        <i class="bi bi-telephone-fill text-success fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold">Telepon</h6>
                                        <p class="mb-0 text-muted">+62 812-3456-7890</p>
                                    </div>
                                    <div class="ms-auto">
                                        <i class="bi bi-arrow-right-circle text-muted"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="contact-item bg-light rounded-3 p-3 mb-3" onclick="sendEmail()">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                        <i class="bi bi-envelope-fill text-primary fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold">Email</h6>
                                        <p class="mb-0 text-muted">admin@labuanbajo.go.id</p>
                                    </div>
                                    <div class="ms-auto">
                                        <i class="bi bi-arrow-right-circle text-muted"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="contact-item bg-light rounded-3 p-3 mb-3" onclick="openWhatsApp()">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                                        <i class="bi bi-whatsapp text-success fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold">WhatsApp</h6>
                                        <p class="mb-0 text-muted">+62 812-3456-7890</p>
                                    </div>
                                    <div class="ms-auto">
                                        <i class="bi bi-arrow-right-circle text-muted"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="contact-item bg-light rounded-3 p-3" onclick="openMaps()">
                                <div class="d-flex align-items-center">
                                    <div class="bg-danger bg-opacity-10 rounded-circle p-2 me-3">
                                        <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold">Kantor Pusat</h6>
                                        <p class="mb-0 text-muted">Jl. Soekarno-Hatta No. 1, Labuan Bajo</p>
                                    </div>
                                    <div class="ms-auto">
                                        <i class="bi bi-arrow-right-circle text-muted"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mt-4">
                                <i class="bi bi-clock me-2"></i>
                                <strong>Jam Operasional:</strong> Senin-Jumat (08:00-17:00 WITA)
                            </div>
                        </div>
                        
                        <!-- Tab Kirim Pesan -->
                        <div class="tab-pane fade" id="pills-message" role="tabpanel" aria-labelledby="pills-message-tab">
                            <?php if (isset($sukses)): ?>
                                <div class="alert alert-success" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <?= $sukses ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($error_msg)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <?= $error_msg ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama" name="nama" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="subjek" class="form-label">Subjek</label>
                                    <select class="form-select" id="subjek" name="subjek" required>
                                        <option value="">Pilih Subjek</option>
                                        <option value="lupa_password">Lupa Password</option>
                                        <option value="akun_terkunci">Akun Terkunci</option>
                                        <option value="kesalahan_login">Kesalahan Login</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="pesan" class="form-label">Pesan</label>
                                    <textarea class="form-control" id="pesan" name="pesan" rows="4" required placeholder="Jelaskan masalah yang Anda alami..."></textarea>
                                </div>
                                
                                <button type="submit" name="kirim_pesan" class="btn btn-primary w-100">
                                    <i class="bi bi-send me-2"></i>Kirim Pesan
                                </button>
                            </form>
                        </div>
                        
                        <!-- Tab Darurat -->
                        <div class="tab-pane fade" id="pills-emergency" role="tabpanel" aria-labelledby="pills-emergency-tab">
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>Hubungi Darurat</strong> - Jika Anda mengalami masalah kritis yang memerlukan penanganan segera
                            </div>
                            
                            <div class="contact-item bg-danger bg-opacity-10 border border-danger rounded-3 p-3 mb-3" onclick="makeEmergencyCall()">
                                <div class="d-flex align-items-center">
                                    <div class="bg-danger rounded-circle p-3 me-3">
                                        <i class="bi bi-telephone-fill text-white fs-3"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold text-danger">Hotline Darurat</h6>
                                        <p class="mb-0 text-danger fw-bold">112</p>
                                        <small class="text-muted">24/7 Available</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="contact-item bg-warning bg-opacity-10 border border-warning rounded-3 p-3 mb-3" onclick="makeEmergencyCall2()">
                                <div class="d-flex align-items-center">
                                    <div class="bg-warning rounded-circle p-3 me-3">
                                        <i class="bi bi-shield-exclamation text-white fs-3"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold text-warning">Admin Darurat</h6>
                                        <p class="mb-0 text-warning fw-bold">+62 811-1234-5678</p>
                                        <small class="text-muted">Available 24/7</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h6 class="fw-semibold mb-3">Situasi Darurat:</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="bi bi-check-circle text-danger me-2"></i>Sistem tidak dapat diakses semua user</li>
                                    <li class="mb-2"><i class="bi bi-check-circle text-danger me-2"></i>Data hilang atau corrupt</li>
                                    <li class="mb-2"><i class="bi bi-check-circle text-danger me-2"></i>Keamanan sistem terancam</li>
                                    <li class="mb-2"><i class="bi bi-check-circle text-danger me-2"></i>Error kritis di dashboard</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="copyContactInfo()">
                        <i class="bi bi-clipboard me-2"></i>Salin Kontak
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Bantuan (FAQ) -->
    <div class="modal fade" id="faqModal" tabindex="-1" aria-labelledby="faqModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="faqModalLabel">
                        <i class="bi bi-question-circle me-2"></i>Pusat Bantuan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    Bagaimana cara reset password?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Untuk reset password, Anda perlu menghubungi admin pusat melalui menu "Hubungi Admin Pusat" di halaman login. Pilih opsi "Kirim Pesan" dan jelaskan masalah Anda.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Saya tidak bisa login, apa yang salah?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Pastikan username dan password yang Anda masukkan sudah benar. Periksa juga Caps Lock dan pastikan tidak ada spasi yang tidak disengaja. Jika masih mengalami masalah, hubungi admin pusat.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contactModal">Hubungi Admin</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Kebijakan -->
    <div class="modal fade" id="policyModal" tabindex="-1" aria-labelledby="policyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="policyModalLabel">
                        <i class="bi bi-shield-check me-2"></i>Kebijakan Privasi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 class="fw-semibold">Pengumpulan Data</h6>
                    <p class="text-muted">Kami mengumpulkan informasi yang Anda berikan secara langsung saat mendaftar atau menggunakan layanan kami.</p>
                    
                    <h6 class="fw-semibold mt-3">Penggunaan Data</h6>
                    <p class="text-muted">Data Anda digunakan untuk menyediakan, mengoperasikan, dan meningkatkan layanan kami.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi untuk toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }
        
        // Fungsi untuk membuat panggilan telepon
        function makeCall(number) {
            window.location.href = `tel:+${number}`;
        }
        
        // Fungsi untuk membuka email
        function sendEmail() {
            window.location.href = 'mailto:admin@labuanbajo.go.id?subject=Bantuan%20Akun%20Sistem%20Posko';
        }
        
        // Fungsi untuk membuka WhatsApp
        function openWhatsApp() {
            window.open('https://wa.me/6281234567890?text=Halo,%20saya%20butuh%20bantuan%20untuk%20akun%20sistem%20posko', '_blank');
        }
        
        // Fungsi untuk membuka maps
        function openMaps() {
            window.open('https://maps.google.com/?q=Labuan+Bajo+Kantor+Pusat', '_blank');
        }
        
        // Fungsi untuk panggilan darurat
        function makeEmergencyCall() {
            window.location.href = 'tel:112';
        }
        
        // Fungsi untuk panggilan admin darurat
        function makeEmergencyCall2() {
            window.location.href = 'tel:+6281112345678';
        }
        
        // Fungsi untuk menyalin informasi kontak
        function copyContactInfo() {
            const contactInfo = `Admin Pusat Labuan Bajo
Telepon: +62 812-3456-7890
Email: admin@labuanbajo.go.id
WhatsApp: +62 812-3456-7890
Alamat: Jl. Soekarno-Hatta No. 1, Labuan Bajo`;
            
            navigator.clipboard.writeText(contactInfo).then(function() {
                alert('Informasi kontak berhasil disalin!');
            });
        }
    </script>
</body>
</html>