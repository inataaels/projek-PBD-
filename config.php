<?php
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=BukuKita;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rp($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function need_login() { if (empty($_SESSION['user'])) { header('Location: login.php'); exit; } }
function need_role($roles) {
    need_login();
    if (!in_array((int)$_SESSION['user']['idrole'], $roles)) { http_response_code(403); exit('Akses ditolak'); }
}
function flash($msg, $type = 'success') { $_SESSION['flash'] = [$msg, $type]; }

function tabel($rows, $aksi = null) {
    if (!$rows) { echo '<p class="text-muted">Belum ada data.</p>'; return; }
    echo '<div class="table-responsive"><table class="table table-sm table-striped"><thead><tr>';
    foreach (array_keys($rows[0]) as $h) echo '<th>' . e($h) . '</th>';
    if ($aksi) echo '<th></th>';
    echo '</tr></thead><tbody>';
    foreach ($rows as $r) {
        echo '<tr>';
        foreach ($r as $v) echo '<td>' . e($v) . '</td>';
        if ($aksi) echo '<td class="text-nowrap">' . $aksi($r) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}
