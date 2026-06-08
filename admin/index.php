<?php
require_once __DIR__ . '/layout.php';
admin_head('index','概览');

$c = [
  'reviews' => (int)db()->query('SELECT COUNT(*) FROM reviews')->fetchColumn(),
  'pub'     => (int)db()->query("SELECT COUNT(*) FROM reviews WHERE status='published'")->fetchColumn(),
  'brokers' => (int)db()->query('SELECT COUNT(*) FROM brokers')->fetchColumn(),
  'entities'=> (int)db()->query('SELECT COUNT(*) FROM reg_entities')->fetchColumn(),
  'regs'    => (int)db()->query('SELECT COUNT(*) FROM regulators')->fetchColumn(),
];
$recent = db()->query("SELECT r.*,b.name AS bn FROM reviews r LEFT JOIN brokers b ON r.broker_id=b.id ORDER BY r.created_at DESC LIMIT 6")->fetchAll();
?>
<?php admin_topbar('概览','<a class="btn btn-primary" href="/admin/review_edit.php">+ 新建评测</a>'); ?>
<div class="stat-grid">
  <div class="stat"><div class="v"><?= $c['pub'] ?></div><div class="l">已发布评测</div></div>
  <div class="stat"><div class="v"><?= $c['brokers'] ?></div><div class="l">经纪商品牌</div></div>
  <div class="stat"><div class="v"><?= $c['entities'] ?></div><div class="l">受监管实体</div></div>
  <div class="stat"><div class="v"><?= $c['regs'] ?></div><div class="l">监管机构</div></div>
</div>
<div class="card">
  <div class="adm-top" style="margin-bottom:14px"><h1 style="font-size:17px">最近评测</h1><a class="btn btn-ghost btn-sm" href="/admin/reviews.php">全部 →</a></div>
  <?php if($recent): ?>
  <table>
    <thead><tr><th>标题</th><th>对象</th><th>总分</th><th>状态</th><th></th></tr></thead>
    <tbody>
    <?php foreach($recent as $r):
      $bmap=['published'=>'b-pub','draft'=>'b-draft','scheduled'=>'b-sch'];
      $blbl=['published'=>'已发布','draft'=>'草稿','scheduled'=>'定时']; ?>
      <tr>
        <td style="font-weight:700"><?= h(mb_substr($r['title'],0,32)) ?></td>
        <td class="muted"><?= h($r['bn'] ?: '综合') ?></td>
        <td style="font-weight:850"><?= $r['overall_score']!==null?h($r['overall_score']):'—' ?></td>
        <td><span class="badge <?= $bmap[$r['status']] ?>"><?= $blbl[$r['status']] ?></span></td>
        <td><a class="btn btn-ghost btn-sm" href="/admin/review_edit.php?id=<?= h($r['id']) ?>">编辑</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?><p class="muted">还没有评测，<a href="/admin/review_edit.php" style="color:var(--brand);font-weight:700">立即新建</a></p><?php endif; ?>
</div>
<?php admin_foot();