<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

$id=$_GET['id']??'';
$st=db()->prepare("SELECT c.*, b.name bname, b.logo blogo FROM complaints c LEFT JOIN brokers b ON c.broker_id=b.id WHERE c.id=? LIMIT 1");
$st->execute([$id]); $c=$st->fetch();
if(!$c || $c['status']==='rejected'){ http_response_code(404); header_html('exposure'); echo '<div class="empty"><div class="ico">🚫</div><p>曝光不存在或未通过审核</p><a class="btn" href="/exposure.php">返回曝光台</a></div>'; footer_html(); exit; }

db()->prepare('UPDATE complaints SET views=views+1 WHERE id=?')->execute([$id]);
[$sl,$sc]=complaint_status_label($c['status']);
$evi = array_values(array_filter(array_map('trim', preg_split('/\r?\n/',(string)$c['evidence']))));

header_html('exposure', [
    'title'=>$c['title'].' · 曝光',
    'desc'=>mb_strimwidth((string)$c['content'],0,120,'…','UTF-8'),
]);
?>
<div class="crumb"><a href="/">首页</a> › <a href="/exposure.php">曝光</a> › <b>详情</b></div>

<div class="layout">
  <article class="card panel">
    <div class="exp-hd" style="margin-bottom:14px">
      <span class="badge badge-red"><?=h($c['type'])?></span>
      <h1 style="font-size:23px"><?=h($c['title'])?></h1>
      <span class="pill <?=$sc?>"><?=$sl?></span>
    </div>
    <div class="exp-meta" style="margin-bottom:18px;padding-bottom:16px;border-bottom:1px solid var(--line)">
      <span>曝光人 <?=h($c['nickname'])?></span>
      <span><?=date('Y-m-d H:i',$c['created_at'])?></span>
      <?php if($c['loss_amount']>0): ?><span class="exp-amount">涉及金额 $<?=number_format($c['loss_amount'])?></span><?php endif; ?>
      <span><?=num_short($c['views']+1)?> 阅读</span>
    </div>

    <div class="art-body" style="font-size:15px"><?=nl2br(h($c['content']))?></div>

    <?php if($evi): ?>
    <div style="margin-top:20px">
      <h4 style="font-size:14px;color:var(--muted);margin-bottom:8px">证据材料</h4>
      <?php foreach($evi as $u): ?>
        <?php if(preg_match('/\.(png|jpe?g|gif|webp)$/i',$u)): ?>
          <a href="<?=h($u)?>" target="_blank"><img src="<?=h($u)?>" style="max-width:220px;border-radius:8px;margin:0 8px 8px 0;border:1px solid var(--line)"></a>
        <?php else: ?>
          <div><a href="<?=h($u)?>" target="_blank" rel="nofollow noopener">🔗 <?=h($u)?></a></div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if($c['admin_reply']): ?>
    <div class="rate-box" style="margin-top:22px;background:#eaf1fb;border-color:#cdddf5;flex-direction:column;align-items:flex-start">
      <b style="color:var(--blue)">平台/交易商回应</b>
      <div style="margin-top:6px"><?=nl2br(h($c['admin_reply']))?></div>
      <?php if($c['status']==='resolved' && $c['resolved_amount']>0): ?>
        <div class="badge badge-green" style="margin-top:8px">已解决金额 $<?=number_format($c['resolved_amount'])?></div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </article>

  <aside>
    <?php if($c['broker_id']): ?>
    <div class="card side-card">
      <h3>涉及交易商</h3>
      <a class="rank-item" style="padding:6px 0" href="/broker-detail.php?id=<?=h($c['broker_id'])?>">
        <?=logo_html($c['blogo'],$c['bname'],'rank-logo')?>
        <div class="rank-main"><div class="rank-name"><?=h($c['bname'])?></div><div class="rank-meta">查看监管与评分 ›</div></div>
      </a>
    </div>
    <?php elseif($c['broker_name']): ?>
    <div class="card side-card"><h3>涉及交易商</h3><p><?=h($c['broker_name'])?><br><span class="muted" style="font-size:12px">（本站暂未收录）</span></p></div>
    <?php endif; ?>
    <div class="card side-card">
      <h3>我也要曝光</h3>
      <p class="muted" style="font-size:13px;margin-bottom:12px">遭遇无法出金、恶意滑点或诱导欺诈？发布曝光，帮助更多交易者避坑。</p>
      <a class="btn btn-red btn-block" href="/exposure-submit.php">发布曝光</a>
    </div>
  </aside>
</div>

<?php footer_html(); ?>
