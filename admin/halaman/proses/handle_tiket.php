<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Cek login admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Akses ditolak! Anda harus login sebagai admin.";
    header('location: ../../login/login_admin.php');
    exit;
}

include '../../../database/konek.php';

// Pastikan metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Metode request tidak valid.";
    header("Location: ../index.php?page=kelola_tiket");
    exit;
}

$editing = isset($_POST['id']) && is_numeric($_POST['id']);
$gambar_lama = '';

try {
    // Ambil data gambar lama jika sedang edit
    if ($editing) {
        $stmt = $konek->prepare("SELECT gambar FROM tiket WHERE id = ?");
        $stmt->bind_param("i", $_POST['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $tiket_lama = $result->fetch_assoc();
            $gambar_lama = $tiket_lama['gambar'];
        }
        $stmt->close();
    }

    // ✅ Tentukan folder upload absolut (tidak akan nyasar ke /admin/)
    $target_dir = realpath(__DIR__ . '/../../../assets/images/tiket/');
    if ($target_dir === false) {
        // Jika folder belum ada, buat dulu
        $target_dir = __DIR__ . '/../../../assets/images/tiket/';
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
    }
    $target_dir .= '/';

    $gambar_baru = $gambar_lama;

    // Proses upload gambar baru
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $check = getimagesize($_FILES['gambar']['tmp_name']);
        if ($check === false) {
            throw new Exception("File yang diupload bukan gambar.");
        }

        $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $file_name = 'tiket_' . time() . '.' . $file_extension;
        $target_file = $target_dir . $file_name;

        if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
            throw new Exception("Gagal mengupload gambar. Periksa permission folder.");
        }

        $gambar_baru = $file_name;

        // Hapus gambar lama jika diganti
        if ($editing && !empty($gambar_lama)) {
            $file_lama_path = $target_dir . $gambar_lama;
            if (file_exists($file_lama_path)) {
                unlink($file_lama_path);
            }
        }
    } elseif (!$editing) {
        throw new Exception("Gambar wajib diupload untuk tiket baru.");
    }

    // ✅ Simpan data ke database
    if ($editing) {
        $sql = "UPDATE tiket SET 
                    nama_paket = ?, deskripsi = ?, harga = ?, stok = ?, stok_default = ?, 
                    durasi = ?, kategori_id = ?, status = ?, gambar = ?, 
                    fasilitas = ?, itinerary = ?, syarat = ? 
                WHERE id = ?";

        $stmt = $konek->prepare($sql);
        $stmt->bind_param(
            "ssdiisssssssi",
            $_POST['nama_paket'],
            $_POST['deskripsi'],
            $_POST['harga'],
            $_POST['stok'],
            $_POST['stok_default'],
            $_POST['durasi'],
            $_POST['kategori_id'],
            $_POST['status'],
            $gambar_baru,
            $_POST['fasilitas'],
            $_POST['itinerary'],
            $_POST['syarat'],
            $_POST['id']
        );
        $stmt->execute();
        $_SESSION['success_message'] = "Tiket berhasil diperbarui.";
    } else {
        $stmt = $konek->prepare("INSERT INTO tiket 
            (nama_paket, deskripsi, harga, stok, stok_default, durasi, kategori_id, status, gambar, fasilitas, itinerary, syarat) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssdiisssssss",
            $_POST['nama_paket'],
            $_POST['deskripsi'],
            $_POST['harga'],
            $_POST['stok'],
            $_POST['stok_default'],
            $_POST['durasi'],
            $_POST['kategori_id'],
            $_POST['status'],
            $gambar_baru,
            $_POST['fasilitas'],
            $_POST['itinerary'],
            $_POST['syarat']
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
?>
