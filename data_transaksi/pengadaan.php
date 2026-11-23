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
        if (!$qty) throw new Exception('Tambahkan minimal satu barang dengan jumlah > 0.');

        // header di-INSERT langsung; harga satuan, subtotal, PPN 11%, total dihitung SP + function
        $id = x($conn, "INSERT INTO pengadaan (user_iduser,status,vendor_idvendor,subtotal_nilai,ppn,total_nilai) VALUES (?,?,?,0,0,0)",
                [$_POST['iduser'], 'P', $_POST['idvendor']]);
        foreach ($qty as $b => $j) {
            x($conn, "CALL sp_detail_pengadaan(?,?,?)", [$id, $b, $j]);
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
$idv = (int)($_GET['idvendor'] ?? 0);

echo '<div class="card"><h3>Buat Pengadaan Baru</h3>
<form method="get" class="form"><label>Vendor' .
     select_html('idvendor', q($conn, "SELECT idvendor, nama_vendor FROM view_vendor_aktif"), 'idvendor', 'nama_vendor', $idv, 'onchange="this.form.submit()"') .
     '</label></form>';

if ($idv) {
    // hanya barang aktif milik vendor yang dipilih
    $items = q($conn, "SELECT b.idbarang, b.nama, b.harga, COALESCE((SELECT k.stock FROM kartu_stok k WHERE k.idbarang=b.idbarang ORDER BY k.idkartu_stok DESC LIMIT 1),0) AS stok
                       FROM barang b WHERE b.idvendor=? AND b.status=1 ORDER BY b.nama", [$idv]);
    echo '<form method="post"><input type="hidden" name="idvendor" value="' . $idv . '">
    <div class="form"><label>User' . user_field() . '</label></div>';
    item_table($items);
    echo '<p><button class="btn" type="submit">Simpan Pengadaan</button></p></form>';
} else {
    echo '<p class="empty">Pilih vendor dulu untuk menampilkan barangnya.</p>';
}
echo '</div>';

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
