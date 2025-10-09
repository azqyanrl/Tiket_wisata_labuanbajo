<?php 
include '../../database/konek.php';
include '../boot.php';

// Tampilkan pesan sukses jika ada
if (isset($_SESSION['success_message'])) { 
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">'.$_SESSION['success_message'].'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; 
    unset($_SESSION['success_message']); 
} 
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Kelola Pemesanan</h1>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Kode Booking</th>
                <th>User</th>
                <th>Tiket</th>
                <th>Tanggal Kunjungan</th>
                <th>Total</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $query_pemesanan = $konek->query("SELECT p.*, u.nama_lengkap, t.nama_paket FROM pemesanan p JOIN users u ON p.user_id = u.id JOIN tiket t ON p.tiket_id = t.id ORDER BY p.created_at DESC"); 
            if ($query_pemesanan->num_rows > 0) { 
                while($data = $query_pemesanan->fetch_assoc()) { 
                    $statusClass = ($data['status']=='pending')?'bg-warning text-dark':(($data['status']=='dibayar')?'bg-info':(($data['status']=='selesai')?'bg-success':'bg-danger')); 
                    echo "<tr>
                        <td>{$data['kode_booking']}</td>
                        <td>{$data['nama_lengkap']}</td>
                        <td>{$data['nama_paket']}</td>
                        <td>" . date('d/m/Y', strtotime($data['tanggal_kunjungan'])) . "</td>
                        <td>Rp " . number_format($data['total_harga'], 0, ',', '.') . "</td>
                        <td><span class='badge $statusClass'>" . ucfirst($data['status']) . "</span></td>
                        <td>";
                        if ($data['status'] == 'pending') {
                            echo "<a href='proses/proses_pemesanan.php?id={$data['id']}&action=confirm' class='btn btn-sm btn-success'>Konfirmasi</a> ";
                            echo "<a href='proses/proses_pemesanan.php?id={$data['id']}&action=reject' class='btn btn-sm btn-danger'>Tolak</a>";
                        } else {
                            echo "<span class='text-muted'>-</span>";
                        }
                    echo "</td></tr>"; 
                } 
            } else { 
                echo "<tr><td colspan='7' class='text-center'>Tidak ada data.</td></tr>"; 
            } 
            ?>
        </tbody>
    </table>
</div>