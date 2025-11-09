<?php
session_start();
include '../../database/konek.php';

 $kode_booking = $_POST['kode_booking'];

 $stmt = $konek->prepare("
    SELECT p.*, u.nama_lengkap, t.nama_paket
    FROM pemesanan p
    JOIN users u ON p.user_id = u.id
    JOIN tiket t ON p.tiket_id = t.id
    WHERE p.kode_booking LIKE ? AND t.lokasi = ?
    ORDER BY p.created_at DESC
");
 $kode_booking = "%$kode_booking%";
 $lokasi_admin = $_SESSION['lokasi'];
 $stmt->bind_param("ss", $kode_booking, $lokasi_admin);
 $stmt->execute();
 $result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo '<table class="table table-striped table-hover align-middle">
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
            <tbody>';
    
    while ($row = $result->fetch_assoc()) {
        echo '<tr>
                <td>'.htmlspecialchars($row['kode_booking']).'</td>
                <td>'.htmlspecialchars($row['nama_lengkap']).'</td>
                <td>'.htmlspecialchars($row['nama_paket']).'</td>
                <td>'.date('d/m/Y', strtotime($row['tanggal_kunjungan'])).'</td>
                <td>'.(int)$row['jumlah_tiket'].'</td>
                <td>Rp '.number_format($row['total_harga'], 0, ',', '.').'</td>
                <td><span class="badge bg-warning text-dark">Pending</span></td>
                <td>
                    <a href="?page=verifikasi_tiket&id='.urlencode($row['id']).'" 
                       class="btn btn-sm btn-primary">
                       <i class="bi bi-check-circle"></i> Verifikasi
                    </a>
                    <a href="proses/proses_pemesanan.php?action=cancel&id='.$row['id'].'"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm(\'Batalkan pesanan ini?\')">
                       <i class="bi bi-x-circle"></i> Batalkan
                    </a>
                </td>
            </tr>';
    }
    
    echo '</tbody></table>';
} else {
    echo '<div class="alert alert-info">Tidak ada pemesanan dengan kode booking tersebut.</div>';
}
?>