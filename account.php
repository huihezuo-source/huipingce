<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

$me = require_user('/account.php');
$tab = $_GET['tab'] ?? 'reviews';

// 更新资料
$saved=false;
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['act']??'')==='profile') {
    $nick=trim($_POST['nickname']??''); $bio=mb_substr(trim($_POST['bio']??''),0,200);
    db()->prepare('UPDATE site_users SET nickname=?, bio=? WHERE id=?')->execute([$nick?:$me['username'],$bio,$me['id']]);
    $me['nickname']=$nick?:$me['username']; $me['bio']=$bio; $saved=true;
}

// 我的测评
$myRev = db()->prepare("SELECT r.*, b.name bname, b.logo blogo FROM broker_reviews r LEFT JOIN brokers b ON r.broker_id=b.id
    WHERE r.user_id=? ORDER BY r.created_at DESC");
$myRev->execute([$me['id']]); $myRev=$myRev->fetchAll();

// 我的曝光
$myExp = db()->prepare("SELECT * FROM complaints WHERE user_id=? ORDER BY created_at DESC");
$myExp->execute([$me['id']]); $myExp=$myExp->fetchAll();

header_html('', ['title'=>'个人中心']);
?>
<div class="card acc-head">
  <?=user_avatar_html($me,64)?>
  <div style="flex:1">
    <div class="aname"><?=h(user_display_name($me))?></div>
    <div class="abio"><?=h($me['bio']?:'这个人很懒，什么都没写~')?></div>
  </div>
  <div style="text-align:center">
    <div style="font-size:22px;font-weight:800;font-family:Georgia,serif;color:var(--green-d)"><?=count($myRev)?></div>
    <div class="muted" style="font-size:12px">测评</div>
  </div>
</div>

<div class="acc-tabs">
  <a class="<?=$tab==='reviews'?'on':''?>" href="/account.php?tab=reviews">我的测评 (<?=count($myRev)?>)</a>
  <a class="<?=$tab==='exposures'?'on':''?>" href="/account.php?tab=exposures">我的曝光 (<?=count($myExp)?>)</a>
  <a class="<?=$tab==='profile'?'on':''?>" href="/account.php?tab=profile">资料设置</a>
</div>

<?php if($tab==='reviews'): ?>
  <div class="card" style="padding:6px 22px">
    <?php if(!$myRev): ?><div class="empty"><div class="ico">💬</div><p>还没有测评，去<a href="/brokers.php">给交易商打分</a></p></div>
    <?php else: foreach($myRev as $r): ?>
      <div class="review-item">
        <?=logo_html($r['blogo'],$r['bname'],'rank-logo')?>
        <div class="review-main">
          <div class="review-hd">
            <a class="uname" href="/broker-detail.php?id=<?=h($r['broker_id'])?>"><?=h($r['bname']?:'（已下架）')?></a>
            <?=stars_html($r['stars'])?><span class="badge badge-amber"><?=star_word($r['stars'])?></span>
            <?php if($r['status']==='pending'): ?><span class="badge badge-gray">审核中</span><?php elseif($r['status']==='rejected'): ?><span class="badge badge-red">未通过</span><?php endif; ?>
            <span class="rtime"><?=time_ago($r['created_at'])?></span>
          </div>
          <?php if($r['content']): ?><div class="review-body"><?=nl2br(h($r['content']))?></div><?php endif; ?>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>

<?php elseif($tab==='exposures'): ?>
  <div class="card">
    <?php if(!$myExp): ?><div class="empty"><div class="ico">⚠️</div><p>还没有曝光记录，<a href="/exposure-submit.php">我要曝光</a></p></div>
    <?php else: foreach($myExp as $c): [$sl,$sc]=complaint_status_label($c['status']); ?>
      <div class="exp-item">
        <div class="exp-hd">
          <span class="badge badge-red"><?=h($c['type'])?></span>
          <h3><a href="/exposure-detail.php?id=<?=h($c['id'])?>"><?=h($c['title'])?></a></h3>
          <span class="pill <?=$sc?>"><?=$sl?></span>
        </div>
        <div class="exp-meta"><span><?=h($c['broker_name']?:'—')?></span><span><?=time_ago($c['created_at'])?></span></div>
      </div>
    <?php endforeach; endif; ?>
  </div>

<?php else: ?>
  <div class="card form-card" style="max-width:520px">
    <?php if($saved): ?><div class="form-msg ok">✅ 资料已保存</div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="act" value="profile">
      <div class="field"><label>用户名</label><input type="text" value="<?=h($me['username'])?>" disabled><div class="hint">用户名不可修改</div></div>
      <div class="field"><label>昵称</label><input type="text" name="nickname" value="<?=h($me['nickname']?:'')?>"></div>
      <div class="field"><label>简介</label><textarea name="bio" style="min-height:80px" maxlength="200"><?=h($me['bio']?:'')?></textarea></div>
      <button class="btn btn-primary btn-block" type="submit">保存</button>
    </form>
  </div>
<?php endif; ?>

<?php footer_html(); ?>
