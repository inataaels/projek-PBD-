<?php
require_once 'config.php'; need_login();
$n = fn($t) => $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
$stok = $pdo->query("SELECT b.idbarang AS ID, b.nama AS Barang,
    IFNULL((SELECT stock FROM kartu_stok k WHERE k.idbarang = b.idbarang ORDER BY k.idkartu_stok DESC LIMIT 1), 0) AS Stok,
    hitung_harga_jual(b.idbarang) AS 'Harga Jual'
    FROM barang b WHERE b.status = 1")->fetchAll();
include 'header.php';
?>
<h4 class="mb-3">Dashboard</h4>
<div class="row g-3 mb-4">
  <?php foreach (['Barang' => 'barang', 'Pengadaan' => 'pengadaan', 'Penerimaan' => 'penerimaan', 'Penjualan' => 'penjualan'] as $l => $t): ?>
    <div class="col-6 col-md-3"><div class="card text-center"><div class="card-body">
      <div class="fs-2 fw-bold"><?= $n($t) ?></div><div class="text-muted"><?= $l ?></div>
    </div></div></div>
  <?php endforeach; ?>
</div>
<div class="card"><div class="card-header">Stok Barang</div><div class="card-body"><?php tabel($stok); ?></div></div>
<?php include 'footer.php'; ?>
