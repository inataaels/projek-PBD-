<?php
require_once __DIR__ . '/layout.php';

/*
 * Halaman CRUD generik untuk tabel master.
 * $c = [title, file, table, pk, soft=>[kolom,nilaiNonaktif]|null,
 *       fields=>[[name,label,type(text|number|select|password),options|sql]],
 *       list_sql, columns=>[[label,key,fmt]]]
 */
function crud_page(array $c) {
    global $conn;
    $pk = $c['pk']; $t = $c['table'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            if (($_POST['aksi'] ?? '') === 'hapus') {
                if (!empty($c['soft'])) {
                    x($conn, "UPDATE $t SET {$c['soft'][0]}=? WHERE $pk=?", [$c['soft'][1], $_POST['id']]);
                    flash('Data dinonaktifkan.');
                } else {
                    x($conn, "DELETE FROM $t WHERE $pk=?", [$_POST['id']]);
                    flash('Data berhasil dihapus.');
                }
            } else {
                $cols = []; $vals = [];
                foreach ($c['fields'] as $f) {
                    $v = trim($_POST[$f['name']] ?? '');
                    if ($f['type'] === 'password') {
                        if ($v === '' && !empty($_POST['id'])) continue;
                        $v = password_hash($v, PASSWORD_DEFAULT);
                    }
                    $cols[] = $f['name']; $vals[] = ($v === '' ? null : $v);
                }
                if (!empty($_POST['id'])) {
                    $set = implode(',', array_map(fn($k) => "$k=?", $cols));
                    x($conn, "UPDATE $t SET $set WHERE $pk=?", [...$vals, $_POST['id']]);
                    flash('Data berhasil diperbarui.');
                } else {
                    $ph = implode(',', array_fill(0, count($cols), '?'));
                    x($conn, "INSERT INTO $t (" . implode(',', $cols) . ") VALUES ($ph)", $vals);
                    flash('Data berhasil ditambahkan.');
                }
            }
        } catch (mysqli_sql_exception $e) {
            $msg = $e->getMessage();
            // 1451 = tidak bisa hapus/ubah karena masih dipakai tabel lain (foreign key)
            if ($e->getCode() == 1451 || stripos($msg, 'foreign key constraint') !== false) {
                $msg = 'Data tidak bisa dihapus karena masih dipakai oleh data lain.';
            }
            flash('Gagal : ' . $msg, 'err');
        }
        redirect($c['file']);
    }

    $edit = null;
    if (isset($_GET['edit'])) {
        $r = q($conn, "SELECT * FROM $t WHERE $pk=?", [$_GET['edit']]);
        $edit = $r ? $r[0] : null;
    }

    page_header($c['title'], $c['file']);
    echo '<div class="card"><h3>' . ($edit ? 'Edit' : 'Tambah') . ' ' . e($c['title']) . '</h3>
          <form method="post" class="form">
          <input type="hidden" name="id" value="' . e($edit[$pk] ?? '') . '">';
    foreach ($c['fields'] as $f) {
        $val = $edit[$f['name']] ?? '';
        echo '<label>' . e($f['label']);
        if ($f['type'] === 'select') {
            $opts = isset($f['sql']) ? q($conn, $f['sql']) : $f['options'];
            echo '<select name="' . e($f['name']) . '" required><option value="">-- pilih --</option>';
            foreach ($opts as $o) {
                [$ov, $ol] = isset($f['sql']) ? [$o['v'], $o['l']] : $o;
                echo '<option value="' . e($ov) . '"' . ((string)$val === (string)$ov ? ' selected' : '') . '>' . e($ol) . '</option>';
            }
            echo '</select>';
        } elseif ($f['type'] === 'password') {
            echo '<input type="password" name="' . e($f['name']) . '" ' . ($edit ? '' : 'required') . ' placeholder="' . ($edit ? 'kosongkan jika tidak diubah' : '') . '">';
        } else {
            echo '<input type="' . $f['type'] . '" step="any" name="' . e($f['name']) . '" value="' . e($val) . '" required>';
        }
        echo '</label>';
    }
    echo '<div><button class="btn" type="submit">Simpan</button>' .
         ($edit ? ' <a class="btn gray" href="' . $c['file'] . '">Batal</a>' : '') . '</div></form></div>';

    $rows = q($conn, $c['list_sql']);
    echo '<div class="card"><table class="tbl"><thead><tr><th>No</th>';
    foreach ($c['columns'] as $col) echo '<th>' . e($col[0]) . '</th>';
    echo '<th>Aksi</th></tr></thead><tbody>';
    foreach ($rows as $i => $r) {
        echo '<tr><td>' . ($i + 1) . '</td>';
        foreach ($c['columns'] as $col) {
            $v = $r[$col[1]];
            echo '<td>' . (($col[2] ?? '') === 'rp' ? rp($v) : e($v)) . '</td>';
        }
        echo '<td><a class="btn small" href="' . $c['file'] . '?edit=' . e($r[$pk]) . '">Edit</a>
              <form method="post" style="display:inline" onsubmit="return confirm(\'Yakin?\')">
              <input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="' . e($r[$pk]) . '">
              <button class="btn small red">Hapus</button></form></td></tr>';
    }
    if (!$rows) echo '<tr><td colspan="' . (count($c['columns']) + 2) . '" class="empty">Belum ada data.</td></tr>';
    echo '</tbody></table></div>';
    page_footer();
}
