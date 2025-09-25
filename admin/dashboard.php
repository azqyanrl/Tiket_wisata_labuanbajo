<?php
session_start();
include "boot.php";

if (!isset($_SESSION['username'])) {
    echo "<script>document.location.href='login/login.php';</script>";
    exit;
}
?>

<?php
require_once '../database/konek.php';
// Statistik dasar
$stmt = $pdo->query("SELECT COUNT(*) as total FROM tickets");
$total_tickets = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM transactions WHERE status = 'paid'");
$total_transactions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT SUM(total_harga) as total FROM transactions WHERE status = 'paid'");
$total_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

if ($total_revenue === null) {
    $total_revenue = 0;
}

// Transaksi terbaru
$stmt = $pdo->query("SELECT t.*, u.nama_lengkap, tk.nama_paket 
                     FROM transactions t 
                     JOIN users u ON t.user_id = u.id 
                     JOIN tickets tk ON t.ticket_id = tk.id 
                     ORDER BY t.tanggal_pesan DESC LIMIT 5");
$recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Tiket Labuan Bajo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body style="margin:0; padding:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color:#f8f9fa;">
    <div style="display:flex; min-height:100vh;">
        <!-- Sidebar -->
        <div style="width:250px; background:#343a40; color:white;">
            <div style="padding:20px; text-align:center; border-bottom:1px solid #495057;">
                <i style="font-size:36px; color:#007bff;" class="bi bi-speedometer2"></i>
                <h3 style="margin:10px 0;">Admin Panel</h3>
            </div>
            <div style="padding:20px 0;">
                <a href="index.php" style="display:block; padding:12px 20px; color:white; text-decoration:none; background:#007bff;">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="tickets/index.php" style="display:block; padding:12px 20px; color:white; text-decoration:none;">
                    <i class="bi bi-ticket-perforated"></i> Tiket
                </a>
                <a href="transactions/index.php" style="display:block; padding:12px 20px; color:white; text-decoration:none;">
                    <i class="bi bi-cart3"></i> Transaksi
                </a>
                <a href="users/index.php" style="display:block; padding:12px 20px; color:white; text-decoration:none;">
                    <i class="bi bi-people"></i> Users
                </a>
                <a href="gallery/index.php" style="display:block; padding:12px 20px; color:white; text-decoration:none;">
                    <i class="bi bi-images"></i> Galeri
                </a>
                <a href="reports/index.php" style="display:block; padding:12px 20px; color:white; text-decoration:none;">
                    <i class="bi bi-file-earmark-bar-graph"></i> Laporan
                </a>
                <a href="settings/index.php" style="display:block; padding:12px 20px; color:white; text-decoration:none;">
                    <i class="bi bi-gear"></i> Pengaturan
                </a>
                <a href="logout.php" style="display:block; padding:12px 20px; color:#dc3545; text-decoration:none;">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div style="flex:1; padding:20px;">
            <div style="background:white; border-radius:10px; box-shadow:0 4px 15px rgba(0,0,0,0.1); padding:20px; margin-bottom:30px;">
                <h1 style="margin:0 0 20px; color:#2c3e50;">Dashboard Admin</h1>
                
                <!-- Statistik Cards -->
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:20px; margin-bottom:30px;">
                    <div style="background:#e3f2fd; border-left:4px solid #2196f3; padding:20px; border-radius:5px;">
                        <h3 style="margin:0 0 10px; color:#2196f3;"><?php echo $total_tickets; ?></h3>
                        <p style="margin:0; color:#757575;">Total Tiket</p>
                    </div>
                    <div style="background:#e8f5e9; border-left:4px solid #4caf50; padding:20px; border-radius:5px;">
                        <h3 style="margin:0 0 10px; color:#4caf50;"><?php echo $total_users; ?></h3>
                        <p style="margin:0; color:#757575;">Total User</p>
                    </div>
                    <div style="background:#fff3e0; border-left:4px solid #ff9800; padding:20px; border-radius:5px;">
                        <h3 style="margin:0 0 10px; color:#ff9800;"><?php echo $total_transactions; ?></h3>
                        <p style="margin:0; color:#757575;">Transaksi</p>
                    </div>
                    <div style="background:#fce4ec; border-left:4px solid #e91e63; padding:20px; border-radius:5px;">
                        <h3 style="margin:0 0 10px; color:#e91e63;">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></h3>
                        <p style="margin:0; color:#757575;">Pendapatan</p>
                    </div>
                </div>
                
                <!-- Recent Transactions -->
                <h2 style="margin:0 0 20px; color:#2c3e50;">Transaksi Terbaru</h2>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f8f9fa;">
                                <th style="padding:12px; text-align:left; border-bottom:1px solid #dee2e6;">ID</th>
                                <th style="padding:12px; text-align:left; border-bottom:1px solid #dee2e6;">User</th>
                                <th style="padding:12px; text-align:left; border-bottom:1px solid #dee2e6;">Tiket</th>
                                <th style="padding:12px; text-align:left; border-bottom:1px solid #dee2e6;">Total</th>
                                <th style="padding:12px; text-align:left; border-bottom:1px solid #dee2e6;">Status</th>
                                <th style="padding:12px; text-align:left; border-bottom:1px solid #dee2e6;">Tanggal</th>
                                <th style="padding:12px; text-align:left; border-bottom:1px solid #dee2e6;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_transactions as $transaction): ?>
                                <tr>
                                    <td style="padding:12px; border-bottom:1px solid #dee2e6;"><?php echo $transaction['id']; ?></td>
                                    <td style="padding:12px; border-bottom:1px solid #dee2e6;"><?php echo $transaction['nama_lengkap']; ?></td>
                                    <td style="padding:12px; border-bottom:1px solid #dee2e6;"><?php echo $transaction['nama_paket']; ?></td>
                                    <td style="padding:12px; border-bottom:1px solid #dee2e6;">Rp <?php echo number_format($transaction['total_harga'], 0, ',', '.'); ?></td>
                                    <td style="padding:12px; border-bottom:1px solid #dee2e6;">
                                        <?php if ($transaction['status'] == 'pending'): ?>
                                            <span style="background:#ffc107; color:#212529; padding:4px 8px; border-radius:4px; font-size:14px;">Pending</span>
                                        <?php elseif ($transaction['status'] == 'paid'): ?>
                                            <span style="background:#28a745; color:white; padding:4px 8px; border-radius:4px; font-size:14px;">Paid</span>
                                        <?php else: ?>
                                            <span style="background:#dc3545; color:white; padding:4px 8px; border-radius:4px; font-size:14px;">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:12px; border-bottom:1px solid #dee2e6;"><?php echo date('d/m/Y', strtotime($transaction['tanggal_pesan'])); ?></td>
                                    <td style="padding:12px; border-bottom:1px solid #dee2e6;">
                                        <a href="transactions/detail.php?id=<?php echo $transaction['id']; ?>" style="background:#007bff; color:white; padding:4px 8px; border-radius:4px; text-decoration:none; font-size:14px;">Detail</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>