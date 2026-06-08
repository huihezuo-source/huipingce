<?php
require_once __DIR__ . '/layout.php';
admin_head('reviews','评测管理');

$q = trim($_GET['q'] ?? '');
$page = max(1,(int)($_GET['page'] ?? 1));
$cond='1=1'; $args=[];
if ($q!==''){ $cond.=' AND r.title LIKE ?'; $args[]="%$q%"; }
$total=(int)(function() use($cond,$args){ $s=db()->prepare("SELECT COUNT(*) FROM reviews r WHERE $cond"); $s->execute($args); return $s->fetchColumn(); })();
$pg=admin_paginate($page,$total,20);
$st=db()->prepare("SELECT r.*,b.name AS bn FROM reviews r LEFT JOIN brokers b ON r.broker_id=b.id WHERE $cond ORDER BY r.created_at DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}");
$st->execute($args); $rows=$st->fetchAll();
$bmap=['published'=>'b-pub','draft'=>'b-draft','scheduled'=>'b-sch'];
$blbl=['published'=>'已发布','draft'=>'草稿','scheduled'=>'定时'];
?>
<?php admin_topbar('评测管理','<a class="btn btn-primary" href="/admin/review_edit.php">+ 新建评测</a>'); ?>
<?php if(isset($_GET['saved'])): ?><div class="flash flash-ok">✅ 评测已保存</div><?php endif; ?>
<form class="toolbar" method="get">
  <input class="search" name="q" value="<?= h($q) ?>" placeholder="搜索评测标题…">
  <button class="btn btn-ghost btn-sm">搜索</button>
</form>
<?php if($rows): ?>
<table>
  <thead><tr><th>标题</th><th>对象</th><th>总分</th><th>状态</th><th>发布时间</th><th>浏览</th><th></th></tr></thead>
  <tbody>
  <?php foreach($rows as $r): ?>
    <tr>
      <td style="font-weight:700;max-width:320px"><?= h($r['title']) ?></td>
      <td class="muted"><?= h($r['bn'] ?: '综合') ?></td>
      <td style="font-weight:850"><?= $r['overall_score']!==null?h($r['overall_score']):'—' ?></td>
      <td><span class="badge <?= $bmap[$r['status']] ?>"><?= $blbl[$r['status']] ?></span></td>
      <td class="muted"><?= $r['publish_at']?date('Y-m-d H:i',$r['publish_at']):'—' ?></td>
      <td class="muted"><?= (int)$r['views'] ?></td>
      <td style="white-space:nowrap">
        <a class="btn btn-ghost btn-sm" href="/admin/review_edit.php?id=<?= h($r['id']) ?>">编辑</a>
        <a class="btn btn-ghost btn-sm" href="/review-detail.php?id=<?= h($r['id']) ?>" target="_blank">预览</a>
        <button class="btn btn-ghost btn-sm btn-danger" onclick="admDelete('/admin/api/review_delete.php','<?= h($r['id']) ?>','<?= h(addslashes($r['title'])) ?>')">删除</button>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?= admin_pager_html($pg,'/admin/reviews.php?'.($q?'q='.urlencode($q).'&':'')) ?>
<?php else: ?>
  <div class="card" style="text-align:center;padding:48px"><p class="muted">还没有评测</p><p style="margin-top:12px"><a class="btn btn-primary" href="/admin/review_edit.php">+ 新建第一篇评测</a></p></div>
<?php endif; ?>
<?php admin_foot();