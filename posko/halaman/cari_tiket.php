<?php
 $search_result = null;
 $search_term = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $search_term = trim($_POST['search_term']);
    
    if (!empty($search_term)) {
        // Cari tiket berdasarkan kode booking atau nama pelanggan
        $sql = "SELECT p.*, t.nama_paket, t.lokasi, u.nama_lengkap 
                FROM pemesanan p 
                JOIN tiket t ON p.tiket_id = t.id 
                JOIN users u ON p.user_id = u.id
                WHERE (p.kode_booking LIKE ? OR u.nama_lengkap LIKE ?) AND t.lokasi = ?
                ORDER BY p.created_at DESC";
        $stmt = $konek->prepare($sql);
        $search_param = "%$search_term%";
        $stmt->bind_param('sss', $search_param, $search_param, $lokasi);
        $stmt->execute();
        $search_result = $stmt->get_result();
    }
}
?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-primary">Pencarian Tiket</h6>
    </div>
    <div class="card-body">
        <div class="input-group mb-3">
            <input type="text" id="searchInput" class="form-control" placeholder="Masukkan kode booking atau nama pelanggan" value="<?= htmlspecialchars($search_term) ?>">
            <button class="btn btn-outline-secondary" type="button" id="resetBtn">
                <i class="bi bi-x-circle"></i> Reset
            </button>
        </div>

        <!-- Hasil pencarian akan muncul di sini -->
        <div id="searchResults">
            <?php if ($search_result !== null && $search_result->num_rows > 0): ?>
                <div class="table-responsive mt-4">
                    <table class="table table-bordered">
                        <thead>
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
                            <?php while($row = $search_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['kode_booking']) ?></td>
                                <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                <td><?= htmlspecialchars($row['nama_paket']) ?></td>
                                <td><?= htmlspecialchars($row['tanggal_kunjungan']) ?></td>
                                <td><?= htmlspecialchars($row['jumlah_tiket']) ?></td>
                                <td><?= 'Rp ' . number_format($row['total_harga'], 0, ',', '.') ?></td>
                                <td>
                                    <?php 
                                    $statusClass = '';
                                    switch($row['status']) {
                                        case 'pending': $statusClass = 'bg-warning'; break;
                                        case 'dibayar': $statusClass = 'bg-success'; break;
                                        case 'selesai': $statusClass = 'bg-info'; break;
                                        case 'batal': $statusClass = 'bg-danger'; break;
                                    }
                                    ?>
                                    <span class="badge <?= $statusClass ?>"><?= ucfirst($row['status']) ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-primary btn-sm verify-btn" 
                                            data-kode="<?= htmlspecialchars($row['kode_booking']) ?>"
                                            data-pelanggan="<?= htmlspecialchars($row['nama_lengkap']) ?>"
                                            data-paket="<?= htmlspecialchars($row['nama_paket']) ?>"
                                            data-tanggal="<?= htmlspecialchars($row['tanggal_kunjungan']) ?>"
                                            data-jumlah="<?= htmlspecialchars($row['jumlah_tiket']) ?>"
                                            data-total="<?= htmlspecialchars($row['total_harga']) ?>"
                                            data-status="<?= htmlspecialchars($row['status']) ?>">
                                        <i class="bi bi-check-circle"></i> Verifikasi
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif ($search_result !== null): ?>
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-triangle"></i> Tidak ditemukan tiket dengan kata kunci "<strong><?= htmlspecialchars($search_term) ?></strong>"
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Verifikasi -->
<div class="modal fade" id="verifyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Verifikasi Tiket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="verifyForm">
                    <input type="hidden" id="modalKode" name="kode_booking">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Pelanggan:</strong> <span id="modalPelanggan"></span></p>
                            <p><strong>Paket:</strong> <span id="modalPaket"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tanggal:</strong> <span id="modalTanggal"></span></p>
                            <p><strong>Jumlah:</strong> <span id="modalJumlah"></span></p>
                            <p><strong>Total:</strong> <span id="modalTotal"></span></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Metode Pembayaran</label>
                            <select name="metode_pembayaran" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="qris">QRIS</option>
                                <option value="transfer">Transfer Bank</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Tiket</label>
                            <select name="status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="dibayar">Dibayar</option>
                                <option value="selesai">Selesai</option>
                                <option value="batal">Batal</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Catatan (Opsional)</label>
                        <textarea name="catatan" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveVerifyBtn">
                    <i class="bi bi-check-circle"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Pencarian otomatis
let searchTimer;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimer);
    const searchTerm = this.value;
    
    if (searchTerm.length >= 3) {
        searchTimer = setTimeout(function() {
            window.location.href = '?page=cari_tiket&search_term=' + encodeURIComponent(searchTerm);
        }, 500);
    } else if (searchTerm.length === 0) {
        window.location.href = '?page=cari_tiket';
    }
});

// Reset pencarian
document.getElementById('resetBtn').addEventListener('click', function() {
    document.getElementById('searchInput').value = '';
    window.location.href = '?page=cari_tiket';
});

// Modal verifikasi
const verifyModal = new bootstrap.Modal(document.getElementById('verifyModal'));

document.querySelectorAll('.verify-btn').forEach(button => {
    button.addEventListener('click', function() {
        // Isi modal dengan data tiket
        document.getElementById('modalKode').value = this.dataset.kode;
        document.getElementById('modalPelanggan').textContent = this.dataset.pelanggan;
        document.getElementById('modalPaket').textContent = this.dataset.paket;
        document.getElementById('modalTanggal').textContent = this.dataset.tanggal;
        document.getElementById('modalJumlah').textContent = this.dataset.jumlah;
        document.getElementById('modalTotal').textContent = 'Rp ' + parseFloat(this.dataset.total).toLocaleString('id-ID');
        
        // Set status saat ini
        const statusSelect = document.querySelector('select[name="status"]');
        statusSelect.value = this.dataset.status;
        
        verifyModal.show();
    });
});

// Simpan verifikasi
document.getElementById('saveVerifyBtn').addEventListener('click', function() {
    const form = document.getElementById('verifyForm');
    const formData = new FormData(form);
    
    fetch('?page=verifikasi_tiket', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Tiket berhasil diverifikasi!');
            window.location.reload();
        } else {
            alert('Terjadi kesalahan: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan sistem');
    });
});
</script>