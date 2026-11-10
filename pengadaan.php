<?php
require_once 'config/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();
        $rows = []; $sub = 0;
        foreach (($_POST['barang'] ?? []) as $i => $b) {
            $j = (int)$_POST['jumlah'][$i]; $h = (int)$_POST['harga'][$i];
            if (!$b || $j <= 0 || $h < 0) continue;
            $rows[] = [$b, $j, $h]; $sub += $j * $h;
        }
        if (!$rows) throw new Exception('Tambahkan minimal satu barang dengan jumlah > 0.');
        $ppn = (int)round($sub * PPN_PERSEN / 100);
        $id = x($conn, "INSERT INTO pengadaan (user_iduser,status,vendor_idvendor,subtotal_nilai,ppn,total_nilai) VALUES (?,?,?,?,?,?)",
                [$_POST['iduser'], 'P', $_POST['idvendor'], $sub, $ppn, $sub + $ppn]);
        foreach ($rows as [$b, $j, $h]) {
            x($conn, "INSERT INTO detail_pengadaan (harga_satuan,jumlah,sub_total,idbarang,idpengadaan) VALUES (?,?,?,?,?)",
              [$h, $j, $j * $h, $b, $id]);
        }
        $conn->commit();
        flash("Pengadaan #$id berhasil dibuat.");
    } catch (Throwable $e) {
        $conn->rollback();
        flash($e->getMessage(), 'err');
    }
    redirect('pengadaan.php');
}

page_header('Pengadaan', 'pengadaan.php');
echo '<div class="card"><h3>Buat Pengadaan Baru</h3><form method="post">
<div class="form"><label>User' . select_html('iduser', q($conn, "SELECT iduser, username FROM user"), 'iduser', 'username', null, 'required') . '</label>
<label>Vendor' . select_html('idvendor', q($conn, "SELECT idvendor, nama_vendor FROM view_vendor_aktif"), 'idvendor', 'nama_vendor', null, 'required') . '</label></div>';
item_table(barang_aktif($conn));
echo '<p><button class="btn" type="submit">Simpan Pengadaan</button></p></form></div>';

$list = q($conn, "SELECT p.idpengadaan, p.`timestamp` AS tgl, v.nama_vendor, u.username, p.total_nilai, p.status
    FROM pengadaan p JOIN vendor v ON p.vendor_idvendor=v.idvendor JOIN user u ON p.user_iduser=u.iduser
    ORDER BY p.idpengadaan DESC");
echo '<div class="card"><h3>Daftar Pengadaan</h3><table class="tbl"><thead><tr><th>ID</th><th>Tanggal</th><th>Vendor</th><th>User</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead><tbody>';
foreach ($list as $r) {
    echo '<tr><td>' . $r['idpengadaan'] . '</td><td>' . e($r['tgl']) . '</td><td>' . e($r['nama_vendor']) . '</td><td>' . e($r['username']) . '</td><td>' . rp($r['total_nilai']) . '</td>
    <td><span class="badge ' . ($r['status'] === 'S' ? 'ok' : 'warn') . '">' . ($r['status'] === 'S' ? 'Selesai' : 'Diproses') . '</span></td>
    <td><a class="btn small" href="detail_pengadaan.php?id=' . $r['idpengadaan'] . '">Detail</a></td></tr>';
}
if (!$list) echo '<tr><td colspan="7" class="empty">Belum ada pengadaan.</td></tr>';
echo '</tbody></table></div>';
page_footer();
