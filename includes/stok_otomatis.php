<?php
if (!function_exists('getStokTersisa')) {
    function getStokTersisa($konek, $tiket_id, $tanggal = null) {
        // Jika tanggal tidak dikirim, pakai tanggal hari ini
        if ($tanggal === null) {
            $tanggal = date('Y-m-d');
        }

        // Pastikan koneksi valid
        if (!$konek) {
            throw new Exception("Koneksi database tidak ditemukan.");
        }

        // Ambil stok harian (sudah di-reset otomatis jam 00:00)
        $q = $konek->prepare("SELECT stok FROM tiket WHERE id = ?");
        $q->bind_param("i", $tiket_id);
        $q->execute();
        $res = $q->get_result();
        $row = $res->fetch_assoc();
        $q->close();

        if (!$row) {
            throw new Exception("Data tiket tidak ditemukan.");
        }

        $stok_harian = (int)$row['stok'];

        // Hitung total tiket yang sudah dipesan untuk tanggal tersebut
        $p = $konek->prepare("
            SELECT IFNULL(SUM(jumlah_tiket), 0) AS total_terpesan
            FROM pemesanan
            WHERE tiket_id = ? AND tanggal_kunjungan = ? AND status != 'batal'
        ");
        $p->bind_param("is", $tiket_id, $tanggal);
        $p->execute();
        $result = $p->get_result()->fetch_assoc();
        $p->close();

        $terpesan = (int)$result['total_terpesan'];
        $stok_tersisa = max($stok_harian - $terpesan, 0);

        return $stok_tersisa;
    }
}
?>