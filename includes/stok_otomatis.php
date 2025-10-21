<?php
if (!function_exists('getStokTersisa')) {
    function getStokTersisa($konek, $tiket_id) {
        $tanggal_hari_ini = date('Y-m-d');
        $q = $konek->prepare("
            SELECT t.stok - IFNULL(SUM(p.jumlah_tiket), 0) AS stok_tersisa
            FROM tiket t
            LEFT JOIN pemesanan p ON t.id = p.tiket_id
                AND p.tanggal_kunjungan = ?
                AND p.status IN ('pending', 'dibayar', 'selesai')
            WHERE t.id = ?
            GROUP BY t.id
        ");
        $q->bind_param("si", $tanggal_hari_ini, $tiket_id);
        $q->execute();
        $res = $q->get_result()->fetch_assoc();
        $q->close();

        return $res ? intval($res['stok_tersisa']) : 0;
    }
}
?>
