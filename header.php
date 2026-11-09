<?php
require_once 'config.php';
need_login();
$u = $_SESSION['user'];
$menu = [
    'index.php'      => ['Dashboard', [301, 302, 303]],
    'master.php'     => ['Data Master', [301]],
    'pengadaan.php'  => ['Pengadaan', [301, 303]],
    'penerimaan.php' => ['Penerimaan', [301, 303]],
    'penjualan.php'  => ['Penjualan', [301, 302]],
    'retur.php'      => ['Retur', [301, 303]],
    'kartu_stok.php' => ['Kartu Stok', [301, 302, 303]],
];
$cur = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>BukuKita</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="index.php">📚 BukuKita</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <?php foreach ($menu as $file => [$label, $roles]): if (!in_array((int)$u['idrole'], $roles)) continue; ?>
          <li class="nav-item"><a class="nav-link <?= $cur === $file ? 'active' : '' ?>" href="<?= $file ?>"><?= $label ?></a></li>
        <?php endforeach; ?>
      </ul>
      <span class="navbar-text me-3"><?= e($u['username']) ?> (<?= e($u['nama_role']) ?>)</span>
      <a class="btn btn-outline-light btn-sm" href="logout.php">Keluar</a>
    </div>
  </div>
</nav>
<div class="container pb-5">
<?php if (!empty($_SESSION['flash'])): [$m, $t] = $_SESSION['flash']; unset($_SESSION['flash']); ?>
  <div class="alert alert-<?= e($t) ?>"><?= e($m) ?></div>
<?php endif; ?>
