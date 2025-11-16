<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'posko') {
    echo "<script>alert('Akses ditolak!'); document.location.href='../login/login.php';</script>";
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';

$lokasi_admin = $_SESSION['lokasi'];
?>


<!-- 🔍 Form Pencarian -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="bi bi-search me-2"></i>Cari Pemesanan Berdasarkan Kode Booking</h5>
    </div>
    <div class="card-body">
        <div class="input-group">
            <input type="text" id="kode_booking" class="form-control" placeholder="Ketik kode booking..." autofocus>
            <span class="input-group-text"><i class="bi bi-search"></i></span>
        </div>
    </div>
</div>

<!-- 📋 Hasil Pencarian -->
<div id="hasil_pencarian" class="table-responsive mb-4">
    <div class="text-center text-muted p-3">Masukkan kode booking untuk memulai pencarian.</div>
</div>

<!-- 📋 Tabel Semua Pesanan Pending -->
<div class="card shadow-sm">
    <div class="card-header bg-primary text-light">
        <h5 class="mb-0"><i class="bi bi-hourglass-split"></i> Daftar Pesanan Pending</h5>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Kode Booking</th>
                    <th>Pelanggan</th>
                    <th>Paket</th>
                    <th>Tanggal</th>
                    <th>Jumlah</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $konek->prepare("
                    SELECT p.*, u.nama_lengkap, t.nama_paket
                    FROM pemesanan p
                    JOIN users u ON p.user_id = u.id
                    JOIN tiket t ON p.tiket_id = t.id
                    WHERE t.lokasi = ? AND p.status = 'pending'
                    ORDER BY p.created_at DESC
                ");
                $stmt->bind_param("s", $lokasi_admin);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['kode_booking']) ?></td>
                    <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                    <td><?= htmlspecialchars($row['nama_paket']) ?></td>
                    <td><?= date('d/m/Y', strtotime($row['tanggal_kunjungan'])) ?></td>
                    <td><?= (int)$row['jumlah_tiket'] ?></td>
                    <td>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                    <td>
                        <a href="index.php?page=verifikasi_tiket&id=<?= urlencode($row['id']) ?>" 
                           class="btn btn-sm btn-success">
                           <i class="bi bi-check-circle"></i> Verifikasi
                        </a>

                        <form method="POST" action="index.php?page=verifikasi_tiket" style="display:inline-block;" 
                              onsubmit="return confirm('Batalkan pesanan ini?')">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="bi bi-x-circle"></i> Batal
                            </button>
                        </form>
                    </td>
                </tr>
                <?php
                    endwhile;
                else:
                    echo "<tr><td colspan='8' class='text-center text-muted'>Tidak ada pesanan pending saat ini.</td></tr>";
                endif;
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ✅ AJAX Script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    $("#kode_booking").on("keyup", function(){
        let kode = $(this).val().trim();
        if(kode.length > 0){
            $.ajax({
                url: "search_ajax.php",
                method: "POST",
                data: { kode_booking: kode },
                beforeSend: function(){
                    $("#hasil_pencarian").html("<div class='text-center text-muted p-3'>Mencari...</div>");
                },
                success: function(data){
                    $("#hasil_pencarian").html(data);
                }
            });
        } else {
            $("#hasil_pencarian").html("<div class='text-center text-muted p-3'>Masukkan kode booking untuk memulai pencarian.</div>");
        }
    });
});
</script>
