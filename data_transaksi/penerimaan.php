<?php
require_once 'config/layout.php';

// sisa yang belum diterima per baris detail pengadaan
function item_terima($conn, $idpengadaan) {
    return q($conn, "SELECT d.iddetail_pengadaan, d.idbarang, b.nama, d.jumlah, d.harga_satuan,
        d.jumlah - COALESCE((SELECT SUM(dp.jumlah_terima) FROM detail_penerimaan dp
                             JOIN penerimaan pn ON dp.idpenerimaan=pn.idpenerimaan
                             WHERE pn.idpengadaan=d.idpengadaan AND dp.barang_idbarang=d.idbarang),0) AS sisa
        FROM detail_pengadaan d JOIN barang b ON d.idbarang=b.idbarang WHERE d.idpengadaan=?", [$idpengadaan]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();
        $idp = (int)$_POST['idpengadaan'];
        $p = q($conn, "SELECT status FROM pengadaan WHERE idpengadaan=? FOR UPDATE", [$idp]);
        if (!$p || $p[0]['status'] !== 'P') throw new Exception('Pengadaan tidak valid atau sudah selesai.');
        $id = x($conn, "INSERT INTO penerimaan (status,idpengadaan,iduser) VALUES ('P',?,?)", [$idp, $_POST['iduser']]);
        $n = 0;
        foreach (item_terima($conn, $idp) as $d) {
            $k  = $d['iddetail_pengadaan'];
            $jt = (int)($_POST['terima'][$k] ?? 0);
            if ($jt <= 0) continue;
            $harga = (int)($_POST['harga'][$k] ?? 0);   // harga_satuan_terima boleh diedit
            // kartu stok, harga barang, dan status (sinkron dengan pengadaan) diurus trigger
            x($conn, "CALL sp_detail_penerimaan(?,?,?,?)", [$id, $d['idbarang'], $jt, $harga > 0 ? $harga : null]);
            $n++;
        }
        if (!$n) throw new Exception('Isi jumlah terima minimal untuk satu barang.');
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
    echo '<form method="post"><input type="hidden" name="idpengadaan" value="' . $pilih . '">
    <div class="form"><label>User Penerima' . user_field() . '</label></div>
    <table class="tbl"><thead><tr><th>Barang</th><th>Dipesan</th><th>Sisa</th><th>Harga Satuan Terima</th><th>Jumlah Diterima</th></tr></thead><tbody>';
    foreach (item_terima($conn, $pilih) as $d) {
        $k = $d['iddetail_pengadaan'];
        if ($d['sisa'] <= 0) {
            echo '<tr><td>' . e($d['nama']) . '</td><td>' . $d['jumlah'] . '</td><td>0</td><td>' . rp($d['harga_satuan']) . '</td><td><span class="badge ok">Lengkap</span></td></tr>';
            continue;
        }
        echo '<tr><td>' . e($d['nama']) . '</td><td>' . $d['jumlah'] . '</td><td>' . $d['sisa'] . '</td>
        <td><input type="number" min="0" name="harga[' . $k . ']" value="' . $d['harga_satuan'] . '"></td>
        <td><input type="number" min="0" max="' . $d['sisa'] . '" name="terima[' . $k . ']" value="' . $d['sisa'] . '"></td></tr>';
    }
    echo '</tbody></table><p><button class="btn" type="submit">Simpan Penerimaan</button></p></form>';
}
echo '</div>';

$list = q($conn, "SELECT pn.idpenerimaan, pn.created_at, pn.idpengadaan, pn.status, u.username,
    COALESCE((SELECT SUM(sub_total_terima) FROM detail_penerimaan dp WHERE dp.idpenerimaan=pn.idpenerimaan),0) AS nilai
    FROM penerimaan pn JOIN user u ON pn.iduser=u.iduser ORDER BY pn.idpenerimaan DESC");
echo '<div class="card"><h3>Daftar Penerimaan</h3><table class="tbl"><thead><tr><th>ID</th><th>Tanggal</th><th>Pengadaan</th><th>User</th><th>Nilai</th><th>Status</th><th>Aksi</th></tr></thead><tbody>';
foreach ($list as $r) {
    echo '<tr><td>' . $r['idpenerimaan'] . '</td><td>' . e($r['created_at']) . '</td><td>#' . $r['idpengadaan'] . '</td><td>' . e($r['username']) . '</td><td>' . rp($r['nilai']) . '</td>
    <td><span class="badge ' . ($r['status'] === 'S' ? 'ok' : 'warn') . '">' . ($r['status'] === 'S' ? 'Selesai' : 'Diproses') . '</span></td>
    <td><a class="btn small" href="detail_penerimaan.php?id=' . $r['idpenerimaan'] . '">Detail</a></td></tr>';
}
if (!$list) echo '<tr><td colspan="7" class="empty">Belum ada penerimaan.</td></tr>';
echo '</tbody></table></div>';
page_footer();
