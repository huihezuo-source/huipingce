<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

$id = $_GET['id'] ?? '';
$st = db()->prepare('SELECT * FROM regulators WHERE id=? LIMIT 1');
$st->execute([$id]);
$rg = $st->fetch();
if (!$rg) { http_response_code(404); $rg = null; }

if ($rg) {
  // 实体名录（搜索 + 分页）
  $q = trim($_GET['q'] ?? '');
  $page = max(1, (int)($_GET['page'] ?? 1));
  $cond = 'WHERE regulator_id=?'; $args = [$id];
  if ($q !== '') { $cond .= ' AND (name LIKE ? OR license_no LIKE ?)'; $args[]="%$q%"; $args[]="%$q%"; }
  $total = (int)(function() use($cond,$args){ $s=db()->prepare("SELECT COUNT(*) FROM reg_entities $cond"); $s->execute($args); return $s->fetchColumn(); })();
  $pg = admin_paginate($page, $total, 20);
  $els = db()->prepare("SELECT * FROM reg_entities $cond ORDER BY status ASC, name ASC LIMIT {$pg['per']} OFFSET {$pg['offset']}");
  $els->execute($args);
  $entities = $els->fetchAll();
}

if ($rg) header_html('regulators', [
  'title'=>$rg['name'].' '.$rg['full_name'].' 监管详情',
  'desc' =>$rg['name'].'（'.$rg['country'].'）受监管实体公司名录，含牌照号、牌照类型与监管状态，'.($rg['entity_count']).' 家在册实体。',
  'kw'   =>$rg['name'].','.$rg['full_name'].',受监管实体,牌照查询',
]);
else header_html('regulators', ['title'=>'监管机构未找到']);

if (!$rg): ?>
  <section class="sec"><div class="container"><div class="empty"><div class="ico">🔍</div><h3>未找到该监管机构</h3><p><a class="btn btn-ghost btn-sm" href="/regulators.php">返回监管列表</a></p></div></div></section>
<?php footer_html(); exit; endif; ?>

<section class="sec-sm">
  <div class="container">
    <div class="crumb"><a href="/">首页</a><span class="sep">/</span><a href="/regulators.php">监管机构</a><span class="sep">/</span><span><?= h($rg['name']) ?></span></div>

    <div class="panel" style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
      <span style="font-size:52px;line-height:1"><?= h($rg['flag'] ?: '🏛️') ?></span>
      <div style="flex:1;min-width:240px">
        <h1 style="font-size:28px;font-weight:850;letter-spacing:-.02em;display:flex;align-items:center;gap:10px;flex-wrap:wrap">
          <?= h($rg['name']) ?> <span class="badge <?= grade_class($rg['grade']) ?>"><?= h($rg['grade']) ?> · <?= h(grade_label($rg['grade'])) ?></span>
        </h1>
        <p style="color:var(--ink-2);margin-top:4px"><?= h($rg['full_name']) ?></p>
        <div style="display:flex;gap:18px;margin-top:10px;font-size:13px;color:var(--ink-3);flex-wrap:wrap">
          <span>📍 <?= h($rg['country']) ?> · <?= h($rg['region']) ?></span>
          <?php if($rg['established']): ?><span>🏛️ 成立 <?= (int)$rg['established'] ?></span><?php endif; ?>
          <?php if($rg['gov_type']): ?><span>🛡️ <?= h($rg['gov_type']) ?></span><?php endif; ?>
          <span>🏢 <b style="color:var(--ink)"><?= (int)$rg['entity_count'] ?></b> 家受监管实体</span>
        </div>
      </div>
      <div style="display:flex;flex-direction:column;gap:8px">
        <div class="ring <?= score_tier($rg['trust_score']/10) ?>" data-p="<?= (int)$rg['trust_score'] ?>" style="--sz:84px"><span class="ring-v <?= score_tier($rg['trust_score']/10) ?>"><?= (int)$rg['trust_score'] ?></span></div>
        <div style="text-align:center;font-size:12px;color:var(--ink-3);font-weight:600">信任度</div>
      </div>
    </div>

    <?php if($rg['description']): ?>
    <div class="panel"><h3>关于 <?= h($rg['name']) ?></h3><p style="color:var(--ink-2);line-height:1.8"><?= nl2br(h($rg['description'])) ?></p>
      <?php if($rg['query_url']): ?><p style="margin-top:14px"><a class="btn btn-ghost btn-sm" href="<?= h($rg['query_url']) ?>" target="_blank" rel="nofollow noopener">🔗 官方牌照查询入口</a></p><?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="panel">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px">
        <h3 style="margin:0">受监管实体名录 <span style="color:var(--ink-3);font-weight:600;font-size:13px">共 <?= $total ?> 家</span></h3>
        <form class="search-box" style="max-width:320px" method="get">
          <input type="hidden" name="id" value="<?= h($id) ?>">
          <span>🔍</span><input type="text" name="q" value="<?= h($q) ?>" placeholder="搜索公司名 / 牌照号">
        </form>
      </div>
      <?php if($entities): ?>
      <div class="tbl-wrap">
        <table class="tbl">
          <thead><tr><th>受监管实体</th><th>牌照号</th><th>牌照类型</th><th>客户</th><th>状态</th></tr></thead>
          <tbody>
          <?php foreach($entities as $e): [$sl,$sc]=entity_status_label($e['status']); ?>
            <tr>
              <td><div class="ent-name"><?= h($e['name']) ?></div><?php if($e['city']||$e['reg_date']): ?><div class="ent-sub"><?= h(trim(($e['city']?$e['city'].' · ':'').($e['reg_date']?'注册 '.$e['reg_date']:''),' ·')) ?></div><?php endif; ?></td>
              <td><span class="lic"><?= h($e['license_no'] ?: '—') ?></span></td>
              <td style="color:var(--ink-2);font-size:13px"><?= h($e['license_type'] ?: '—') ?></td>
              <td style="font-size:13px;color:var(--ink-2)"><?= h($e['client_type'] ?: '—') ?></td>
              <td><span class="badge <?= $sc ?>"><?= h($sl) ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?= admin_pager_html($pg, '/regulator-detail.php?id='.h($id).($q?'&q='.urlencode($q):'').'&') ?>
      <?php else: ?>
        <div class="empty"><div class="ico">🗂️</div><h3><?= $q?'未匹配到实体':'该监管机构实体采集中' ?></h3><p>受监管实体数据将随采集器持续补全。</p></div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php footer_html();