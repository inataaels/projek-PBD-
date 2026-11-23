<?php
require_once 'config/layout.php';
$id = (int)($_GET['id'] ?? 0);
$h = q($conn, "SELECT pn.*, u.username FROM penerimaan pn JOIN user u ON pn.iduser=u.iduser WHERE pn.idpenerimaan=?", [$id]);
if (!$h) { flash('Data tidak ditemukan.', 'err'); redirect('penerimaan.php'); }
$h = $h[0];
$d = q($conn, "SELECT d.*, b.nama FROM detail_penerimaan d JOIN barang b ON d.barang_idbarang=b.idbarang WHERE d.idpenerimaan=?", [$id]);

page_header("Detail Penerimaan #$id", 'penerimaan.php');
echo '<div class="card"><p>Tanggal: <b>' . e($h['created_at']) . '</b> | Pengadaan: <b>#' . $h['idpengadaan'] . '</b> | User: <b>' . e($h['username']) . '</b></p>
<table class="tbl"><thead><tr><th>No</th><th>Barang</th><th>Jumlah Terima</th><th>Harga Satuan</th><th>Subtotal</th></tr></thead><tbody>';
$tot = 0;
foreach ($d as $i => $r) {
    $tot += $r['sub_total_terima'];
    echo '<tr><td>' . ($i + 1) . '</td><td>' . e($r['nama']) . '</td><td>' . $r['jumlah_terima'] . '</td><td>' . rp($r['harga_satuan_terima']) . '</td><td>' . rp($r['sub_total_terima']) . '</td></tr>';
}
echo '</tbody></table><div class="totals">Total: <b>' . rp($tot) . '</b></div>
<p><a class="btn gray" href="penerimaan.php">&laquo; Kembali</a></p></div>';
page_footer();
