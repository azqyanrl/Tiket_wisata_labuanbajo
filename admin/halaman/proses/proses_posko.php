<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login/login.php');
    exit();
}
include '../../../database/konek.php';

// Tambah Admin Posko
if (isset($_POST['add_posko'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $lokasi = $_POST['lokasi'];
    $username = explode('@', $email)[0];

    $cek = $konek->prepare("SELECT id FROM users WHERE email = ?");
    $cek->bind_param("s", $email);
    $cek->execute();
    if ($cek->get_result()->num_rows > 0) {
        header("location:../manage_posko.php?pesan=email_ada");
    } else {
        $stmt = $konek->prepare("INSERT INTO users (username, email, password, nama_lengkap, role, lokasi) VALUES (?, ?, ?, ?, 'posko', ?)");
        $stmt->bind_param("sssss", $username, $email, $password, $nama, $lokasi);
        $stmt->execute();
        header("location:../manage_posko.php?pesan=berhasil_tambah");
    }
}

// Edit Admin Posko
if (isset($_POST['edit_posko'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $lokasi = $_POST['lokasi'];

    $sql = "UPDATE users SET nama_lengkap = ?, email = ?, lokasi = ?";
    $params = "sss";
    $values = [$nama, $email, $lokasi];

    // Jika checkbox reset password dicentang
    if (isset($_POST['reset_password'])) {
        $new_password = password_hash('123456', PASSWORD_DEFAULT);
        $sql .= ", password = ?";
        $params .= "s";
        $values[] = $new_password;
    }
    
    $sql .= " WHERE id = ?";
    $params .= "i";
    $values[] = $id;

    $stmt = $konek->prepare($sql);
    $stmt->bind_param($params, ...$values);
    $stmt->execute();
    header("location:../manage_posko.php?pesan=berhasil_edit");
}

// Hapus Admin Posko
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $konek->prepare("DELETE FROM users WHERE id = ? AND role = 'posko'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("location:../manage_posko.php?pesan=berhasil_hapus");
}
?>