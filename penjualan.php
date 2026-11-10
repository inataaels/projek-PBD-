<?php
require_once 'config/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();
        $m = q($conn, "SELECT persen FROM margin_penjualan WHERE idmargin_penjualan=? AND status=1", [$_POST['idmargin']]);
        if (!$m) throw new Exception('Margin tidak valid.');
        $persen = (float)$m[0]['persen'];

        $qty = [];                                  // gabungkan barang yang sama
        foreach (($_POST['barang'] ?? []) as $i => $b) {
            $j = (int)$_POST['jumlah'][$i];
            if ($b && $j > 0) $qty[$b] = ($qty[$b] ?? 0) + $j;
        }
        if (!$qty) throw new Exception('Tambahkan minimal satu barang.');

        $rows = []; $sub = 0;
        foreach ($qty as $b => $j) {
            $br = q($conn, "SELECT nama, harga FROM barang WHERE idbarang=? AND status=1", [$b]);
            if (!$br) throw new Exception('Barang tidak valid.');
            if (stok_barang($conn, $b) < $j) throw new Exception('Stok "' . $br[0]['nama'] . '" tidak mencukupi.');
            $hj = (int)round($br[0]['harga'] * (1 + $persen / 100));   // harga jual = harga beli + margin
            $rows[] = [$b, $j, $hj]; $sub += $j * $hj;
        }
        $ppn = (int)round($sub * PPN_PERSEN / 100);
        $id = x($conn, "INSERT INTO penjualan (subtotal_nilai,ppn,total_nilai,iduser,idmargin_penjualan) VALUES (?,?,?,?,?)",
                [$sub, $ppn, $sub + $ppn, $_POST['iduser'], $_POST['idmargin']]);
        foreach ($rows as [$b, $j, $hj]) {
            x($conn, "INSERT INTO detail_penjualan (harga_satuan,jumlah,subtotal,penjualan_idpenjualan,idbarang) VALUES (?,?,?,?,?)",
              [$hj, $j, $j * $hj, $id, $b]);
            catat_stok($conn, $b, 'J', 0, $j, $id);
        }
        $conn->commit();
        flash("Penjualan #$id berhasil. Total " . rp($sub + $ppn));
    } catch (Throwable $e) {
        $conn->rollback();
        flash($e->getMessage(), 'err');
    }
    redirect('penjualan.php');
}

page_header('Penjualan', 'penjualan.php');
$margin = q($conn, "SELECT idmargin_penjualan, persen FROM margin_penjualan WHERE status=1 ORDER BY persen");
echo '<div class="card"><h3>Transaksi Penjualan Baru</h3><form method="post"><div class="form">
<label>Kasir' . select_html('iduser', q($conn, "SELECT iduser, username FROM user"), 'iduser', 'username', null, 'required') . '</label>
<label>Margin<select name="idmargin" id="margin" required><option value="">-- pilih --</option>';
foreach ($margin as $m) echo '<option value="' . $m['idmargin_penjualan'] . '" data-persen="' . $m['persen'] . '">' . $m['persen'] . ' %</option>';
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
