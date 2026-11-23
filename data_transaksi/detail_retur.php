<?php
require_once 'config/layout.php';
$id = (int)($_GET['id'] ?? 0);
$h = q($conn, "SELECT r.*, u.username FROM retur r JOIN user u ON r.iduser=u.iduser WHERE r.idretur=?", [$id]);
if (!$h) { flash('Data tidak ditemukan.', 'err'); redirect('retur.php'); }
$h = $h[0];
$d = q($conn, "SELECT dr.*, b.nama FROM detail_retur dr
    JOIN detail_penerimaan dp ON dr.iddetail_penerimaan=dp.iddetail_penerimaan
    JOIN barang b ON dp.barang_idbarang=b.idbarang WHERE dr.idretur=?", [$id]);

page_header("Detail Retur #$id", 'retur.php');
echo '<div class="card"><p>Tanggal: <b>' . e($h['created_at']) . '</b> | Penerimaan: <b>#' . $h['idpenerimaan'] . '</b> | User: <b>' . e($h['username']) . '</b></p>
<table class="tbl"><thead><tr><th>No</th><th>Barang</th><th>Jumlah</th><th>Alasan</th></tr></thead><tbody>';
foreach ($d as $i => $r) {
    echo '<tr><td>' . ($i + 1) . '</td><td>' . e($r['nama']) . '</td><td>' . $r['jumlah'] . '</td><td>' . e($r['alasan']) . '</td></tr>';
}
echo '</tbody></table><p><a class="btn gray" href="retur.php">&laquo; Kembali</a></p></div>';
page_footer();
