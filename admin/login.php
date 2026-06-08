<?php
require_once __DIR__ . '/../includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['hpc_user'])) { header('Location: /admin/index.php'); exit; }

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    $st = db()->prepare('SELECT * FROM editors WHERE username=? LIMIT 1');
    $st->execute([$u]);
    $row = $st->fetch();
    if ($row && hash('sha256', $p) === $row['pass_hash']) {
        $_SESSION['hpc_user'] = ['id'=>$row['id'],'username'=>$row['username'],'name'=>$row['name'],'role'=>$row['role']];
        db()->prepare('UPDATE editors SET last_login=? WHERE id=?')->execute([now_ts(),$row['id']]);
        header('Location: /admin/index.php'); exit;
    }
    $err = '用户名或密码错误';
}
$siteName = setting('siteName','汇评测');
?>
<!DOCTYPE html><html lang="zh-CN"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>登录 · <?= h($siteName) ?>后台</title>
<link rel="icon" href="/assets/img/favicon.svg">
<link rel="stylesheet" href="/assets/css/admin.css">
</head><body>
<div class="login-wrap">
  <form class="login-card" method="post">
    <div class="lg"><span class="brand-mark"></span><?= h($siteName) ?> 后台</div>
    <div class="sub">外汇经纪商评测管理系统</div>
    <?php if($err): ?><div class="flash flash-err"><?= h($err) ?></div><?php endif; ?>
    <div class="form-row"><label>用户名</label><input class="inp" name="username" autofocus required></div>
    <div class="form-row"><label>密码</label><input class="inp" type="password" name="password" required></div>
    <button class="btn btn-primary" style="width:100%;justify-content:center;padding:12px" type="submit">登 录</button>
  </form>
</div></body></html>
