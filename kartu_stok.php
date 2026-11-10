<?php
require_once 'config/layout.php';
page_header('Kartu Stok', 'kartu_stok.php');
$pilih = (int)($_GET['idbarang'] ?? 0);
$jenis = ['T' => 'Penerimaan', 'J' => 'Penjualan', 'R' => 'Retur'];

echo '<div class="card"><form method="get" class="form"><label>Filter Barang' .
     select_html('idbarang', q($conn, "SELECT idbarang, nama FROM barang ORDER BY nama"), 'idbarang', 'nama', $pilih, 'onchange="this.form.submit()"') .
     '</label></form></div>';

$sql = "SELECT k.*, b.nama FROM kartu_stok k JOIN barang b ON k.idbarang=b.idbarang" . ($pilih ? " WHERE k.idbarang=?" : "") . " ORDER BY k.idkartu_stok DESC LIMIT 300";
$rows = q($conn, $sql, $pilih ? [$pilih] : []);
echo '<div class="card"><table class="tbl"><thead><tr><th>ID</th><th>Tanggal</th><th>Barang</th><th>Transaksi</th><th>Ref</th><th>Masuk</th><th>Keluar</th><th>Stok</th></tr></thead><tbody>';
foreach ($rows as $r) {
    echo '<tr><td>' . $r['idkartu_stok'] . '</td><td>' . e($r['created_at']) . '</td><td>' . e($r['nama']) . '</td><td>' . ($jenis[$r['jenis_transaksi']] ?? $r['jenis_transaksi']) . '</td>
    <td>#' . $r['idtransaksi'] . '</td><td>' . (int)$r['masuk'] . '</td><td>' . (int)$r['keluar'] . '</td><td><b>' . $r['stock'] . '</b></td></tr>';
}
if (!$rows) echo '<tr><td colspan="8" class="empty">Belum ada mutasi stok.</td></tr>';
echo '</tbody></table></div>';
page_footer();
