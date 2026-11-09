<?php
require_once 'config.php'; need_login();
$f = (int)($_GET['barang'] ?? 0);
$barang = $pdo->query("SELECT idbarang, nama FROM barang")->fetchAll(PDO::FETCH_KEY_PAIR);
if ($f) {
    $s = $pdo->prepare("SELECT ks.idkartu_stok AS ID, ks.created_at AS Waktu, ks.idtransaksi AS 'ID Transaksi', b.nama AS Barang,
        CASE ks.jenis_transaksi WHEN 'P' THEN 'Penerimaan' WHEN 'J' THEN 'Penjualan' WHEN 'R' THEN 'Retur' END AS Jenis,
        ks.masuk AS Masuk, ks.keluar AS Keluar, ks.stock AS Stok
        FROM kartu_stok ks JOIN barang b ON b.idbarang=ks.idbarang WHERE ks.idbarang=? ORDER BY ks.idkartu_stok DESC LIMIT 200");
    $s->execute([$f]); $rows = $s->fetchAll();
} else {
    $rows = $pdo->query("SELECT * FROM viewkartu_stok ORDER BY ID DESC LIMIT 200")->fetchAll();
}
include 'header.php';
?>
<h4 class="mb-3">Kartu Stok</h4>
<form class="row g-2 mb-3"><div class="col-auto">
  <select name="barang" class="form-select" onchange="this.form.submit()">
    <option value="0">Semua barang</option>
    <?php foreach ($barang as $id => $n): ?><option value="<?= $id ?>" <?= $f === $id ? 'selected' : '' ?>><?= e($n) ?></option><?php endforeach; ?>
  </select></div></form>
<?php tabel($rows); ?>
<?php include 'footer.php'; ?>
