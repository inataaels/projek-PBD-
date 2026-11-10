<?php
require_once 'config/layout.php';

function item_retur($conn, $idpenerimaan) {
    return q($conn, "SELECT dp.iddetail_penerimaan, dp.barang_idbarang, b.nama, dp.jumlah_terima,
        dp.jumlah_terima - COALESCE((SELECT SUM(r.jumlah) FROM detail_retur r WHERE r.iddetail_penerimaan=dp.iddetail_penerimaan),0) AS sisa
        FROM detail_penerimaan dp JOIN barang b ON dp.barang_idbarang=b.idbarang WHERE dp.idpenerimaan=?", [$idpenerimaan]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();
        $idpn = (int)$_POST['idpenerimaan'];
        $id = x($conn, "INSERT INTO retur (idpenerimaan,iduser) VALUES (?,?)", [$idpn, $_POST['iduser']]);
        $n = 0;
        foreach (item_retur($conn, $idpn) as $it) {
            $k = $it['iddetail_penerimaan'];
            $j = (int)($_POST['jumlah'][$k] ?? 0);
            if ($j <= 0) continue;
            if ($j > $it['sisa']) throw new Exception('Jumlah retur "' . $it['nama'] . '" melebihi sisa yang bisa diretur (' . $it['sisa'] . ').');
            $alasan = trim($_POST['alasan'][$k] ?? '');
            if ($alasan === '') throw new Exception('Alasan retur "' . $it['nama'] . '" wajib diisi.');
            x($conn, "INSERT INTO detail_retur (jumlah,alasan,idretur,iddetail_penerimaan) VALUES (?,?,?,?)", [$j, $alasan, $id, $k]);
            catat_stok($conn, $it['barang_idbarang'], 'R', 0, $j, $id);
            $n++;
        }
        if (!$n) throw new Exception('Isi jumlah retur minimal untuk satu barang.');
        $conn->commit();
        flash("Retur #$id berhasil dicatat, stok dikurangi.");
    } catch (Throwable $e) {
        $conn->rollback();
        flash($e->getMessage(), 'err');
    }
    redirect('retur.php');
}

page_header('Retur', 'retur.php');
$pn = q($conn, "SELECT pn.idpenerimaan, CONCAT('Penerimaan #', pn.idpenerimaan, ' - Pengadaan #', pn.idpengadaan, ' (', pn.created_at, ')') AS label FROM penerimaan pn ORDER BY pn.idpenerimaan DESC");
$pilih = (int)($_GET['idpenerimaan'] ?? 0);
echo '<div class="card"><h3>Retur Barang ke Vendor</h3>
<form method="get" class="form"><label>Penerimaan' . select_html('idpenerimaan', $pn, 'idpenerimaan', 'label', $pilih, 'onchange="this.form.submit()"') . '</label></form>';
if ($pilih) {
    echo '<form method="post"><input type="hidden" name="idpenerimaan" value="' . $pilih . '">
    <div class="form"><label>User' . select_html('iduser', q($conn, "SELECT iduser, username FROM user"), 'iduser', 'username', null, 'required') . '</label></div>
    <table class="tbl"><thead><tr><th>Barang</th><th>Diterima</th><th>Sisa Bisa Diretur</th><th>Jumlah Retur</th><th>Alasan</th></tr></thead><tbody>';
    foreach (item_retur($conn, $pilih) as $it) {
        $k = $it['iddetail_penerimaan'];
        echo '<tr><td>' . e($it['nama']) . '</td><td>' . $it['jumlah_terima'] . '</td><td>' . $it['sisa'] . '</td>
        <td><input type="number" min="0" max="' . $it['sisa'] . '" name="jumlah[' . $k . ']" value="0"></td>
        <td><input type="text" maxlength="200" name="alasan[' . $k . ']" placeholder="mis. cacat cetak"></td></tr>';
    }
    echo '</tbody></table><p><button class="btn" type="submit">Simpan Retur</button></p></form>';
}
echo '</div>';

$list = q($conn, "SELECT r.idretur, r.created_at, r.idpenerimaan, u.username FROM retur r JOIN user u ON r.iduser=u.iduser ORDER BY r.idretur DESC");
echo '<div class="card"><h3>Daftar Retur</h3><table class="tbl"><thead><tr><th>ID</th><th>Tanggal</th><th>Penerimaan</th><th>User</th><th>Aksi</th></tr></thead><tbody>';
foreach ($list as $r) {
    echo '<tr><td>' . $r['idretur'] . '</td><td>' . e($r['created_at']) . '</td><td>#' . $r['idpenerimaan'] . '</td><td>' . e($r['username']) . '</td>
    <td><a class="btn small" href="detail_retur.php?id=' . $r['idretur'] . '">Detail</a></td></tr>';
}
if (!$list) echo '<tr><td colspan="5" class="empty">Belum ada retur.</td></tr>';
echo '</tbody></table></div>';
page_footer();
