<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('location: ../login/login.php');
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';

$kode_booking = $_GET['kode'] ?? '';
$pemesanan_data = null;
$user_data = null;
$tiket_data = null;

if ($kode_booking) {
    $query_pemesanan = $konek->prepare("
        SELECT p.*, u.nama_lengkap, u.email, u.no_hp, t.nama_paket, t.harga, t.gambar 
        FROM pemesanan p 
        JOIN users u ON p.user_id = u.id 
        JOIN tiket t ON p.tiket_id = t.id 
        WHERE p.kode_booking = ?
        LIMIT 1
    ");
    $query_pemesanan->bind_param("s", $kode_booking);
    $query_pemesanan->execute();
    $result_pemesanan = $query_pemesanan->get_result();

    if ($result_pemesanan->num_rows > 0) {
        $pemesanan_data = $result_pemesanan->fetch_assoc();
        $user_data = [
            'nama_lengkap' => $pemesanan_data['nama_lengkap'],
            'email' => $pemesanan_data['email'],
            'no_hp' => $pemesanan_data['no_hp']
        ];
        $tiket_data = [
            'nama_paket' => $pemesanan_data['nama_paket'],
            'harga' => $pemesanan_data['harga'],
            'gambar' => $pemesanan_data['gambar']
        ];
    } else {
        $error_message = "Kode booking tidak ditemukan.";
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Input Pembayaran</h1>
</div>

<?php if (isset($error_message)): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <h5>Cari Pemesanan Berdasarkan Kode Booking</h5>
    </div>
    <div class="card-body">
        <div class="col-md-12 position-relative">
            <label for="kode_booking" class="form-label">Kode Booking</label>
            <input type="text" class="form-control" id="kode_booking" name="kode_booking"
                placeholder="Ketik minimal 3 huruf kode booking..." autocomplete="off"
                value="<?php echo htmlspecialchars($kode_booking); ?>">
            <div id="hasil_pencarian" class="list-group position-absolute w-100 mt-1 shadow-sm" style="z-index: 1000;"></div>
        </div>
    </div>
</div>

<?php if ($pemesanan_data): ?>
<div class="card mb-4">
    <div class="card-header"><h5>Detail Pemesanan</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Informasi Pemesanan</h6>
                <p><strong>Kode Booking:</strong> <?= htmlspecialchars($pemesanan_data['kode_booking']); ?></p>
                <p><strong>Tanggal Kunjungan:</strong> <?= date('d/m/Y', strtotime($pemesanan_data['tanggal_kunjungan'])); ?></p>
                <p><strong>Jumlah Tiket:</strong> <?= htmlspecialchars($pemesanan_data['jumlah_tiket']); ?></p>
                <p><strong>Total Harga:</strong> Rp <?= number_format($pemesanan_data['total_harga'], 0, ',', '.'); ?></p>
                <p><strong>Status:</strong>
                    <span class="badge bg-<?= ($pemesanan_data['status'] == 'pending') ? 'warning' : 'info'; ?>">
                        <?= ucfirst(htmlspecialchars($pemesanan_data['status'])); ?>
                    </span>
                </p>
            </div>
            <div class="col-md-6">
                <h6>Informasi Pengguna</h6>
                <p><strong>Nama Lengkap:</strong> <?= htmlspecialchars($user_data['nama_lengkap']); ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user_data['email']); ?></p>
                <p><strong>No. HP:</strong> <?= htmlspecialchars($user_data['no_hp']); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5>Proses Pembayaran</h5></div>
    <div class="card-body">
        <form method="POST" action="proses/proses_pembayaran.php">
            <input type="hidden" name="pemesanan_id" value="<?= htmlspecialchars($pemesanan_data['id']); ?>">
            <input type="hidden" name="kode_booking" value="<?= htmlspecialchars($pemesanan_data['kode_booking']); ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="metode_pembayaran" class="form-label">Metode Pembayaran</label>
                    <select class="form-select" id="metode_pembayaran" name="metode_pembayaran" required>
                        <option value="">Pilih Metode Pembayaran</option>
                        <option value="tunai">Tunai</option>
                        <option value="Qris">Qris</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Proses Pembayaran</button>
                    <a href="index.php?page=kelola_pemesanan" class="btn btn-secondary">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
document.getElementById('kode_booking').addEventListener('input', function() {
    const kode = this.value.trim();
    const hasilDiv = document.getElementById('hasil_pencarian');

    if (kode.length < 3) {
        hasilDiv.innerHTML = '';
        return;
    }

    fetch('ajax_cari_booking.php?kode_booking=' + encodeURIComponent(kode))
        .then(res => res.json())
        .then(data => {
            hasilDiv.innerHTML = '';
            if (data.success && data.data.length > 0) {
                data.data.forEach(item => {
                    const el = document.createElement('a');
                    el.href = '?page=input_pembayaran&kode=' + item.kode_booking;
                    el.className = 'list-group-item list-group-item-action';
                    el.innerHTML = `
                        <div><strong>${item.kode_booking}</strong> — ${item.nama_lengkap}</div>
                        <small>${item.nama_paket} | Status: ${item.status}</small>
                    `;
                    hasilDiv.appendChild(el);
                });
            } else {
                hasilDiv.innerHTML = `<div class="list-group-item text-muted">Tidak ada hasil ditemukan.</div>`;
            }
        })
        .catch(() => {
            hasilDiv.innerHTML = `<div class="list-group-item text-danger">Gagal memuat data.</div>`;
        });
});
</script>
