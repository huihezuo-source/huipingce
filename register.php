<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

$next = safe_next($_GET['next'] ?? ($_POST['next'] ?? '/'), '/');
if (current_user()) { header('Location: '.$next); exit; }

$err='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (($_POST['password']??'') !== ($_POST['password2']??'')) {
        $err = '两次输入的密码不一致';
    } else {
        [$ok,$res] = user_register($_POST['username']??'', $_POST['password']??'', $_POST['email']??'', $_POST['nickname']??'');
        if ($ok) {
            user_login($_POST['username'], $_POST['password']);
            header('Location: '.$next); exit;
        }
        $err = $res;
    }
}

header_html('', ['title'=>'注册']);
?>
<div class="form-wrap">
  <div class="card form-card">
    <h1>注册汇评测</h1>
    <p class="sub">加入交易者社区，分享你的真实交易商体验</p>
    <?php if($err): ?><div class="form-msg err"><?=h($err)?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="next" value="<?=h($next)?>">
      <div class="field"><label>用户名 <span class="req">*</span></label><input type="text" name="username" value="<?=h($_POST['username']??'')?>" autofocus><div class="hint">2-20 位字母/数字/下划线/中文，登录用</div></div>
      <div class="field"><label>昵称</label><input type="text" name="nickname" value="<?=h($_POST['nickname']??'')?>" placeholder="展示名，留空默认用用户名"></div>
      <div class="field"><label>邮箱（选填）</label><input type="email" name="email" value="<?=h($_POST['email']??'')?>"></div>
      <div class="field"><label>密码 <span class="req">*</span></label><input type="password" name="password"><div class="hint">至少 6 位</div></div>
      <div class="field"><label>确认密码 <span class="req">*</span></label><input type="password" name="password2"></div>
      <button class="btn btn-primary btn-lg btn-block" type="submit">注册并登录</button>
    </form>
    <div class="form-foot">已有账号？<a href="/login.php?next=<?=urlencode($next)?>">去登录</a></div>
  </div>
</div>
<?php footer_html(); ?>
