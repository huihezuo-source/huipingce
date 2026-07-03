<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

$id = $_GET['id'] ?? '';
$st = db()->prepare('SELECT * FROM regulators WHERE id=? LIMIT 1');
$st->execute([$id]);
$r = $st->fetch();
if(!$r){ http_response_code(404); header_html('regulators'); echo '<div class="empty"><div class="ico">🚫</div><p>监管机构不存在</p><a class="btn" href="/regulators.php">返回</a></div>'; footer_html(); exit; }

// 受监管实体（分页）
$page = max(1,(int)($_GET['page']??1)); $per=30; $off=($page-1)*$per;
$cnt = db()->prepare('SELECT COUNT(*) FROM reg_entities WHERE regulator_id=?'); $cnt->execute([$id]);
$total=(int)$cnt->fetchColumn(); $pages=max(1,(int)ceil($total/$per));
$est = db()->prepare("SELECT * FROM reg_entities WHERE regulator_id=? ORDER BY status ASC, name ASC LIMIT $per OFFSET $off");
$est->execute([$id]); $ents=$est->fetchAll();

header_html('regulators', [
    'title'=>$r['name'].' '.$r['full_name'].' 监管查询',
    'desc'=>$r['name'].'（'.$r['country'].'）监管信任评级、牌照查询入口与受监管实体名录，共 '.$total.' 家。'
]);
?>
<div class="crumb"><a href="/">首页</a> › <a href="/regulators.php">监管机构</a> › <b><?=h($r['name'])?></b></div>

<div class="card bd-head">
  <div class="bd-logo ph" style="font-size:40px"><?=h($r['flag']?:'🛡️')?></div>
  <div class="bd-head-main">
    <h1><?=h($r['name'])?> <span class="grade <?=grade_class($r['grade'])?>" style="height:26px;font-size:14px"><?=h($r['grade'])?></span></h1>
    <p class="bd-summary"><?=h($r['full_name'])?></p>
    <div class="bd-facts">
      <span>国家/地区 <b><?=h($r['country'])?></b></span>
      <span>类型 <b><?=h($r['gov_type']?:'—')?></b></span>
      <?php if($r['established']): ?><span>设立 <b><?=h($r['established'])?></b></span><?php endif; ?>
      <span>评级 <b><?=grade_label($r['grade'])?></b></span>
      <?php if($r['query_url']): ?><span><a href="<?=h($r['query_url'])?>" target="_blank" rel="nofollow noopener">官方牌照查询 ↗</a></span><?php endif; ?>
    </div>
    <?php if($r['description']): ?><p class="sub" style="margin-top:12px"><?=nl2br(h($r['description']))?></p><?php endif; ?>
  </div>
  <div class="bd-rating" style="width:150px">
    <div class="bd-rating-left" style="width:100%">
      <div class="rating-num great"><?=(int)$r['trust_score']?></div>
      <div class="cnt">信任评分 / 100</div>
      <div class="muted" style="font-size:12px;margin-top:6px"><?=$total?>家受监管实体</div>
    </div>
  </div>
</div>

<div class="panel card">
  <div class="panel-hd">受监管实体名录 <span class="cnt">共 <?=$total?> 家</span></div>
  <?php if(!$ents): ?>
    <div class="empty" style="padding:30px"><p>暂未采集该机构的受监管实体，可在后台运行采集脚本补全</p></div>
  <?php else: ?>
    <table class="ent-table">
      <thead><tr><th>持牌实体</th><th>牌照/注册号</th><th>类型</th><th>客户类型</th><th>状态</th></tr></thead>
      <tbody>
      <?php foreach($ents as $e): [$sl,$sc]=entity_status_label($e['status']); ?>
        <tr>
          <td><b><?=h($e['name'])?></b><?php if($e['city']||$e['country']): ?><br><span class="muted" style="font-size:12px"><?=h(trim($e['city'].' '.$e['country']))?></span><?php endif; ?></td>
          <td><?=h($e['license_no']?:'—')?></td>
          <td><?=h($e['license_type']?:'—')?></td>
          <td><?=h($e['client_type']?:'—')?></td>
          <td><span class="pill <?=$sc?>"><?=$sl?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php if($pages>1): ?>
<div class="pager">
  <?php for($i=max(1,$page-3);$i<=min($pages,$page+3);$i++): ?>
    <?php if($i==$page): ?><span class="cur"><?=$i?></span><?php else: ?><a href="/regulator-detail.php?id=<?=h($id)?>&page=<?=$i?>"><?=$i?></a><?php endif; ?>
  <?php endfor; ?>
</div>
<?php endif; ?>

<?php footer_html(); ?>
