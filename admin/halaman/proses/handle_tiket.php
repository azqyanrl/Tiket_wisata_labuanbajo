<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek role admin (sama seperti di file lain)
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Akses ditolak! Anda harus login sebagai admin.";
    header('location: ../../login/login_admin.php');
    exit;
}

// Sesuaikan path konek.php sesuai strukturmu.
// Jika file ini di admin/tiket/proses, path ke database biasanya '../../database/konek.php'
include '../../../database/konek.php';

// Pastikan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Metode request tidak valid.";
    header("Location: ../index.php?page=kelola_tiket");
    exit;
}

$editing = isset($_POST['id']) && is_numeric($_POST['id']);
$gambar_lama = '';

try {
    // Ambil gambar lama saat editing
    if ($editing) {
        $stmt = $konek->prepare("SELECT gambar FROM tiket WHERE id = ?");
        $stmt->bind_param("i", $_POST['id']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $gambar_lama = $row['gambar'];
        }
        $stmt->close();
    }

    // Folder target upload (pastikan path benar)
    $target_dir = __DIR__ . '/../../../assets/images/tiket/';
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // Default: tetap pakai gambar lama
    $gambar_baru = $gambar_lama;

    // Jika ada file diupload dan valid
    if (isset($_FILES['gambar']) && isset($_FILES['gambar']['error']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $check = getimagesize($_FILES['gambar']['tmp_name']);
        if ($check === false) {
            throw new Exception("File yang diupload bukan gambar.");
        }

        $file_extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $file_name = 'tiket_' . time() . '.' . $file_extension;
        $target_file = $target_dir . $file_name;

        if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
            throw new Exception("Gagal mengupload gambar. Periksa permission folder.");
        }

        // Hapus gambar lama jika ada
        if ($editing && !empty($gambar_lama)) {
            $file_lama_path = $target_dir . $gambar_lama;
            if (file_exists($file_lama_path)) {
                @unlink($file_lama_path);
            }
        }

        $gambar_baru = $file_name;
    } elseif (!$editing) {
        // Jika tambah baru dan tidak mengupload gambar -> error
        throw new Exception("Gambar wajib diupload untuk tiket baru.");
    }

    // Sanitasi tipe trip, angka kosong ke 0
    $tipe_trip = null;
    if (isset($_POST['tipe_trip']) && $_POST['tipe_trip'] !== '') {
        $tipe_trip = (int) $_POST['tipe_trip'];
    }
    $kapasitas = !empty($_POST['kapasitas']) ? (int) $_POST['kapasitas'] : 0;
    $stok_default = !empty($_POST['stok_default']) ? (int) $_POST['stok_default'] : 0;

    // Simpan ke database
    if ($editing) {
        $sql = "UPDATE tiket SET 
                    nama_paket = ?, deskripsi = ?, harga = ?, stok = ?, stok_default = ?, 
                    durasi = ?, kategori_id = ?, status = ?, gambar = ?, lokasi = ?,
                    fasilitas = ?, itinerary = ?, syarat = ?, tipe_trip_id = ?, 
                    kapasitas = ?, jadwal = ?
                WHERE id = ?";
        $stmt = $konek->prepare($sql);
        $stmt->bind_param(
            "ssdiisssssssisssi",
            $_POST['nama_paket'],
            $_POST['deskripsi'],
            $_POST['harga'],
            $_POST['stok'],
            $stok_default,
            $_POST['durasi'],
            $_POST['kategori_id'],
            $_POST['status'],
            $gambar_baru,
            $_POST['lokasi'],
            $_POST['fasilitas'],
            $_POST['itinerary'],
            $_POST['syarat'],
            $tipe_trip,
            $kapasitas,
            $_POST['jadwal'],
            $_POST['id']
        );
        $stmt->execute();
        $_SESSION['success_message'] = "Tiket berhasil diperbarui.";
    } else {
        $stmt = $konek->prepare("INSERT INTO tiket 
            (nama_paket, deskripsi, harga, stok, stok_default, durasi, kategori_id, status, gambar, lokasi, fasilitas, itinerary, syarat, tipe_trip_id, kapasitas, jadwal) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssdiisssssssisss",
            $_POST['nama_paket'],
            $_POST['deskripsi'],
            $_POST['harga'],
            $_POST['stok'],
            $stok_default,
            $_POST['durasi'],
            $_POST['kategori_id'],
            $_POST['status'],
            $gambar_baru,
            $_POST['lokasi'],
            $_POST['fasilitas'],
            $_POST['itinerary'],
            $_POST['syarat'],
            $tipe_trip,
            $kapasitas,
            $_POST['jadwal']
        );
        $stmt->execute();
        $_SESSION['success_message'] = "Tiket baru berhasil ditambahkan.";
    }

    $stmt->close();
    $konek->close();

    header("Location: ../index.php?page=kelola_tiket");
    exit;

} catch (Exception $e) {
    $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
    header("Location: ../index.php?page=kelola_tiket");
    exit;
}
