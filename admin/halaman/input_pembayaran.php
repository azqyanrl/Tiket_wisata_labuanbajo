<?php
ob_start(); 
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('location: ../../login/login.php');
    exit;
}

// Sesuaikan path include karena file sudah dipindahkan
include '../../database/konek.php';
include '../../includes/boot.php';

// Cari pemesanan berdasarkan kode booking
 $kode_booking = '';
 $pemesanan_data = null;
 $user_data = null;
 $tiket_data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cari_booking'])) {
    $kode_booking = $_POST['kode_booking'];
    
    $query_pemesanan = $konek->prepare("SELECT p.*, u.nama_lengkap, u.email, u.no_hp, t.nama_paket, t.harga, t.gambar 
                                      FROM pemesanan p 
                                      JOIN users u ON p.user_id = u.id 
                                      JOIN tiket t ON p.tiket_id = t.id 
                                      WHERE p.kode_booking = ?");
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
        $error_message = "Kode booking tidak ditemukan";
    }
}

// Proses pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_pembayaran'])) {
    $pemesanan_id = $_POST['pemesanan_id'];
    $metode_pembayaran = $_POST['metode_pembayaran'];
    
    // 1. Update status pemesanan menjadi 'dibayar'
    $update_pemesanan = $konek->prepare("UPDATE pemesanan SET status = 'dibayar', metode_pembayaran = ? WHERE id = ?");
    $update_pemesanan->bind_param("si", $metode_pembayaran, $pemesanan_id);
    $update_pemesanan->execute();
    
    // 2. Ambil data pemesanan untuk membuat transaksi
    $query_get_pemesanan = $konek->prepare("SELECT user_id, tiket_id, jumlah_tiket, total_harga FROM pemesanan WHERE id = ?");
    $query_get_pemesanan->bind_param("i", $pemesanan_id);
    $query_get_pemesanan->execute();
    $pemesanan_data_for_transaction = $query_get_pemesanan->get_result()->fetch_assoc();
    
    if ($pemesanan_data_for_transaction) {
        // 3. Buat entri baru di tabel transactions
        $query_insert_transaction = $konek->prepare("INSERT INTO transactions (user_id, ticket_id, jumlah_tiket, total_harga, status) VALUES (?, ?, ?, ?, 'paid')");
        $query_insert_transaction->bind_param("iiid", $pemesanan_data_for_transaction['user_id'], $pemesanan_data_for_transaction['tiket_id'], $pemesanan_data_for_transaction['jumlah_tiket'], $pemesanan_data_for_transaction['total_harga']);
        $query_insert_transaction->execute();
        
        // 4. Ambil ID transaksi yang baru dibuat
        $transaction_id = $konek->insert_id;
        
        // 5. Cek apakah sudah ada data di tabel payments untuk pemesanan ini
        $query_cek_payment = $konek->prepare("SELECT id FROM payments WHERE pemesanan_id = ?");
        $query_cek_payment->bind_param("i", $pemesanan_id);
        $query_cek_payment->execute();
        $result_cek_payment = $query_cek_payment->get_result();

        if ($result_cek_payment->num_rows > 0) {
            // Jika sudah ada, update statusnya dan transaction_id
            $query_payment = $konek->prepare("UPDATE payments SET transaction_id = ?, status = 'verified' WHERE pemesanan_id = ?");
            $query_payment->bind_param("ii", $transaction_id, $pemesanan_id);
            $query_payment->execute();
        } else {
            // Jika belum ada, buat entri baru di payments
            $query_payment = $konek->prepare("INSERT INTO payments (pemesanan_id, transaction_id, bukti_transfer, status) VALUES (?, ?, ?, 'verified')");
            $bukti_transfer = "Pembayaran langsung oleh admin - " . $metode_pembayaran;
            $query_payment->bind_param("iis", $pemesanan_id, $transaction_id, $bukti_transfer);
            $query_payment->execute();
        }
        
        $_SESSION['success_message'] = "Pembayaran berhasil dicatat untuk kode booking: " . $_POST['kode_booking'];
        header("Location: index.php?page=kelola_pemesanan");
        exit();
        
    } else {
        $_SESSION['error_message'] = "Gagal memproses pembayaran. Data pemesanan tidak ditemukan.";
        header("Location: index.php?page=input_pembayaran");
        exit();
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Input Pembayaran</h1>
</div>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <h5>Cari Pemesanan Berdasarkan Kode Booking</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-8">
                    <label for="kode_booking" class="form-label">Kode Booking</label>
                    <input type="text" class="form-control" id="kode_booking" name="kode_booking" value="<?php echo $kode_booking; ?>" required>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" name="cari_booking" class="btn btn-primary w-100">Cari</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($pemesanan_data): ?>
<div class="card mb-4">
    <div class="card-header">
        <h5>Detail Pemesanan</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Informasi Pemesanan</h6>
                <p><strong>Kode Booking:</strong> <?php echo $pemesanan_data['kode_booking']; ?></p>
                <p><strong>Tanggal Kunjungan:</strong> <?php echo date('d/m/Y', strtotime($pemesanan_data['tanggal_kunjungan'])); ?></p>
                <p><strong>Jumlah Tiket:</strong> <?php echo $pemesanan_data['jumlah_tiket']; ?></p>
                <p><strong>Total Harga:</strong> Rp <?php echo number_format($pemesanan_data['total_harga'], 0, ',', '.'); ?></p>
                <p><strong>Status:</strong> <span class="badge bg-<?php echo ($pemesanan_data['status'] == 'pending') ? 'warning' : 'info'; ?>"><?php echo ucfirst($pemesanan_data['status']); ?></span></p>
            </div>
            <div class="col-md-6">
                <h6>Informasi Pengguna</h6>
                <p><strong>Nama Lengkap:</strong> <?php echo $user_data['nama_lengkap']; ?></p>
                <p><strong>Email:</strong> <?php echo $user_data['email']; ?></p>
                <p><strong>No. HP:</strong> <?php echo $user_data['no_hp']; ?></p>
                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalCetakUser">
                    <i class="bi bi-printer"></i> Cetak Data User
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Proses Pembayaran</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="pemesanan_id" value="<?php echo $pemesanan_data['id']; ?>">
            <input type="hidden" name="kode_booking" value="<?php echo $pemesanan_data['kode_booking']; ?>">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="metode_pembayaran" class="form-label">Metode Pembayaran</label>
                    <select class="form-select" id="metode_pembayaran" name="metode_pembayaran" required>
                        <option value="">Pilih Metode Pembayaran</option>
                        <option value="tunai">Tunai</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="kartu_kredit">Kartu Kredit</option>
                        <option value="e-wallet">E-Wallet</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" name="proses_pembayaran" class="btn btn-primary">Proses Pembayaran</button>
                    <a href="index.php?page=kelola_pemesanan" class="btn btn-secondary">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Modal Cetak User -->
<div class="modal fade" id="modalCetakUser" tabindex="-1" aria-labelledby="modalCetakUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCetakUserLabel">Cetak Data User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="printableArea">
                    <div class="text-center mb-4">
                        <h4>Labuan Bajo Tourism</h4>
                        <p>Data Pengguna</p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Informasi Pribadi</h6>
                            <table class="table table-sm">
                                <tr><td width="30%">Nama Lengkap</td><td width="5%">:</td><td><?php echo $user_data['nama_lengkap']; ?></td></tr>
                                <tr><td>Email</td><td>:</td><td><?php echo $user_data['email']; ?></td></tr>
                                <tr><td>No. HP</td><td>:</td><td><?php echo $user_data['no_hp']; ?></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Informasi Pemesanan</h6>
                            <table class="table table-sm">
                                <tr><td width="30%">Kode Booking</td><td width="5%">:</td><td><?php echo $pemesanan_data['kode_booking']; ?></td></tr>
                                <tr><td>Tanggal Kunjungan</td><td>:</td><td><?php echo date('d/m/Y', strtotime($pemesanan_data['tanggal_kunjungan'])); ?></td></tr>
                                <tr><td>Jumlah Tiket</td><td>:</td><td><?php echo $pemesanan_data['jumlah_tiket']; ?></td></tr>
                                <tr><td>Total Harga</td><td>:</td><td>Rp <?php echo number_format($pemesanan_data['total_harga'], 0, ',', '.'); ?></td></tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <p class="small text-muted">Dicetak pada tanggal: <?php echo date('d/m/Y H:i:s'); ?></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="printDiv('printableArea')">Cetak</button>
            </div>
        </div>
    </div>
</div>

<script>
function printDiv(divName) {
    var printContents = document.getElementById(divName).innerHTML;
    var originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
}
</script>

<?php ob_end_flush();