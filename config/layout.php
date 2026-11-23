<?php
require_once __DIR__ . '/database.php';

const PPN_PERSEN = 11;

/* ---------- Helper umum ---------- */
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rp($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function redirect($url) { header("Location: $url"); exit; }

// SELECT -> array of rows
function q($conn, $sql, $p = []) {
    $st = $conn->prepare($sql);
    $p ? $st->execute($p) : $st->execute();
    $r = $st->get_result();
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}
// INSERT/UPDATE/DELETE -> insert_id
function x($conn, $sql, $p = []) {
    $st = $conn->prepare($sql);
    $p ? $st->execute($p) : $st->execute();
    return $conn->insert_id;
}

function flash($m = null, $t = 'ok') {
    if ($m !== null) { $_SESSION['flash'] = [$t, $m]; return; }
    if (isset($_SESSION['flash'])) {
        [$t, $m] = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return "<div class='alert $t'>" . e($m) . "</div>";
    }
    return '';
}

/* ---------- Login & hak akses ---------- */
function akses_role() {
    return [
        'admin'  => null, // semua halaman
        'kasir'  => ['index.php','penjualan.php','detail_penjualan.php','kartu_stok.php'],
        'gudang' => ['index.php','barang.php','pengadaan.php','detail_pengadaan.php','penerimaan.php',
                     'detail_penerimaan.php','retur.php','detail_retur.php','kartu_stok.php'],
    ];
}
function boleh($file) {
    $r = strtolower($_SESSION['role'] ?? '');
    $a = akses_role();
    if (!array_key_exists($r, $a)) return false;
    return $a[$r] === null || in_array($file, $a[$r]);
}
function require_login() {
    if (empty($_SESSION['iduser'])) redirect('halaman_login.php');
    if (!boleh(basename($_SERVER['PHP_SELF']))) {
        http_response_code(403);
        die('<p style="font-family:sans-serif;padding:30px">Anda tidak memiliki akses ke halaman ini. <a href="index.php">Kembali</a></p>');
    }
}
function user_field() {
    return '<input type="hidden" name="iduser" value="' . e($_SESSION['iduser']) . '">'
         . '<input type="text" value="' . e($_SESSION['username']) . '" readonly>';
}
require_login();

/* ---------- Stok & kartu stok ---------- */
function stok_barang($conn, $idbarang) {
    $r = q($conn, "SELECT stock FROM kartu_stok WHERE idbarang=? ORDER BY idkartu_stok DESC LIMIT 1", [$idbarang]);
    return $r ? (int)$r[0]['stock'] : 0;
}
// jenis: T = penerimaan, J = penjualan, R = retur
function catat_stok($conn, $idbarang, $jenis, $masuk, $keluar, $idtransaksi) {
    $baru = stok_barang($conn, $idbarang) + $masuk - $keluar;
    if ($baru < 0) throw new Exception('Stok tidak mencukupi untuk barang #' . $idbarang);
    x($conn, "INSERT INTO kartu_stok (jenis_transaksi,masuk,keluar,stock,idtransaksi,idbarang) VALUES (?,?,?,?,?,?)",
      [$jenis, $masuk, $keluar, $baru, $idtransaksi, $idbarang]);
}
function barang_aktif($conn) {
    return q($conn, "SELECT b.idbarang, b.nama, b.harga,
        COALESCE((SELECT k.stock FROM kartu_stok k WHERE k.idbarang=b.idbarang ORDER BY k.idkartu_stok DESC LIMIT 1),0) AS stok
        FROM barang b WHERE b.status=1 ORDER BY b.nama");
}

/* ---------- Komponen HTML ---------- */
function select_html($name, $rows, $v, $l, $sel = null, $extra = '') {
    $h = "<select name='" . e($name) . "' $extra><option value=''>-- pilih --</option>";
    foreach ($rows as $r) {
        $s = ((string)$sel === (string)$r[$v]) ? 'selected' : '';
        $h .= "<option value='" . e($r[$v]) . "' $s>" . e($r[$l]) . "</option>";
    }
    return $h . "</select>";
}

function item_table(array $barang, bool $stok = false, bool $hargaReadonly = false) {
    $opt = '<option value="">-- pilih barang --</option>';
    foreach ($barang as $b) {
        $opt .= '<option value="' . e($b['idbarang']) . '" data-harga="' . e($b['harga']) . '">'
              . e($b['nama']) . ($stok ? ' (stok: ' . $b['stok'] . ')' : '') . '</option>';
    }
    $ro = $hargaReadonly ? 'readonly' : '';
    echo '<table class="tbl"><thead><tr><th>Barang</th><th>Jumlah</th><th>Harga Satuan</th><th>Subtotal</th><th></th></tr></thead><tbody id="rows"></tbody></table>
    <template id="tpl"><tr>
      <td><select name="barang[]" class="brg" required>' . $opt . '</select></td>
      <td><input type="number" name="jumlah[]" class="jml" min="1" value="1"></td>
      <td><input type="number" name="harga[]" class="hrg" min="0" value="0" ' . $ro . '></td>
      <td class="sub">0</td>
      <td><button type="button" class="btn red del">&times;</button></td>
    </tr></template>
    <p><button type="button" id="addRow" class="btn">+ Tambah Barang</button></p>
    <div class="totals" data-ppn="' . PPN_PERSEN . '">Subtotal: <b id="tSub">0</b> &nbsp;|&nbsp; PPN ' . PPN_PERSEN . '%: <b id="tPpn">0</b> &nbsp;|&nbsp; Total: <b id="tTot">0</b></div>';
}

function page_header($title, $active) {
    $menu = [
        'MENU UTAMA' => [['index.php', 'Dashboard']],
        'DATA MASTER' => [['role.php', 'Role'], ['user.php', 'User'], ['margin.php', 'Margin Penjualan'],
                          ['satuan.php', 'Satuan'], ['vendor.php', 'Vendor'], ['barang.php', 'Barang']],
        'TRANSAKSI' => [['pengadaan.php', 'Pengadaan'], ['penerimaan.php', 'Penerimaan'],
                        ['penjualan.php', 'Penjualan'], ['retur.php', 'Retur']],
        'LAPORAN' => [['kartu_stok.php', 'Kartu Stok']],
    ];
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . e($title) . ' - BukuKita</title>
    <link rel="stylesheet" href="assets/style.css"></head><body>
    <div class="sidebar"><h2>BukuKita</h2>';
    foreach ($menu as $judul => $links) {
        $links = array_filter($links, fn($l) => boleh($l[0]));
        if (!$links) continue;
        echo '<p class="menu-title">' . $judul . '</p>';
        foreach ($links as [$href, $label]) {
            echo '<a href="' . $href . '"' . ($href === $active ? ' class="active"' : '') . '>' . $label . '</a>';
        }
    }
    echo '<div class="userbox">' . e($_SESSION['username']) . '<small>' . e($_SESSION['role']) . '</small><a href="logout.php">Logout</a></div>';
    echo '</div><div class="content"><h1>' . e($title) . '</h1>' . flash();
}

function page_footer() {
    echo '</div><script src="assets/app.js"></script></body></html>';
}
