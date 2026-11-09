<?php
require_once 'config.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s = $pdo->prepare("SELECT u.*, r.nama_role FROM user u JOIN role r ON u.idrole = r.idrole WHERE u.username = ?");
    $s->execute([$_POST['username'] ?? '']);
    $u = $s->fetch();
    $pass = $_POST['password'] ?? '';
    $ok = false;
    if ($u) {
        if (password_verify($pass, $u['password'])) {
            $ok = true;
        } elseif (hash_equals((string)$u['password'], $pass)) { // data awal masih plain text -> otomatis di-hash
            $ok = true;
            $pdo->prepare("UPDATE user SET password = ? WHERE iduser = ?")
                ->execute([password_hash($pass, PASSWORD_DEFAULT), $u['iduser']]);
        }
    }
    if ($ok) {
        session_regenerate_id(true);
        unset($u['password']);
        $_SESSION['user'] = $u;
        header('Location: index.php'); exit;
    }
    $error = 'Username atau password salah.';
}
?>
<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - BukuKita</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh">
<div class="container" style="max-width:380px">
  <div class="card shadow-sm"><div class="card-body p-4">
    <h4 class="text-center mb-3">📚 BukuKita</h4>
    <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <div class="mb-3"><label class="form-label">Username</label><input name="username" class="form-control" required autofocus></div>
      <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
      <button class="btn btn-primary w-100">Masuk</button>
    </form>
  </div></div>
</div></body></html>
