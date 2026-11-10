<?php
require_once 'config/database.php';

if (!empty($_SESSION['iduser'])) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    $st = $conn->prepare("SELECT u.iduser, u.username, u.password, r.nama_role
                          FROM user u JOIN role r ON u.idrole = r.idrole WHERE u.username = ?");
    $st->execute([$u]);
    $row = $st->get_result()->fetch_assoc();

    // password_verify untuk user baru; perbandingan biasa hanya untuk data contoh di SQL
    if ($row && (password_verify($p, $row['password']) || hash_equals($row['password'], $p))) {
        session_regenerate_id(true);
        $_SESSION['iduser']   = $row['iduser'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role']     = $row['nama_role'];
        header('Location: index.php');
        exit;
    }
    $error = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BukuKita</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-page">
    <form method="post" class="login-box">
        <h2>BUKUKITA</h2>
        <p>Silakan login untuk melanjutkan</p>
        <?php if ($error): ?><div class="alert err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <label>Username<input type="text" name="username" required autofocus></label>
        <label>Password<input type="password" name="password" required></label>
        <button class="btn" type="submit">Login</button>
    </form>
</body>
</html>
