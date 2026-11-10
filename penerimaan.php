<?php
require_once 'config/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();
        $idp = (int)$_POST['idpengadaan'];
        $p = q($conn, "SELECT status FROM pengadaan WHERE idpengadaan=? FOR UPDATE", [$idp]);
        if (!$p || $p[0]['status'] !== 'P') throw new Exception('Pengadaan tidak valid atau sudah diterima.');
        $det = q($conn, "SELECT * FROM detail_pengadaan WHERE idpengadaan=?", [$idp]);
        $id = x($conn, "INSERT INTO penerimaan (status,idpengadaan,iduser) VALUES ('A',?,?)", [$idp, $_POST['iduser']]);
        $n = 0;
        foreach ($det as $d) {
            $jt = (int)($_POST['terima'][$d['iddetail_pengadaan']] ?? 0);
            if ($jt < 0 || $jt > $d['jumlah']) throw new Exception('Jumlah terima tidak boleh melebihi jumlah pesanan.');
            if ($jt === 0) continue;
            x($conn, "INSERT INTO detail_penerimaan (idpenerimaan,barang_idbarang,jumlah_terima,harga_satuan_terima,sub_total_terima) VALUES (?,?,?,?,?)",
              [$id, $d['idbarang'], $jt, $d['harga_satuan'], $jt * $d['harga_satuan']]);
            catat_stok($conn, $d['idbarang'], 'T', $jt, 0, $id);
            $n++;
        }
        if (!$n) throw new Exception('Isi jumlah terima minimal untuk satu barang.');
        x($conn, "UPDATE pengadaan SET status='S' WHERE idpengadaan=?", [$idp]);
        $conn->commit();
        flash("Penerimaan #$id berhasil dicatat, stok diperbarui.");
    } catch (Throwable $e) {
        $conn->rollback();
        flash($e->getMessage(), 'err');
    }
    redirect('penerimaan.php');
}

page_header('Penerimaan', 'penerimaan.php');
$open = q($conn, "SELECT p.idpengadaan, CONCAT('#', p.idpengadaan, ' - ', v.nama_vendor, ' (', p.total_nilai, ')') AS label
                  FROM pengadaan p JOIN vendor v ON p.vendor_idvendor=v.idvendor WHERE p.status='P'");
$pilih = (int)($_GET['idpengadaan'] ?? 0);

echo '<div class="card"><h3>Terima Barang dari Pengadaan</h3>
<form method="get" class="form"><label>Pengadaan (status Diproses)' . select_html('idpengadaan', $open, 'idpengadaan', 'label', $pilih, 'onchange="this.form.submit()"') . '</label></form>';
if ($pilih) {
    $det = q($conn, "SELECT d.*, b.nama FROM detail_pengadaan d JOIN barang b ON d.idbarang=b.idbarang WHERE d.idpengadaan=?", [$pilih]);
    echo '<form method="post"><input type="hidden" name="idpengadaan" value="' . $pilih . '">
    <div class="form"><label>User Penerima' . user_field() . '</label></div>
    <table class="tbl"><thead><tr><th>Barang</th><th>Dipesan</th><th>Harga</th><th>Jumlah Diterima</th></tr></thead><tbody>';
    foreach ($det as $d) {
        echo '<tr><td>' . e($d['nama']) . '</td><td>' . $d['jumlah'] . '</td><td>' . rp($d['harga_satuan']) . '</td>
        <td><input type="number" min="0" max="' . $d['jumlah'] . '" name="terima[' . $d['iddetail_pengadaan'] . ']" value="' . $d['jumlah'] . '"></td></tr>';
    }
    echo '</tbody></table><p><button class="btn" type="submit">Simpan Penerimaan</button></p></form>';
}
echo '</div>';

$list = q($conn, "SELECT pn.idpenerimaan, pn.created_at, pn.idpengadaan, u.username,
    COALESCE((SELECT SUM(sub_total_terima) FROM detail_penerimaan dp WHERE dp.idpenerimaan=pn.idpenerimaan),0) AS nilai
    FROM penerimaan pn JOIN user u ON pn.iduser=u.iduser ORDER BY pn.idpenerimaan DESC");
echo '<div class="card"><h3>Daftar Penerimaan</h3><table class="tbl"><thead><tr><th>ID</th><th>Tanggal</th><th>Pengadaan</th><th>User</th><th>Nilai</th><th>Aksi</th></tr></thead><tbody>';
foreach ($list as $r) {
    echo '<tr><td>' . $r['idpenerimaan'] . '</td><td>' . e($r['created_at']) . '</td><td>#' . $r['idpengadaan'] . '</td><td>' . e($r['username']) . '</td><td>' . rp($r['nilai']) . '</td>
    <td><a class="btn small" href="detail_penerimaan.php?id=' . $r['idpenerimaan'] . '">Detail</a></td></tr>';
}
if (!$list) echo '<tr><td colspan="6" class="empty">Belum ada penerimaan.</td></tr>';
echo '</tbody></table></div>';
page_footer();
