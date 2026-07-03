<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

$next = safe_next($_GET['next'] ?? ($_POST['next'] ?? '/'), '/');
if (current_user()) { header('Location: '.$next); exit; }

$err='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    [$ok,$res] = user_login($_POST['username']??'', $_POST['password']??'');
    if ($ok) { header('Location: '.$next); exit; }
    $err = $res;
}

header_html('', ['title'=>'登录']);
?>
<div class="form-wrap">
  <div class="card form-card">
    <h1>登录汇评测</h1>
    <p class="sub">登录后可给交易商打分、写测评、曝光维权</p>
    <?php if($err): ?><div class="form-msg err"><?=h($err)?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="next" value="<?=h($next)?>">
      <div class="field"><label>用户名</label><input type="text" name="username" value="<?=h($_POST['username']??'')?>" autofocus></div>
      <div class="field"><label>密码</label><input type="password" name="password"></div>
      <button class="btn btn-primary btn-lg btn-block" type="submit">登录</button>
    </form>
    <div class="form-foot">还没有账号？<a href="/register.php?next=<?=urlencode($next)?>">立即注册</a></div>
  </div>
</div>
<?php footer_html(); ?>
