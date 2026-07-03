<?php
require_once __DIR__ . '/layout.php';
admin_head('brokers','经纪商管理');

$q = trim($_GET['q'] ?? '');
$page = max(1,(int)($_GET['page'] ?? 1));
$cond='1=1'; $args=[];
if ($q!==''){ $cond.=' AND (name LIKE ? OR name_en LIKE ?)'; $args[]="%$q%"; $args[]="%$q%"; }
$total=(int)(function() use($cond,$args){ $s=db()->prepare("SELECT COUNT(*) FROM brokers WHERE $cond"); $s->execute($args); return $s->fetchColumn(); })();
$pg=admin_paginate($page,$total,20);
$st=db()->prepare("SELECT * FROM brokers WHERE $cond ORDER BY featured DESC, score DESC, name LIMIT {$pg['per']} OFFSET {$pg['offset']}");
$st->execute($args); $rows=$st->fetchAll();
?>
<?php admin_topbar('经纪商管理','<a class="btn btn-primary" href="/admin/broker_edit.php">+ 新建经纪商</a>'); ?>
<?php if(isset($_GET['saved'])): ?><div class="flash flash-ok">✅ 已保存</div><?php endif; ?>
<form class="toolbar" method="get">
  <input class="search" name="q" value="<?= h($q) ?>" placeholder="搜索品牌名…">
  <button class="btn btn-ghost btn-sm">搜索</button>
</form>
<?php if($rows): ?>
<table>
  <thead><tr><th>品牌</th><th>关联实体</th><th>评分</th><th>杠杆</th><th>精选</th><th></th></tr></thead>
  <tbody>
  <?php foreach($rows as $b):
    $ec=(int)(function() use($b){ $s=db()->prepare('SELECT COUNT(*) FROM broker_entity_map WHERE broker_id=?'); $s->execute([$b['id']]); return $s->fetchColumn(); })(); ?>
    <tr>
      <td style="font-weight:700"><?= h($b['name']) ?><?php if($b['name_en']&&$b['name_en']!==$b['name']): ?><div class="muted"><?= h($b['name_en']) ?></div><?php endif; ?></td>
      <td class="muted"><?= $ec ?> 个</td>
      <td style="font-weight:850"><?= $b['score']!==null?h($b['score']):'—' ?></td>
      <td class="muted"><?= h($b['leverage']) ?></td>
      <td><?= $b['featured']?'⭐':'' ?></td>
      <td style="white-space:nowrap">
        <a class="btn btn-ghost btn-sm" href="/admin/broker_edit.php?id=<?= h($b['id']) ?>">编辑</a>
        <a class="btn btn-ghost btn-sm" href="/broker-detail.php?id=<?= h($b['id']) ?>" target="_blank">预览</a>
        <button class="btn btn-ghost btn-sm btn-danger" onclick="admDelete('/admin/api/broker_delete.php','<?= h($b['id']) ?>','<?= h(addslashes($b['name'])) ?>')">删除</button>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?= admin_pager_html($pg,'/admin/brokers.php?'.($q?'q='.urlencode($q).'&':'')) ?>
<?php else: ?>
  <div class="card" style="text-align:center;padding:48px"><p class="muted">还没有经纪商。可由采集器入库实体后聚合，或手动新建。</p></div>
<?php endif; ?>
<?php admin_foot();