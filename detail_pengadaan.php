<?php
require_once 'config/layout.php';
$id = (int)($_GET['id'] ?? 0);
$h = q($conn, "SELECT p.*, p.`timestamp` AS tgl, v.nama_vendor, u.username FROM pengadaan p
    JOIN vendor v ON p.vendor_idvendor=v.idvendor JOIN user u ON p.user_iduser=u.iduser WHERE p.idpengadaan=?", [$id]);
if (!$h) { flash('Data tidak ditemukan.', 'err'); redirect('pengadaan.php'); }
$h = $h[0];
$d = q($conn, "SELECT d.*, b.nama FROM detail_pengadaan d JOIN barang b ON d.idbarang=b.idbarang WHERE d.idpengadaan=?", [$id]);

page_header("Detail Pengadaan #$id", 'pengadaan.php');
echo '<div class="card"><p>Tanggal: <b>' . e($h['tgl']) . '</b> | Vendor: <b>' . e($h['nama_vendor']) . '</b> | User: <b>' . e($h['username']) . '</b> | Status: <b>' . ($h['status'] === 'S' ? 'Selesai' : 'Diproses') . '</b></p>
<table class="tbl"><thead><tr><th>No</th><th>Barang</th><th>Harga Satuan</th><th>Jumlah</th><th>Subtotal</th></tr></thead><tbody>';
foreach ($d as $i => $r) {
    echo '<tr><td>' . ($i + 1) . '</td><td>' . e($r['nama']) . '</td><td>' . rp($r['harga_satuan']) . '</td><td>' . $r['jumlah'] . '</td><td>' . rp($r['sub_total']) . '</td></tr>';
}
echo '</tbody></table><div class="totals">Subtotal: <b>' . rp($h['subtotal_nilai']) . '</b> | PPN: <b>' . rp($h['ppn']) . '</b> | Total: <b>' . rp($h['total_nilai']) . '</b></div>
<p><a class="btn gray" href="pengadaan.php">&laquo; Kembali</a></p></div>';
page_footer();
