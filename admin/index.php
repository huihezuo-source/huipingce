<?php
require_once __DIR__ . '/layout.php';
admin_head('index','概览');

$c = [
  'brokers'  => (int)db()->query('SELECT COUNT(*) FROM brokers')->fetchColumn(),
  'entities' => (int)db()->query('SELECT COUNT(*) FROM reg_entities')->fetchColumn(),
  'regs'     => (int)db()->query('SELECT COUNT(*) FROM regulators')->fetchColumn(),
  'articles' => (int)db()->query("SELECT COUNT(*) FROM articles WHERE status='published'")->fetchColumn(),
  'reviews'  => (int)db()->query("SELECT COUNT(*) FROM broker_reviews WHERE status='approved'")->fetchColumn(),
  'pending'  => (int)db()->query("SELECT COUNT(*) FROM broker_reviews WHERE status='pending'")->fetchColumn(),
  'users'    => (int)db()->query('SELECT COUNT(*) FROM site_users')->fetchColumn(),
  'cpending' => (int)db()->query("SELECT COUNT(*) FROM complaints WHERE status='pending'")->fetchColumn(),
];
$recentC = db()->query("SELECT c.*, b.name bn FROM complaints c LEFT JOIN brokers b ON c.broker_id=b.id ORDER BY c.created_at DESC LIMIT 6")->fetchAll();
$recentR = db()->query("SELECT r.*, b.name bn, u.nickname FROM broker_reviews r LEFT JOIN brokers b ON r.broker_id=b.id LEFT JOIN site_users u ON r.user_id=u.id ORDER BY r.created_at DESC LIMIT 6")->fetchAll();
?>
<?php admin_topbar('概览'); ?>
<div class="stat-grid">
  <div class="stat"><div class="v"><?= $c['brokers'] ?></div><div class="l">交易商</div></div>
  <div class="stat"><div class="v"><?= $c['entities'] ?></div><div class="l">受监管实体</div></div>
  <div class="stat"><div class="v"><?= $c['regs'] ?></div><div class="l">监管机构</div></div>
  <div class="stat"><div class="v"><?= $c['articles'] ?></div><div class="l">已发布资讯</div></div>
  <div class="stat"><div class="v"><?= $c['reviews'] ?></div><div class="l">用户测评</div></div>
  <div class="stat"><div class="v"><?= $c['users'] ?></div><div class="l">注册会员</div></div>
</div>

<?php if($c['pending']>0 || $c['cpending']>0): ?>
<div class="card" style="border-left:4px solid #f0a500">
  <b>待处理：</b>
  <?php if($c['pending']>0): ?><a href="/admin/reviews.php?status=pending" style="margin-right:16px">⭐ <?= $c['pending'] ?> 条测评待审</a><?php endif; ?>
  <?php if($c['cpending']>0): ?><a href="/admin/complaints.php?status=pending">⚠️ <?= $c['cpending'] ?> 条曝光待核实</a><?php endif; ?>
</div>
<?php endif; ?>

<div class="card">
  <div class="adm-top" style="margin-bottom:14px"><h1 style="font-size:17px">最新曝光</h1><a class="btn btn-ghost btn-sm" href="/admin/complaints.php">全部 →</a></div>
  <?php if($recentC): ?>
  <table>
    <thead><tr><th>标题</th><th>类型</th><th>涉及</th><th>状态</th><th></th></tr></thead>
    <tbody>
    <?php foreach($recentC as $r): [$sl,$sc]=complaint_status_label($r['status']); ?>
      <tr>
        <td style="font-weight:700"><?= h(mb_strimwidth($r['title'],0,30,'…','UTF-8')) ?></td>
        <td><?= h($r['type']) ?></td>
        <td class="muted"><?= h($r['bn'] ?: $r['broker_name'] ?: '—') ?></td>
        <td><?= $sl ?></td>
        <td><a class="btn btn-ghost btn-sm" href="/admin/complaints.php?id=<?= h($r['id']) ?>">处理</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?><p class="muted">暂无曝光</p><?php endif; ?>
</div>

<div class="card">
  <div class="adm-top" style="margin-bottom:14px"><h1 style="font-size:17px">最新测评</h1><a class="btn btn-ghost btn-sm" href="/admin/reviews.php">全部 →</a></div>
  <?php if($recentR): ?>
  <table>
    <thead><tr><th>会员</th><th>交易商</th><th>评分</th><th>内容</th><th>状态</th></tr></thead>
    <tbody>
    <?php foreach($recentR as $r): ?>
      <tr>
        <td><?= h($r['nickname'] ?: '—') ?></td>
        <td class="muted"><?= h($r['bn'] ?: '—') ?></td>
        <td style="font-weight:850"><?= (int)$r['stars'] ?>★</td>
        <td class="muted"><?= h(mb_strimwidth((string)$r['content'],0,24,'…','UTF-8')) ?></td>
        <td><?= $r['status']==='approved'?'已通过':($r['status']==='pending'?'待审':'已拒') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?><p class="muted">暂无测评</p><?php endif; ?>
</div>
<?php admin_foot();
