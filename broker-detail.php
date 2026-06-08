<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

$id = $_GET['id'] ?? '';
$st = db()->prepare('SELECT * FROM brokers WHERE id=? LIMIT 1');
$st->execute([$id]);
$b = $st->fetch();

if (!$b) {
  header_html('brokers', ['title'=>'经纪商未找到']);
  echo '<section class="sec"><div class="container"><div class="empty"><div class="ico">🔍</div><h3>未找到该经纪商</h3><p><a class="btn btn-ghost btn-sm" href="/brokers.php">返回经纪商列表</a></p></div></div></section>';
  footer_html(); exit;
}

$ents = broker_entities($b['id'], $b['name']);
// 按监管机构分组
$groups = [];
foreach ($ents as $e) {
  $k = $e['reg_name'] ?: '其他';
  $groups[$k]['reg'] = $e;
  $groups[$k]['items'][] = $e;
}
$review = broker_latest_review($b['id']);
$scores = $review ? review_scores($review['id']) : [];

$tier = $b['score']!==null ? score_tier($b['score']) : 'mid';

// Schema.org
$jsonld = json_encode([
  '@context'=>'https://schema.org','@type'=>'FinancialService',
  'name'=>$b['name'], 'url'=>setting('siteUrl').'/broker-detail.php?id='.$b['id'],
  'description'=>$b['summary'],
  'aggregateRating'=>$b['score']!==null?[
    '@type'=>'AggregateRating','ratingValue'=>$b['score'],'bestRating'=>10,'ratingCount'=>1
  ]:null,
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

header_html('brokers', [
  'title'=>$b['name'].' 评测与监管核验',
  'desc' =>$b['name'].'（'.$b['name_en'].'）经纪商详情：关联 '.count($ents).' 个受监管实体，综合评分 '.($b['score']??'—').'。'.$b['summary'],
  'kw'   =>$b['name'].','.$b['name_en'].',监管核验,经纪商评测,受监管实体',
  'jsonld'=>$jsonld,
  'og'=>['type'=>'profile'],
]);
?>
<section class="sec-sm">
  <div class="container">
    <div class="crumb"><a href="/">首页</a><span class="sep">/</span><a href="/brokers.php">外汇经纪商</a><span class="sep">/</span><span><?= h($b['name']) ?></span></div>

    <div class="detail-grid">
      <div>
        <!-- 品牌头卡 -->
        <div class="panel" style="display:flex;align-items:center;gap:18px;flex-wrap:wrap">
          <div class="bc-logo" style="width:64px;height:64px;font-size:24px;border-radius:16px"><?php if($b['logo']): ?><img src="<?= h($b['logo']) ?>" alt=""><?php else: ?><?= h(mb_substr($b['name'],0,1)) ?><?php endif; ?></div>
          <div style="flex:1;min-width:200px">
            <h1 style="font-size:26px;font-weight:850;letter-spacing:-.02em;display:flex;align-items:center;gap:8px;flex-wrap:wrap"><?= h($b['name']) ?>
              <?php if($b['verified']): ?><span class="badge g-aaa">✓ 已核验</span><?php endif; ?></h1>
            <?php if($b['name_en'] && $b['name_en']!==$b['name']): ?><p style="color:var(--ink-3);font-size:13px"><?= h($b['name_en']) ?></p><?php endif; ?>
            <div style="display:flex;gap:14px;margin-top:8px;font-size:13px;color:var(--ink-3);flex-wrap:wrap">
              <?php if($b['established']): ?><span>成立 <?= (int)$b['established'] ?></span><?php endif; ?>
              <?php if($b['headquarters']): ?><span>📍 <?= h($b['headquarters']) ?></span><?php endif; ?>
              <span><?= h($b['btype']) ?></span>
            </div>
          </div>
          <?php if($b['score']!==null): ?>
          <div style="text-align:center">
            <div class="ring <?= $tier ?>" data-p="<?= $b['score']*10 ?>" style="--sz:88px"><span class="ring-v <?= $tier ?>"><?= h($b['score']) ?></span></div>
            <div style="font-size:12px;color:var(--ink-3);font-weight:600;margin-top:4px"><?= h(score_label($b['score'])) ?></div>
          </div>
          <?php endif; ?>
        </div>

        <?php if($b['summary']): ?>
        <div class="panel"><p style="font-size:15.5px;line-height:1.8;color:var(--ink-2)"><?= h($b['summary']) ?></p></div>
        <?php endif; ?>

        <!-- 受监管实体（栏目2核心）-->
        <div class="panel">
          <h3>🛡️ 受监管实体（<?= count($ents) ?>）</h3>
          <?php if($groups): ?>
          <p style="font-size:13px;color:var(--ink-3);margin-bottom:16px;margin-top:-8px">同一品牌通常在不同地区设立独立法律实体，你开户所在的实体决定了适用的监管与投资者保护。</p>
          <?php foreach($groups as $rname=>$g): $rg=$g['reg']; ?>
          <div style="margin-bottom:16px;border:1px solid var(--border);border-radius:var(--r-lg);overflow:hidden">
            <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:var(--surface-2);border-bottom:1px solid var(--border)">
              <span style="font-size:20px"><?= h($rg['reg_flag'] ?: '🏛️') ?></span>
              <a href="/regulator-detail.php?id=<?= h($rg['regulator_id']) ?>" style="font-weight:800"><?= h($rname) ?></a>
              <span class="badge <?= grade_class($rg['reg_grade']) ?>"><?= h($rg['reg_grade']) ?></span>
              <span style="margin-left:auto;font-size:12px;color:var(--ink-3)"><?= count($g['items']) ?> 个实体</span>
            </div>
            <?php foreach($g['items'] as $e): [$sl,$sc]=entity_status_label($e['status']); ?>
            <div style="padding:12px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;flex-wrap:wrap">
              <div style="flex:1;min-width:180px"><div style="font-weight:700"><?= h($e['name']) ?></div>
                <div style="font-size:12px;color:var(--ink-3);margin-top:2px"><?= h($e['license_type'] ?: '') ?><?= $e['client_type']?' · '.h($e['client_type']):'' ?></div></div>
              <div style="font-size:13px;color:var(--ink-2)"><span style="color:var(--ink-3)">牌照</span> <b class="lic"><?= h($e['license_no'] ?: '—') ?></b></div>
              <span class="badge <?= $sc ?>"><?= h($sl) ?></span>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endforeach; ?>
          <?php else: ?>
            <div class="empty" style="padding:32px"><div class="ico">🔎</div><h3>受监管实体核验中</h3><p>该品牌的监管实体匹配将随采集补全。</p></div>
          <?php endif; ?>
        </div>

        <!-- 评测 -->
        <?php if($review): $rt=score_tier($review['overall_score']); ?>
        <div class="panel">
          <h3>⭐ 汇评测多维评测</h3>
          <div class="score-hero" style="margin-bottom:20px">
            <div class="ring big-ring <?= $rt ?>" data-p="<?= $review['overall_score']*10 ?>"><span class="ring-v <?= $rt ?>"><?= h($review['overall_score']) ?></span></div>
            <div class="sh-text"><div class="lbl">综合评分 · <?= h(score_label($review['overall_score'])) ?></div>
              <div class="verdict"><?= h($review['verdict']) ?></div></div>
          </div>
          <?php if($scores): ?>
          <div class="bars">
            <?php foreach($scores as $s): $stt=score_tier($s['score']); ?>
            <div class="bar-row">
              <span class="bl"><?= h($s['dimension']) ?></span>
              <div class="bar-track"><div class="bar-fill <?= $stt ?>" data-w="<?= $s['score']*10 ?>"></div></div>
              <span class="bv <?= $stt ?>"><?= h($s['score']) ?></span>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <p style="margin-top:18px"><a class="btn btn-primary btn-sm" href="/review-detail.php?id=<?= h($review['id']) ?>">阅读完整评测 →</a></p>
        </div>
        <?php endif; ?>
      </div>

      <!-- 侧栏：基础参数 -->
      <aside class="sticky-side">
        <div class="panel">
          <h3>📊 基础参数</h3>
          <?php
          $params = [
            ['平台', $b['platform']],
            ['账户类型', $b['btype']],
            ['最高杠杆', $b['leverage']],
            ['点差', $b['spread']],
            ['最低入金', $b['min_dep']>0?'$'.$b['min_dep']:'—'],
            ['成立年份', $b['established']?:'—'],
            ['总部', $b['headquarters']?:'—'],
          ];
          foreach($params as $p): ?>
          <div style="display:flex;justify-content:space-between;padding:11px 0;border-bottom:1px solid var(--border);font-size:14px">
            <span style="color:var(--ink-3)"><?= h($p[0]) ?></span><b><?= h($p[1]) ?></b></div>
          <?php endforeach; ?>
          <?php if($b['website']): ?><a class="btn btn-primary" style="width:100%;margin-top:16px" href="<?= h($b['website']) ?>" target="_blank" rel="nofollow noopener">访问官网 →</a><?php endif; ?>
        </div>
        <div class="panel">
          <h3>🛡️ 监管概览</h3>
          <?php if($groups): foreach($groups as $rname=>$g): $rg=$g['reg']; ?>
          <div style="display:flex;align-items:center;gap:8px;padding:8px 0;font-size:14px">
            <span><?= h($rg['reg_flag'] ?: '🏛️') ?></span><b><?= h($rname) ?></b>
            <span class="badge <?= grade_class($rg['reg_grade']) ?>" style="margin-left:auto"><?= h($rg['reg_grade']) ?></span>
          </div>
          <?php endforeach; else: ?><p style="font-size:13px;color:var(--ink-3)">监管核验中</p><?php endif; ?>
        </div>
      </aside>
    </div>
  </div>
</section>
<?php footer_html();