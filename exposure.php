<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

$type = trim($_GET['type'] ?? '');
$types = complaint_types();
$page = max(1,(int)($_GET['page']??1)); $per=15; $off=($page-1)*$per;

$where="c.status<>'rejected'"; $p=[];
if ($type!=='' && in_array($type,$types,true)){ $where.=' AND c.type=?'; $p[]=$type; }

$cnt=db()->prepare("SELECT COUNT(*) FROM complaints c WHERE $where"); $cnt->execute($p);
$total=(int)$cnt->fetchColumn(); $pages=max(1,(int)ceil($total/$per));
$page=min($page,$pages); $off=($page-1)*$per;

$st=db()->prepare("SELECT c.*, b.name bname FROM complaints c LEFT JOIN brokers b ON c.broker_id=b.id
    WHERE $where ORDER BY c.created_at DESC LIMIT $per OFFSET $off");
$st->execute($p); $rows=$st->fetchAll();

// 统计
$sumAll  = (int)db()->query("SELECT COUNT(*) FROM complaints WHERE status<>'rejected'")->fetchColumn();
$sumDone = (int)db()->query("SELECT COUNT(*) FROM complaints WHERE status='resolved'")->fetchColumn();
$sumAmt  = (float)db()->query("SELECT COALESCE(SUM(resolved_amount),0) FROM complaints WHERE status='resolved'")->fetchColumn();

header_html('exposure', [
    'title'=>'外汇曝光维权台',
    'desc'=>'外汇交易商无法出金、滑点欺诈、诱导交易等真实曝光案例，交易者维权互助。'
]);
?>
<div class="crumb"><a href="/">首页</a> › <b>曝光</b></div>

<div class="section-hd"><h2>⚠️ 曝光维权台</h2><a class="btn btn-red" href="/exposure-submit.php">我要曝光</a></div>

<div class="exp-stats">
  <div class="card exp-stat"><b><?=number_format($sumAll)?></b><span>累计曝光</span></div>
  <div class="card exp-stat ok"><b><?=number_format($sumDone)?></b><span>已协助解决</span></div>
  <div class="card exp-stat ok"><b>$<?=number_format($sumAmt)?></b><span>已解决金额</span></div>
</div>

<div class="card filters" style="margin-bottom:18px">
  <div class="fgroup">
    <span class="flabel">类型</span>
    <a class="chip<?=$type===''?' on':''?>" href="/exposure.php">全部</a>
    <?php foreach($types as $t): ?>
      <a class="chip<?=$type===$t?' on':''?>" href="/exposure.php?type=<?=urlencode($t)?>"><?=$t?></a>
    <?php endforeach; ?>
  </div>
</div>

<div class="card">
  <?php if(!$rows): ?>
    <div class="empty"><div class="ico">🕊️</div><p>暂无曝光记录</p></div>
  <?php else: foreach($rows as $c): [$sl,$sc]=complaint_status_label($c['status']); ?>
    <div class="exp-item">
      <div class="exp-hd">
        <span class="badge badge-red"><?=h($c['type'])?></span>
        <h3><a href="/exposure-detail.php?id=<?=h($c['id'])?>"><?=h($c['title'])?></a></h3>
        <span class="pill <?=$sc?>"><?=$sl?></span>
      </div>
      <?php if($c['content']): ?><p class="exp-sum"><?=h(mb_strimwidth($c['content'],0,120,'…','UTF-8'))?></p><?php endif; ?>
      <div class="exp-meta">
        <?php if($c['bname']): ?><span>涉及 <a href="/broker-detail.php?id=<?=h($c['broker_id'])?>"><?=h($c['bname'])?></a></span>
        <?php elseif($c['broker_name']): ?><span>涉及 <?=h($c['broker_name'])?></span><?php endif; ?>
        <?php if($c['loss_amount']>0): ?><span class="exp-amount">涉及金额 $<?=number_format($c['loss_amount'])?></span><?php endif; ?>
        <span><?=h($c['nickname'])?></span>
        <span><?=time_ago($c['created_at'])?></span>
      </div>
    </div>
  <?php endforeach; endif; ?>
</div>

<?php if($pages>1): $qs=function($pp)use($type){return '/exposure.php?'.($type?'type='.urlencode($type).'&':'').'page='.$pp;}; ?>
<div class="pager">
  <?php for($i=max(1,$page-3);$i<=min($pages,$page+3);$i++): ?>
    <?php if($i==$page): ?><span class="cur"><?=$i?></span><?php else: ?><a href="<?=$qs($i)?>"><?=$i?></a><?php endif; ?>
  <?php endfor; ?>
</div>
<?php endif; ?>

<?php footer_html(); ?>
