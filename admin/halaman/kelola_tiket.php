<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Cek login admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Akses ditolak! Anda harus login sebagai admin.";
    header('location: ../login/login.php');
    exit;
}

include '../../database/konek.php';
include '../../includes/boot.php';
include '../../includes/alerts.php';
include '../../includes/stok_otomatis.php';

// Cek jika ada parameter edit
 $editing = isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']);
 $tiket = null;

if ($editing) {
    $query_tiket = $konek->prepare("SELECT * FROM tiket WHERE id = ?");
    $query_tiket->bind_param("i", $_GET['id']);
    $query_tiket->execute();
    $tiket = $query_tiket->get_result()->fetch_assoc();
}

// Ambil data kategori untuk dropdown
 $query_kategori = $konek->query("SELECT * FROM kategori ORDER BY nama ASC");

// Ambil data lokasi untuk dropdown
 $query_lokasi = $konek->query("SELECT id, nama_lokasi FROM lokasi ORDER BY nama_lokasi ASC");

// Ambil data tipe trip untuk dropdown
 $query_tipe = $konek->query("SELECT * FROM tipe_trip ORDER BY nama ASC");
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Kelola Paket Wisata</h1>
    <!-- Gunakan button dengan atribut Bootstrap untuk modal -->
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tiketModal">
        <i class="bi bi-plus-circle me-1"></i> Tambah Paket Wisata
    </button>
</div>

<!-- Tampilkan alert jika ada -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['success_message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error_message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Gambar</th>
                <th>Nama Paket</th>
                <th>Kategori</th>
                <th>Lokasi</th>
                <th>Harga</th>
                <th>Stok Total</th>
                <th>Stok Tersisa (Hari Ini)</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $query_tiket = $konek->query("SELECT t.*, k.nama as nama_kategori FROM tiket t LEFT JOIN kategori k ON t.kategori_id = k.id ORDER BY t.created_at DESC"); 
            if ($query_tiket->num_rows > 0) { 
                while($data = $query_tiket->fetch_assoc()) { 
                    // 🔹 Ambil stok otomatis dari helper
                    $stok_tersisa = getStokTersisa($konek, $data['id']);
                    echo "<tr>
                        <td><img src='../../assets/images/tiket/".htmlspecialchars($data['gambar'])."' width='60' class='rounded'></td>
                        <td>".htmlspecialchars($data['nama_paket'])."</td>
                        <td>".htmlspecialchars($data['nama_kategori'] ?? 'Tidak ada kategori')."</td>
                        <td>".htmlspecialchars($data['lokasi'])."</td>
                        <td>Rp " . number_format($data['harga'], 0, ',', '.') . "</td>
                        <td>" . htmlspecialchars($data['stok']) . "</td>
                        <td>" . htmlspecialchars($stok_tersisa) . "</td>
                        <td><span class='badge bg-" . (($data['status']=='aktif') ? 'success' : 'danger') . "'>" . ucfirst(htmlspecialchars($data['status'])) . "</span></td>
                        <td>
                            <button type='button' class='btn btn-sm btn-warning edit-tiket' data-id='" . htmlspecialchars($data['id']) . "'>
                                <i class='bi bi-pencil-square'></i> Edit
                            </button> 
                            <a href='proses/hapus_tiket.php?id=" . htmlspecialchars($data['id']) . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin ingin hapus?\")'>
                                <i class='bi bi-trash'></i> Hapus
                            </a>
                        </td>
                    </tr>"; 
                } 
            } else { 
                echo "<tr><td colspan='9' class='text-center'>Tidak ada data.</td></tr>"; 
            } 
            ?>
        </tbody>
    </table>
</div>

<!-- Modal untuk Tambah/Edit Tiket -->
<div class="modal fade" id="tiketModal" tabindex="-1" aria-labelledby="tiketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tiketModalLabel">Tambah Paket Wisata</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="tiketForm" method="POST" action="proses/handle_tiket.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="tiketId">
                    
                    <!-- Informasi Dasar -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Informasi Dasar</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="nama_paket" class="form-label">Nama Paket</label>
                                        <input type="text" id="nama_paket" name="nama_paket" class="form-control" 
                                               placeholder="Masukkan nama paket wisata" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="kategori_id" class="form-label">Kategori</label>
                                        <select id="kategori_id" name="kategori_id" class="form-select" required>
                                            <option value="">-- Pilih Kategori --</option>
                                            <?php 
                                            // Reset pointer kategori
                                            if ($query_kategori->num_rows > 0) {
                                                $query_kategori->data_seek(0);
                                                while($kat = $query_kategori->fetch_assoc()): 
                                            ?>
                                                <option value="<?= $kat['id'] ?>">
                                                    <?= htmlspecialchars($kat['nama']) ?>
                                                </option>
                                            <?php 
                                                endwhile; 
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="lokasi" class="form-label">Lokasi</label>
                                        <select id="lokasi" name="lokasi" class="form-select" required>
                                            <option value="">-- Pilih Lokasi --</option>
                                            <?php 
                                            // Reset pointer lokasi
                                            if ($query_lokasi->num_rows > 0) {
                                                $query_lokasi->data_seek(0);
                                                while($lok = $query_lokasi->fetch_assoc()): 
                                            ?>
                                                <option value="<?= $lok['nama_lokasi'] ?>">
                                                    <?= htmlspecialchars($lok['nama_lokasi']) ?>
                                                </option>
                                            <?php 
                                                endwhile; 
                                            } else {
                                                // Jika tabel lokasi kosong, gunakan nilai default
                                            ?>
                                                <option value="Labuan Bajo">Labuan Bajo</option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="tipe_trip" class="form-label">Tipe Trip</label>
                                        <select id="tipe_trip" name="tipe_trip" class="form-select">
                                            <option value="">-- Pilih Tipe Trip --</option>
                                            <?php 
                                            if ($query_tipe->num_rows > 0) {
                                                $query_tipe->data_seek(0);
                                                while($tipe = $query_tipe->fetch_assoc()): 
                                            ?>
                                                <option value="<?= $tipe['id'] ?>">
                                                    <?= htmlspecialchars($tipe['nama']) ?>
                                                </option>
                                            <?php 
                                                endwhile; 
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="durasi" class="form-label">Durasi</label>
                                        <input type="text" id="durasi" name="durasi" class="form-control" 
                                               placeholder="Contoh: 3 Hari 2 Malam" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="deskripsi" class="form-label">Deskripsi</label>
                                        <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3" 
                                                  placeholder="Jelaskan deskripsi paket wisata" required></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select id="status" name="status" class="form-select">
                                            <option value="aktif">Aktif</option>
                                            <option value="nonaktif">Nonaktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="harga" class="form-label">Harga</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" id="harga" name="harga" class="form-control" 
                                                   placeholder="0" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="stok" class="form-label">Stok Harian</label>
                                        <input type="number" id="stok" name="stok" class="form-control" 
                                               placeholder="0" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="stok_default" class="form-label">Stok Default</label>
                                        <input type="number" id="stok_default" name="stok_default" class="form-control" 
                                               placeholder="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="kapasitas" class="form-label">Kapasitas</label>
                                        <input type="number" id="kapasitas" name="kapasitas" class="form-control" 
                                               placeholder="0">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="jadwal" class="form-label">Jadwal Keberangkatan</label>
                                        <input type="text" id="jadwal" name="jadwal" class="form-control" 
                                               placeholder="Contoh: Setiap Sabtu, Minggu">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="gambar" class="form-label">Gambar</label>
                                        <input type="file" id="gambar" name="gambar" class="form-control" accept="image/*">
                                        <div id="imagePreview" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Detail -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Informasi Detail</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="fasilitas" class="form-label">Fasilitas</label>
                                <textarea id="fasilitas" name="fasilitas" class="form-control" rows="3" 
                                          placeholder="Pisahkan setiap fasilitas dengan koma (,)"></textarea>
                                <div class="form-text">Pisahkan setiap fasilitas dengan koma (,)</div>
                            </div>

                            <div class="mb-3">
                                <label for="itinerary" class="form-label">Itinerary</label>
                                <textarea id="itinerary" name="itinerary" class="form-control" rows="5" 
                                          placeholder="Jelaskan rencana perjalanan hari per hari"></textarea>
                                <div class="form-text">Jelaskan rencana perjalanan hari per hari</div>
                            </div>

                            <div class="mb-3">
                                <label for="syarat" class="form-label">Syarat & Ketentuan</label>
                                <textarea id="syarat" name="syarat" class="form-control" rows="3" 
                                          placeholder="Jelaskan syarat dan ketentuan yang berlaku"></textarea>
                                <div class="form-text">Jelaskan syarat dan ketentuan yang berlaku</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Event listener untuk tombol edit
    document.querySelectorAll('.edit-tiket').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            loadTiketData(id);
        });
    });
    
    // Cek jika ada parameter edit di URL
    const urlParams = new URLSearchParams(window.location.search);
    const action = urlParams.get('action');
    const id = urlParams.get('id');
    
    if (action === 'edit' && id) {
        loadTiketData(id);
    }
    
    function loadTiketData(id) {
        // Fetch data tiket dari server
        fetch(`proses/get_tiket.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                
                // Reset form
                document.getElementById('tiketForm').reset();
                
                // Isi form dengan data tiket
                document.getElementById('tiketId').value = data.id;
                document.getElementById('nama_paket').value = data.nama_paket;
                document.getElementById('kategori_id').value = data.kategori_id;
                document.getElementById('lokasi').value = data.lokasi;
                document.getElementById('tipe_trip').value = data.tipe_trip_id || '';
                document.getElementById('durasi').value = data.durasi;
                document.getElementById('deskripsi').value = data.deskripsi;
                document.getElementById('status').value = data.status;
                document.getElementById('harga').value = data.harga;
                document.getElementById('stok').value = data.stok;
                document.getElementById('stok_default').value = data.stok_default || data.stok;
                document.getElementById('kapasitas').value = data.kapasitas || '';
                document.getElementById('jadwal').value = data.jadwal || '';
                document.getElementById('fasilitas').value = data.fasilitas || '';
                document.getElementById('itinerary').value = data.itinerary || '';
                document.getElementById('syarat').value = data.syarat || '';
                
                // Tampilkan gambar jika ada
                if (data.gambar) {
                    document.getElementById('imagePreview').innerHTML = `
                        <p class="form-text">Gambar saat ini:</p>
                        <img src="../../assets/images/tiket/${data.gambar}" width="100" alt="Current image" class="img-thumbnail">
                    `;
                } else {
                    document.getElementById('imagePreview').innerHTML = '';
                }
                
                // Ubah judul modal
                document.querySelector('#tiketModalLabel').textContent = 'Edit Tiket';
                
                // Tampilkan modal
                const modal = new bootstrap.Modal(document.getElementById('tiketModal'));
                modal.show();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memuat data tiket');
            });
    }
    
    // Reset form saat modal ditutup
    document.getElementById('tiketModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('tiketForm').reset();
        document.getElementById('tiketId').value = '';
        document.getElementById('imagePreview').innerHTML = '';
        document.querySelector('#tiketModalLabel').textContent = 'Tambah Tiket';
    });
    
    // Event listener untuk tombol tambah
    document.querySelector('[data-bs-target="#tiketModal"]').addEventListener('click', function() {
        document.getElementById('gambar').setAttribute('required', 'required');
    });
    
    // Preview gambar sebelum upload
    document.getElementById('gambar').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('imagePreview');
                preview.innerHTML = `
                    <p class="form-text">Preview gambar:</p>
                    <img src="${e.target.result}" width="100" alt="Preview" class="img-thumbnail">
                `;
            }
            reader.readAsDataURL(file);
        }
    });
    
    // Validasi form sebelum submit
    document.getElementById('tiketForm').addEventListener('submit', function(e) {
        const namaPaket = document.getElementById('nama_paket').value.trim();
        const kategori = document.getElementById('kategori_id').value;
        const lokasi = document.getElementById('lokasi').value;
        const durasi = document.getElementById('durasi').value.trim();
        const deskripsi = document.getElementById('deskripsi').value.trim();
        const harga = document.getElementById('harga').value;
        const stok = document.getElementById('stok').value;
        const id = document.getElementById('tiketId').value;
        
        if (!namaPaket || !kategori || !lokasi || !durasi || !deskripsi || !harga || !stok) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang wajib diisi!');
            return false;
        }
        
        // Jika edit, gambar tidak wajib
        if (!id && !document.getElementById('gambar').files[0]) {
            e.preventDefault();
            alert('Gambar wajib diupload untuk tiket baru!');
            return false;
        }
        
        return true;
    });
});
</script>