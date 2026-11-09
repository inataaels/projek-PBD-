<?php
require_once 'config.php'; need_role([301]);

// [judul, tabel, primary key, SQL list, field form]
$cfg = [
 'barang' => ['Barang', 'barang', 'idbarang',
   "SELECT b.idbarang ID, b.jenis Jenis, b.nama Nama, b.harga Harga, s.nama_satuan Satuan, IF(b.status=1,'Aktif','Nonaktif') Status
    FROM barang b JOIN satuan s ON b.idsatuan=s.idsatuan ORDER BY b.idbarang",
   ['jenis'=>['Jenis','enum',['B'=>'Buku','A'=>'Aksesoris','C'=>'Card','G'=>'Game']],
    'nama'=>['Nama','text'], 'harga'=>['Harga Beli','number'],
    'idsatuan'=>['Satuan','sel',"SELECT idsatuan, nama_satuan FROM satuan WHERE status=1"],
    'status'=>['Status','enum',[1=>'Aktif',0=>'Nonaktif']]]],
 'satuan' => ['Satuan', 'satuan', 'idsatuan',
   "SELECT idsatuan ID, nama_satuan Satuan, IF(status=1,'Aktif','Nonaktif') Status FROM satuan",
   ['nama_satuan'=>['Nama Satuan','text'], 'status'=>['Status','enum',[1=>'Aktif',0=>'Nonaktif']]]],
 'vendor' => ['Vendor', 'vendor', 'idvendor',
   "SELECT idvendor ID, nama_vendor Vendor, IF(badan_hukum='Y','Badan Hukum','Non Badan Hukum') Jenis, IF(status='A','Aktif','Nonaktif') Status FROM vendor",
   ['nama_vendor'=>['Nama Vendor','text'], 'badan_hukum'=>['Badan Hukum','enum',['Y'=>'Ya','N'=>'Tidak']],
    'status'=>['Status','enum',['A'=>'Aktif','N'=>'Nonaktif']]]],
 'role' => ['Role', 'role', 'idrole',
   "SELECT idrole ID, nama_role Role FROM role",
   ['nama_role'=>['Nama Role','text']]],
 'user' => ['User', 'user', 'iduser',
   "SELECT u.iduser ID, u.username Username, r.nama_role Role FROM user u JOIN role r ON u.idrole=r.idrole",
   ['username'=>['Username','text'], 'password'=>['Password (kosongkan jika tidak diubah)','password'],
    'idrole'=>['Role','sel',"SELECT idrole, nama_role FROM role"]]],
 'margin' => ['Margin Penjualan', 'margin_penjualan', 'idmargin_penjualan',
   "SELECT m.idmargin_penjualan ID, m.persen 'Persen (%)', IF(m.status=1,'Aktif','Nonaktif') Status, m.created_at Dibuat, m.updated_at Diubah, u.username Oleh
    FROM margin_penjualan m JOIN user u ON m.iduser=u.iduser",
   ['persen'=>['Persen (%)','number'], 'status'=>['Status (hanya 1 yang aktif)','enum',[1=>'Aktif',0=>'Nonaktif']]]],
];

$t = $_GET['t'] ?? 'barang';
if (!isset($cfg[$t])) $t = 'barang';
[$title, $table, $pk, $sql, $fields] = $cfg[$t];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete'])) {
            $pdo->prepare("DELETE FROM $table WHERE $pk = ?")->execute([$_POST['delete']]);
            flash('Data dihapus.');
        } else {
            $id = $_POST['id'] ?? '';
            $cols = []; $vals = [];
            foreach ($fields as $f => $d) {
                $v = trim($_POST[$f] ?? '');
                if ($d[1] === 'password') { if ($v === '') continue; $v = password_hash($v, PASSWORD_DEFAULT); }
                $cols[] = $f; $vals[] = $v;
            }
            if ($id === '') {
                if ($t === 'margin') { $cols[] = 'iduser'; $vals[] = $_SESSION['user']['iduser']; }
                $pdo->prepare("INSERT INTO $table (" . implode(',', $cols) . ") VALUES (" . implode(',', array_fill(0, count($cols), '?')) . ")")->execute($vals);
                $id = $pdo->lastInsertId();
            } else {
                $pdo->prepare("UPDATE $table SET " . implode('=?,', $cols) . "=? WHERE $pk = ?")->execute([...$vals, $id]);
            }
            if ($t === 'margin' && ($_POST['status'] ?? '') === '1') // hanya satu margin aktif
                $pdo->prepare("UPDATE margin_penjualan SET status=0 WHERE idmargin_penjualan <> ?")->execute([$id]);
            flash('Data tersimpan.');
        }
    } catch (PDOException $ex) {
        flash('Gagal: ' . ($ex->getCode() == 23000 ? 'data sedang dipakai di tabel lain / duplikat.' : $ex->getMessage()), 'danger');
    }
    header("Location: master.php?t=$t"); exit;
}

$edit = [];
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM $table WHERE $pk = ?"); $s->execute([$_GET['edit']]);
    $edit = $s->fetch() ?: [];
}
$rows = $pdo->query($sql)->fetchAll();
include 'header.php';
?>
<h4 class="mb-3">Data Master</h4>
<ul class="nav nav-pills mb-3">
  <?php foreach ($cfg as $k => $c): ?>
    <li class="nav-item"><a class="nav-link <?= $k === $t ? 'active' : '' ?>" href="?t=<?= $k ?>"><?= e($c[0]) ?></a></li>
  <?php endforeach; ?>
</ul>

<div class="card mb-4"><div class="card-header"><?= $edit ? 'Ubah' : 'Tambah' ?> <?= e($title) ?></div>
<div class="card-body">
  <form method="post" class="row g-3">
    <input type="hidden" name="id" value="<?= e($edit[$pk] ?? '') ?>">
    <?php foreach ($fields as $f => $d): $val = $d[1] === 'password' ? '' : ($edit[$f] ?? ''); ?>
      <div class="col-md-4">
        <label class="form-label"><?= e($d[0]) ?></label>
        <?php if ($d[1] === 'enum' || $d[1] === 'sel'):
            $opts = $d[1] === 'enum' ? $d[2] : $pdo->query($d[2])->fetchAll(PDO::FETCH_KEY_PAIR); ?>
          <select name="<?= $f ?>" class="form-select" required>
            <?php foreach ($opts as $k => $l): ?>
              <option value="<?= e($k) ?>" <?= (string)$val === (string)$k ? 'selected' : '' ?>><?= e($l) ?></option>
            <?php endforeach; ?>
          </select>
        <?php else: ?>
          <input name="<?= $f ?>" type="<?= $d[1] ?>" step="any" class="form-control" value="<?= e($val) ?>"
                 <?= ($d[1] === 'password' && $edit) ? '' : 'required' ?>>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    <div class="col-12">
      <button class="btn btn-primary">Simpan</button>
      <?php if ($edit): ?><a class="btn btn-secondary" href="?t=<?= $t ?>">Batal</a><?php endif; ?>
    </div>
  </form>
</div></div>

<?php tabel($rows, function ($r) use ($t) {
    $id = e($r['ID']);
    return "<a class='btn btn-sm btn-warning' href='?t=$t&edit=$id'>Ubah</a> "
         . "<form method='post' class='d-inline' onsubmit=\"return confirm('Hapus data ini?')\">"
         . "<button class='btn btn-sm btn-danger' name='delete' value='$id'>Hapus</button></form>";
}); ?>
<?php include 'footer.php'; ?>
