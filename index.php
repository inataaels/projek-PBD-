<?php
require_once 'config/layout.php';
page_header('Dashboard', 'index.php');
$c = fn($t) => q($conn, "SELECT COUNT(*) n FROM $t")[0]['n'];
$omzet = q($conn, "SELECT COALESCE(SUM(total_nilai),0) n FROM penjualan")[0]['n'];
echo '<div class="stats">
<div class="stat"><span>Barang</span><b>' . $c('barang') . '</b></div>
<div class="stat"><span>Vendor</span><b>' . $c('vendor') . '</b></div>
<div class="stat"><span>Pengadaan</span><b>' . $c('pengadaan') . '</b></div>
<div class="stat"><span>Penjualan</span><b>' . $c('penjualan') . '</b></div>
<div class="stat"><span>Total Omzet</span><b>' . rp($omzet) . '</b></div></div>';

echo '<div class="card"><h3>Stok Barang Saat Ini</h3><table class="tbl"><thead><tr><th>Barang</th><th>Harga Beli</th><th>Stok</th></tr></thead><tbody>';
foreach (barang_aktif($conn) as $b) {
    echo '<tr><td>' . e($b['nama']) . '</td><td>' . rp($b['harga']) . '</td><td><span class="badge ' . ($b['stok'] > 5 ? 'ok' : 'warn') . '">' . $b['stok'] . '</span></td></tr>';
}
echo '</tbody></table></div>';
page_footer();
