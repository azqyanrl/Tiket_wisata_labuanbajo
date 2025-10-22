<?php
// admin/proses/tiket_simpan.php
session_start();
include '../../../database/konek.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Akses ditolak! Anda harus login sebagai admin.";
    header("Location: ../../login/login.php");
    exit;
}

// Proses hanya untuk request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $nama_paket = trim($_POST['nama_paket'] ?? '');
    $deskripsi  = trim($_POST['deskripsi'] ?? '');
    $harga      = floatval($_POST['harga'] ?? 0);
    $stok       = intval($_POST['stok'] ?? 0);
    $durasi     = trim($_POST['durasi'] ?? '');
    $kategori   = trim($_POST['kategori'] ?? '');
    $status     = trim($_POST['status'] ?? '');

    // Validasi dasar
    if (empty($nama_paket) || empty($deskripsi) || $harga <= 0) {
        $_SESSION['error_message'] = "Data tidak lengkap atau tidak valid!";
        header("Location: ../index.php?page=kelola_tiket");
        exit;
    }

    // Upload gambar
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../../assets/images/tiket/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $gambar = time() . '_' . basename($_FILES['gambar']['name']);
        move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_dir . $gambar);
    }

    // Update atau Insert
    if ($id > 0) {
        // UPDATE
        $sql = "UPDATE tiket SET nama_paket=?, deskripsi=?, harga=?, stok=?, durasi=?, kategori=?, status=?";
        $params = [$nama_paket, $deskripsi, $harga, $stok, $durasi, $kategori, $status];
        $types = "ssdiiss";

        if (!empty($gambar)) {
            $sql .= ", gambar=?";
            $params[] = $gambar;
            $types .= "s";
        }

        $sql .= " WHERE id=?";
        $params[] = $id;
        $types .= "i";

        $stmt = $konek->prepare($sql);
        $stmt->bind_param($types, ...$params);
    } else {
        // INSERT
        $sql = "INSERT INTO tiket (nama_paket, deskripsi, harga, stok, durasi, kategori, status, gambar)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $konek->prepare($sql);
        $stmt->bind_param("ssdissss", $nama_paket, $deskripsi, $harga, $stok, $durasi, $kategori, $status, $gambar);
    }

    // Eksekusi
    if ($stmt->execute()) {
        $_SESSION['success_message'] = ($id > 0)
            ? "Tiket berhasil diperbarui!"
            : "Tiket baru berhasil ditambahkan!";
    } else {
        $_SESSION['error_message'] = "Gagal menyimpan tiket: " . $stmt->error;
    }

    $stmt->close();
    header("Location: ../index.php?page=kelola_tiket");
    exit;
}
?>
