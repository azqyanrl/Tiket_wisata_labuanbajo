<?php 
include '../../database/konek.php';
include '../boot.php';

// Tampilkan pesan sukses jika ada
if (isset($_SESSION['success_message'])) { 
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">'.$_SESSION['success_message'].'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; 
    unset($_SESSION['success_message']); 
} 

// Tampilkan pesan error jika ada
if (isset($_SESSION['error_message'])) { 
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">'.$_SESSION['error_message'].'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; 
    unset($_SESSION['error_message']); 
} 
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Kelola Pemesanan</h1>
</div>

<!-- Tambahkan form pencarian -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <!-- PERBAIKAN: action form diisi dengan URL saat ini dan ada input tersembunyi untuk 'page' -->
                <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <input type="hidden" name="page" value="kelola_pemesanan">
                    
                    <div class="row">
                        <div class="col-md-3">
                            <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                            <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="<?php echo isset($_GET['tanggal_awal']) ? htmlspecialchars($_GET['tanggal_awal']) : ''; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="<?php echo isset($_GET['tanggal_akhir']) ? htmlspecialchars($_GET['tanggal_akhir']) : ''; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="status_filter" class="form-label">Status</label>
                            <select class="form-select" id="status_filter" name="status_filter">
                                <option value="">Semua Status</option>
                                <option value="pending" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="dibayar" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'dibayar') ? 'selected' : ''; ?>>Dibayar</option>
                                <option value="selesai" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                                <option value="batal" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'batal') ? 'selected' : ''; ?>>Batal</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Cari</button>
                            <!-- PERBAIKAN: Link reset juga menuju ke URL yang benar -->
                            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=kelola_pemesanan" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
            // --- PERBAIKAN: Query Dinamis untuk Pencarian ---
            // Query dasar
            $sql = "SELECT p.*, u.nama_lengkap, t.nama_paket FROM pemesanan p JOIN users u ON p.user_id = u.id JOIN tiket t ON p.tiket_id = t.id";
            
            // Array untuk menyimpan kondisi WHERE
            $conditions = [];
            $params = [];
            $types = '';
            
            // Tambahkan kondisi filter tanggal
            if (isset($_GET['tanggal_awal']) && !empty($_GET['tanggal_awal'])) {
                $conditions[] = "p.tanggal_kunjungan >= ?";
                $params[] = $_GET['tanggal_awal'];
                $types .= 's';
            }
            
            if (isset($_GET['tanggal_akhir']) && !empty($_GET['tanggal_akhir'])) {
                $conditions[] = "p.tanggal_kunjungan <= ?";
                $params[] = $_GET['tanggal_akhir'];
                $types .= 's';
            }
            
            // Tambahkan kondisi filter status
            if (isset($_GET['status_filter']) && !empty($_GET['status_filter'])) {
                $conditions[] = "p.status = ?";
                $params[] = $_GET['status_filter'];
                $types .= 's';
            }
            
            // Gabungkan kondisi WHERE jika ada
            if (count($conditions) > 0) {
                $sql .= " WHERE " . implode(' AND ', $conditions);
            }
            
            // Tambahkan ORDER BY
            $sql .= " ORDER BY p.created_at DESC";
            
            // Persiapkan dan eksekusi query dengan prepared statement untuk keamanan
            $stmt = $konek->prepare($sql);
            
            if ($stmt) {
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                // Jika prepare gagal, tampilkan error (untuk debugging)
                $result = false;
                // echo "<tr><td colspan='7' class='text-center text-danger'>Error pada query database.</td></tr>";
            }

            if ($result && $result->num_rows > 0) { 
                while($data = $result->fetch_assoc()) { 
                    $statusClass = ($data['status']=='pending')?'bg-warning text-dark':(($data['status']=='dibayar')?'bg-info':(($data['status']=='selesai')?'bg-success':'bg-danger')); 
                    echo "<tr>
                        <td>{$data['kode_booking']}</td>
                        <td>{$data['nama_lengkap']}</td>
                        <td>{$data['nama_paket']}</td>
                        <td>" . date('d/m/Y', strtotime($data['tanggal_kunjungan'])) . "</td>
                        <td>Rp " . number_format($data['total_harga'], 0, ',', '.') . "</td>
                        <td><span class='badge $statusClass'>" . ucfirst($data['status']) . "</span></td>
                        <td>";
                        
                        // --- PERBAIKAN: Tampilkan tombol berdasarkan status ---
                        if ($data['status'] == 'pending') {
                            echo "<a href='proses/proses_pemesanan.php?id={$data['id']}&action=confirm' class='btn btn-sm btn-success'>Konfirmasi</a> ";
                            echo "<a href='proses/proses_pemesanan.php?id={$data['id']}&action=reject' class='btn btn-sm btn-danger'>Tolak</a>";
                        } elseif ($data['status'] == 'dibayar') {
                            echo "<a href='proses/proses_pemesanan.php?id={$data['id']}&action=complete' class='btn btn-sm btn-success'>Selesaikan</a> ";
                            echo "<a href='proses/proses_pemesanan.php?id={$data['id']}&action=cancel' class='btn btn-sm btn-danger' onclick='return confirm(\"Apakah Anda yakin ingin membatalkan pesanan ini?\")'>Batalkan</a>";
                        } elseif ($data['status'] == 'selesai') {
                            echo "<span class='text-muted'>Selesai</span>";
                        } else { // status 'batal'
                            echo "<span class='text-muted'>Dibatalkan</span>";
                        }
                    echo "</td></tr>"; 
                } 
            } else { 
                echo "<tr><td colspan='7' class='text-center'>Tidak ada data yang cocok dengan kriteria pencarian.</td></tr>"; 
            } 
            ?>
        </tbody>
    </table>
</div>