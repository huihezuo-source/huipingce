<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

$me = require_user('/exposure-submit.php');
$types = complaint_types();

// 预填交易商
$presetBroker = null;
if (!empty($_GET['broker'])) {
    $q=db()->prepare('SELECT id,name FROM brokers WHERE id=? LIMIT 1'); $q->execute([$_GET['broker']]);
    $presetBroker=$q->fetch()?:null;
}

$err=''; $ok=false;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $title=trim($_POST['title']??'');
    $type=$_POST['type']??'其他';
    $content=trim($_POST['content']??'');
    $bname=trim($_POST['broker_name']??'');
    $bid=trim($_POST['broker_id']??'');
    $loss=($_POST['loss_amount']??'')!=='' ? (float)$_POST['loss_amount'] : null;
    $contact=trim($_POST['contact']??'');
    $evidence=trim($_POST['evidence']??'');

    if(mb_strlen($title)<5) $err='标题至少 5 个字';
    elseif(!in_array($type,$types,true)) $err='请选择曝光类型';
    elseif(mb_strlen($content)<20) $err='请详细描述经过（至少 20 字）';
    else {
        // 关联交易商：优先 broker_id，其次按名精确匹配
        if($bid){ $q=db()->prepare('SELECT id,name FROM brokers WHERE id=? LIMIT 1'); $q->execute([$bid]); $row=$q->fetch(); if($row){ $bname=$row['name']; } else { $bid=''; } }
        if(!$bid && $bname!==''){ $q=db()->prepare('SELECT id FROM brokers WHERE name=? LIMIT 1'); $q->execute([$bname]); $r=$q->fetch(); if($r) $bid=$r['id']; }

        $cid='cp_'.gen_id();
        db()->prepare("INSERT INTO complaints
            (id,broker_id,broker_name,user_id,nickname,type,title,content,loss_amount,evidence,contact,status,ip,created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?, 'pending', ?, ?)")
          ->execute([$cid, $bid?:null, $bname, $me['id'], user_display_name($me), $type, $title, $content, $loss, $evidence, $contact, client_ip(), now_ts()]);
        if($bid) recompute_broker_complaints($bid);
        $ok=true;
    }
}

header_html('exposure', ['title'=>'我要曝光']);
?>
<div class="crumb"><a href="/">首页</a> › <a href="/exposure.php">曝光</a> › <b>我要曝光</b></div>

<div class="form-wrap form-wide">
  <div class="card form-card">
    <h1>发布曝光</h1>
    <p class="sub">如实描述你遭遇的问题，平台核实后公开展示。恶意捏造将被封禁并承担法律责任。</p>

    <?php if($ok): ?>
      <div class="form-msg ok">✅ 曝光已提交，平台将尽快核实后公开。你可以在<a href="/account.php">个人中心</a>查看进度。</div>
      <a class="btn btn-primary" href="/exposure.php">返回曝光台</a>
    <?php else: ?>
      <?php if($err): ?><div class="form-msg err"><?=h($err)?></div><?php endif; ?>
      <form method="post">
        <div class="field">
          <label>涉及交易商 <span class="hint">（本站已收录会自动关联；未收录可直接填名）</span></label>
          <input type="hidden" name="broker_id" value="<?=h($presetBroker['id']??'')?>">
          <input type="text" name="broker_name" value="<?=h($presetBroker['name']??($_POST['broker_name']??''))?>" placeholder="如 IC Markets / 某某平台">
        </div>
        <div class="field">
          <label>曝光类型 <span class="req">*</span></label>
          <select name="type">
            <?php foreach($types as $t): ?><option<?=($_POST['type']??'')===$t?' selected':''?>><?=$t?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>标题 <span class="req">*</span></label>
          <input type="text" name="title" value="<?=h($_POST['title']??'')?>" placeholder="一句话概括，如：出金申请 30 天未到账">
        </div>
        <div class="field">
          <label>详细经过 <span class="req">*</span></label>
          <textarea name="content" placeholder="时间、账户、金额、沟通经过、平台反馈……越详细越有说服力"><?=h($_POST['content']??'')?></textarea>
        </div>
        <div class="field">
          <label>涉及金额（USD，选填）</label>
          <input type="number" name="loss_amount" step="0.01" min="0" value="<?=h($_POST['loss_amount']??'')?>" placeholder="如 5000">
        </div>
        <div class="field">
          <label>证据链接（选填）</label>
          <textarea name="evidence" style="min-height:70px" placeholder="截图/聊天记录/交易单图片链接，每行一条"><?=h($_POST['evidence']??'')?></textarea>
        </div>
        <div class="field">
          <label>联系方式（选填，仅平台可见）</label>
          <input type="text" name="contact" value="<?=h($_POST['contact']??'')?>" placeholder="微信/邮箱，便于核实与协助维权">
        </div>
        <button class="btn btn-red btn-lg btn-block" type="submit">提交曝光</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php footer_html(); ?>
