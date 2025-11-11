<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'posko') {
    $_SESSION['error_message'] = 'Akses ditolak!';
    header('Location: ../login/login.php');
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';

$lokasi_admin = $_SESSION['lokasi'] ?? '';
$id_admin = $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;

// PROSES ACTION (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['action'])) {
    $pemesanan_id = intval($_POST['id']);
    $action = $_POST['action'];
    $metode_pembayaran = $_POST['metode_pembayaran'] ?? '';

    $cek = $konek->prepare("
        SELECT p.id, p.status
        FROM pemesanan p
        JOIN tiket t ON p.tiket_id = t.id
        WHERE p.id = ? AND t.lokasi = ?
        LIMIT 1
    ");
    $cek->bind_param("is", $pemesanan_id, $lokasi_admin);
    $cek->execute();
    $row = $cek->get_result()->fetch_assoc();

    if (!$row) {
        $_SESSION['error_message'] = "Pemesanan tidak ditemukan atau bukan wilayah Anda.";
        header("Location: index.php?page=cari_tiket");
        exit;
    }

    $status_sekarang = $row['status'];

    if ($action === 'confirm') {
        if ($status_sekarang !== 'pending') {
            $_SESSION['error_message'] = "Tidak dapat verifikasi: status bukan pending.";
            header("Location: index.php?page=verifikasi_tiket&id={$pemesanan_id}");
            exit;
        }
        if (empty($metode_pembayaran)) {
            $_SESSION['error_message'] = "Metode pembayaran harus dipilih.";
            header("Location: index.php?page=verifikasi_tiket&id={$pemesanan_id}");
            exit;
        }
        $new_status = 'dibayar';
        $catatan = 'Pembayaran diverifikasi oleh posko dengan metode: ' . $metode_pembayaran;
    } elseif ($action === 'reject') {
        if ($status_sekarang !== 'pending') {
            $_SESSION['error_message'] = "Tidak dapat batalkan: status bukan pending.";
            header("Location: index.php?page=verifikasi_tiket&id={$pemesanan_id}");
            exit;
        }
        $new_status = 'batal';
        $catatan = 'Pembayaran ditolak oleh posko';
        $metode_pembayaran = 'Manual';
    } elseif ($action === 'complete') {
        if ($status_sekarang !== 'dibayar') {
            $_SESSION['error_message'] = "Tidak dapat selesai: status harus dibayar terlebih dahulu.";
            header("Location: index.php?page=verifikasi_tiket&id={$pemesanan_id}");
            exit;
        }
        $new_status = 'selesai';
        $catatan = 'Pesanan diselesaikan oleh posko';
        $metode_pembayaran = 'Manual';
    } else {
        $_SESSION['error_message'] = "Aksi tidak valid.";
        header("Location: index.php?page=cari_tiket");
        exit;
    }

    $konek->begin_transaction();
    
    try {
        $update = $konek->prepare("UPDATE pemesanan SET status = ?, metode_pembayaran = ?, updated_at = NOW() WHERE id = ?");
        $update->bind_param("ssi", $new_status, $metode_pembayaran, $pemesanan_id);
        $update->execute();

        if ($update->affected_rows > 0) {
            if ($action === 'confirm') {
                $query_get_pemesanan = $konek->prepare("
                    SELECT user_id, tiket_id, jumlah_tiket, total_harga 
                    FROM pemesanan 
                    WHERE id = ?
                ");
                $query_get_pemesanan->bind_param("i", $pemesanan_id);
                $query_get_pemesanan->execute();
                $pemesanan_data = $query_get_pemesanan->get_result()->fetch_assoc();

                if ($pemesanan_data) {
                    $query_insert_transaction = $konek->prepare("
                        INSERT INTO transactions (user_id, ticket_id, jumlah_tiket, total_harga, status)
                        VALUES (?, ?, ?, ?, 'paid')
                    ");
                    $query_insert_transaction->bind_param(
                        "iiid",
                        $pemesanan_data['user_id'],
                        $pemesanan_data['tiket_id'],
                        $pemesanan_data['jumlah_tiket'],
                        $pemesanan_data['total_harga']
                    );
                    $query_insert_transaction->execute();

                    $transaction_id = $konek->insert_id;

                    $query_cek_payment = $konek->prepare("SELECT id FROM payments WHERE pemesanan_id = ?");
                    $query_cek_payment->bind_param("i", $pemesanan_id);
                    $query_cek_payment->execute();
                    $result_cek_payment = $query_cek_payment->get_result();

                    if ($result_cek_payment->num_rows > 0) {
                        $query_payment = $konek->prepare("
                            UPDATE payments 
                            SET transaction_id = ?, status = 'verified'
                            WHERE pemesanan_id = ?
                        ");
                        $query_payment->bind_param("ii", $transaction_id, $pemesanan_id);
                        $query_payment->execute();
                    } else {
                        $bukti_transfer = "Pembayaran langsung oleh posko - " . $metode_pembayaran;
                        $query_payment = $konek->prepare("
                            INSERT INTO payments (pemesanan_id, transaction_id, bukti_transfer, status)
                            VALUES (?, ?, ?, 'verified')
                        ");
                        $query_payment->bind_param("iis", $pemesanan_id, $transaction_id, $bukti_transfer);
                        $query_payment->execute();
                    }
                }
            }

            $his = $konek->prepare("
                INSERT INTO verifikasi_history (pemesanan_id, admin_id, metode_pembayaran, status, catatan, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $his->bind_param("iisss", $pemesanan_id, $id_admin, $metode_pembayaran, $new_status, $catatan);
            $his->execute();

            $konek->commit();
            $_SESSION['success_message'] = "Status pesanan berhasil diubah menjadi '{$new_status}'.";
        } else {
            $konek->rollback();
            $_SESSION['error_message'] = "Tidak ada perubahan status (atau query gagal).";
        }
    } catch (Exception $e) {
        $konek->rollback();
        $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
    }

    header("Location: index.php?page=verifikasi_tiket&id={$pemesanan_id}");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php?page=cari_tiket");
    exit;
}

$id = intval($_GET['id']);

$q = $konek->prepare("
    SELECT p.*, u.nama_lengkap, u.email, u.no_hp AS no_telp, t.nama_paket, t.lokasi, t.harga
    FROM pemesanan p
    JOIN users u ON p.user_id = u.id
    JOIN tiket t ON p.tiket_id = t.id
    WHERE p.id = ?
    LIMIT 1
");
$q->bind_param("i", $id);
$q->execute();
$data = $q->get_result()->fetch_assoc();

if (!$data) {
    $_SESSION['error_message'] = "Data tidak ditemukan.";
    header("Location: index.php?page=cari_tiket");
    exit;
}

$statusClass = match($data['status']) {
    'pending' => 'bg-warning text-dark',
    'dibayar' => 'bg-info text-dark',
    'selesai' => 'bg-success',
    'batal' => 'bg-danger',
    default => 'bg-secondary'
};
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-check2-circle text-success me-2"></i>Verifikasi Tiket</h2>
        <a href="index.php?page=cari_tiket" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-ticket-perforated me-2"></i>Detail Pemesanan</h5>
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-6">
                    <p><strong>Kode Booking:</strong> <?= htmlspecialchars($data['kode_booking']) ?></p>
                    <p><strong>Nama Pemesan:</strong> <?= htmlspecialchars($data['nama_lengkap']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($data['email']) ?></p>
                    <p><strong>No. Telepon:</strong> <?= htmlspecialchars($data['no_telp'] ?? '-') ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Paket:</strong> <?= htmlspecialchars($data['nama_paket']) ?></p>
                    <p><strong>Lokasi Posko:</strong> <?= htmlspecialchars($data['lokasi']) ?></p>
                    <p><strong>Tanggal Kunjungan:</strong> <?= date('d/m/Y', strtotime($data['tanggal_kunjungan'])) ?></p>
                    <p><strong>Status:</strong> <span class="badge <?= $statusClass ?>"><?= ucfirst(htmlspecialchars($data['status'])) ?></span></p>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-4"><p><strong>Jumlah Tiket:</strong> <?= (int)$data['jumlah_tiket'] ?></p></div>
                <div class="col-md-4"><p><strong>Harga per Tiket:</strong> Rp <?= number_format($data['harga'], 0, ',', '.') ?></p></div>
                <div class="col-md-4"><p><strong>Total Bayar:</strong> <span class="fw-bold text-success">Rp <?= number_format($data['total_harga'], 0, ',', '.') ?></span></p></div>
            </div>
        </div>
    </div>

    <?php if ($data['status'] === 'pending'): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Proses Pembayaran</h5>
            </div>
            <div class="card-body d-flex gap-3 flex-wrap">
                <!-- Form Konfirmasi -->
                <form method="POST" action="index.php?page=verifikasi_tiket" onsubmit="return confirm('Konfirmasi verifikasi pesanan ini?')">
                    <input type="hidden" name="id" value="<?= $data['id'] ?>">
                    <input type="hidden" name="action" value="confirm">
                    <div class="mb-3">
                        <label for="metode_pembayaran" class="form-label">Metode Pembayaran</label>
                        <select class="form-select" id="metode_pembayaran" name="metode_pembayaran" required>
                            <option value="">Pilih Metode Pembayaran</option>
                            <option value="tunai">Tunai</option>
                            <option value="Qris">Qris</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i> Konfirmasi Pembayaran</button>
                </form>

                <!-- Form Batalkan -->
                <form method="POST" action="index.php?page=verifikasi_tiket" onsubmit="return confirm('Batalkan pesanan ini?')">
                    <input type="hidden" name="id" value="<?= $data['id'] ?>">
                    <input type="hidden" name="action" value="reject">
                    <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle me-1"></i> Batalkan</button>
                </form>
            </div>
        </div>
    <?php elseif ($data['status'] === 'dibayar'): ?>
        <div class="d-flex gap-3">
            <form method="POST" action="index.php?page=verifikasi_tiket" onsubmit="return confirm('Tandai pesanan ini sebagai selesai?')">
                <input type="hidden" name="id" value="<?= $data['id'] ?>">
                <input type="hidden" name="action" value="complete">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check2-all me-1"></i> Selesai</button>
            </form>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Pesanan ini sudah <strong><?= htmlspecialchars($data['status']) ?></strong>. Tidak ada aksi tersedia.</div>
    <?php endif; ?>
</div>
