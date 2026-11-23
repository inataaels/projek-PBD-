<?php
require_once 'config/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();
        $qty = [];                                   // gabungkan barang yang sama
        foreach (($_POST['barang'] ?? []) as $i => $b) {
            $j = (int)($_POST['jumlah'][$i] ?? 0);
            if ($b && $j > 0) $qty[$b] = ($qty[$b] ?? 0) + $j;
        }
        if (!$qty) throw new Exception('Tambahkan minimal satu barang.');

        // margin dipilih kasir, hanya boleh margin yang masih aktif
        $idm = (int)($_POST['idmargin'] ?? 0);
        $m = q($conn, "SELECT idmargin_penjualan FROM margin_penjualan WHERE idmargin_penjualan=? AND status=1", [$idm]);
        if (!$m) throw new Exception('Pilih margin penjualan yang aktif.');

        $id = x($conn, "INSERT INTO penjualan (subtotal_nilai,ppn,total_nilai,iduser,idmargin_penjualan) VALUES (0,0,0,?,?)",
                [$_POST['iduser'], $m[0]['idmargin_penjualan']]);
        foreach ($qty as $b => $j) {
            // harga jual (function), cek stok, subtotal/PPN/total: SP; kartu stok: trigger
            x($conn, "CALL sp_detail_penjualan(?,?,?)", [$id, $b, $j]);
        }
        $total = q($conn, "SELECT total_nilai FROM penjualan WHERE idpenjualan=?", [$id])[0]['total_nilai'];
        $conn->commit();
        flash("Penjualan #$id berhasil. Total " . rp($total));
    } catch (Throwable $e) {
        $conn->rollback();
        flash($e->getMessage(), 'err');
    }
    redirect('penjualan.php');
}

page_header('Penjualan', 'penjualan.php');
$margin = q($conn, "SELECT idmargin_penjualan, persen FROM margin_penjualan WHERE status=1 ORDER BY persen");
echo '<div class="card"><h3>Transaksi Penjualan Baru</h3><form method="post"><div class="form">
<label>Kasir' . user_field() . '</label>
<label>Margin<select id="margin" name="idmargin" required>';
foreach ($margin as $m) {
    echo '<option value="' . $m['idmargin_penjualan'] . '" data-persen="' . $m['persen'] . '">' . $m['persen'] . ' %</option>';
}
if (!$margin) echo '<option value="">Belum ada margin aktif</option>';
echo '</select></label></div>';
item_table(barang_aktif($conn), true, true);
echo '<p><button class="btn" type="submit">Simpan Penjualan</button></p></form></div>';

$list = q($conn, "SELECT p.idpenjualan, p.created_at, u.username, m.persen, p.total_nilai FROM penjualan p
    JOIN user u ON p.iduser=u.iduser JOIN margin_penjualan m ON p.idmargin_penjualan=m.idmargin_penjualan ORDER BY p.idpenjualan DESC");
echo '<div class="card"><h3>Riwayat Penjualan</h3><table class="tbl"><thead><tr><th>ID</th><th>Tanggal</th><th>Kasir</th><th>Margin</th><th>Total</th><th>Aksi</th></tr></thead><tbody>';
foreach ($list as $r) {
    echo '<tr><td>' . $r['idpenjualan'] . '</td><td>' . e($r['created_at']) . '</td><td>' . e($r['username']) . '</td><td>' . $r['persen'] . ' %</td><td>' . rp($r['total_nilai']) . '</td>
    <td><a class="btn small" href="detail_penjualan.php?id=' . $r['idpenjualan'] . '">Detail</a></td></tr>';
}
if (!$list) echo '<tr><td colspan="6" class="empty">Belum ada penjualan.</td></tr>';
echo '</tbody></table></div>';
page_footer();