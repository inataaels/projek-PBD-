<?php
require_once 'config/layout.php';
$id = (int)($_GET['id'] ?? 0);
$h = q($conn, "SELECT p.*, u.username, m.persen FROM penjualan p JOIN user u ON p.iduser=u.iduser
    JOIN margin_penjualan m ON p.idmargin_penjualan=m.idmargin_penjualan WHERE p.idpenjualan=?", [$id]);
if (!$h) { flash('Data tidak ditemukan.', 'err'); redirect('penjualan.php'); }
$h = $h[0];
$d = q($conn, "SELECT d.*, b.nama FROM detail_penjualan d JOIN barang b ON d.idbarang=b.idbarang WHERE d.penjualan_idpenjualan=?", [$id]);

page_header("Detail Penjualan #$id", 'penjualan.php');
echo '<div class="card"><p>Tanggal: <b>' . e($h['created_at']) . '</b> | Kasir: <b>' . e($h['username']) . '</b> | Margin: <b>' . $h['persen'] . ' %</b></p>
<table class="tbl"><thead><tr><th>No</th><th>Barang</th><th>Harga Satuan</th><th>Jumlah</th><th>Subtotal</th></tr></thead><tbody>';
foreach ($d as $i => $r) {
    echo '<tr><td>' . ($i + 1) . '</td><td>' . e($r['nama']) . '</td><td>' . rp($r['harga_satuan']) . '</td><td>' . $r['jumlah'] . '</td><td>' . rp($r['subtotal']) . '</td></tr>';
}
echo '</tbody></table><div class="totals">Subtotal: <b>' . rp($h['subtotal_nilai']) . '</b> | PPN: <b>' . rp($h['ppn']) . '</b> | Total: <b>' . rp($h['total_nilai']) . '</b></div>
<p><a class="btn gray" href="penjualan.php">&laquo; Kembali</a></p></div>';
page_footer();
