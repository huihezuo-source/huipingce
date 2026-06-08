<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

$id = $_GET['id'] ?? '';
$st = db()->prepare("SELECT r.*, b.name AS broker_name, b.logo AS broker_logo, b.score AS broker_score
                     FROM reviews r LEFT JOIN brokers b ON r.broker_id=b.id WHERE r.id=? LIMIT 1");
$st->execute([$id]);
$r = $st->fetch();

if (!$r || ($r['status']!=='published' && !get_session())) {
  header_html('reviews', ['title'=>'评测未找到']);
  echo '<section class="sec"><div class="container"><div class="empty"><div class="ico">🔍</div><h3>未找到该评测</h3><p><a class="btn btn-ghost btn-sm" href="/reviews.php">返回评测列表</a></p></div></div></section>';
  footer_html(); exit;
}

// 浏览数 +1
try { db()->prepare('UPDATE reviews SET views=views+1 WHERE id=?')->execute([$id]); } catch(Exception $e){}

$scores = review_scores($id);
$pros = json_arr($r['pros']);
$cons = json_arr($r['cons']);
$tier = $r['overall_score']!==null ? score_tier($r['overall_score']) : 'mid';
$has_img = !empty($r['cover']);

// Schema.org Review
$jsonld = json_encode(array_filter([
  '@context'=>'https://schema.org','@type'=>'Review',
  'name'=>$r['title'],
  'reviewBody'=>mb_substr(strip_tags($r['content']),0,500),
  'datePublished'=>date('c', $r['publish_at'] ?: $r['created_at']),
  'author'=>['@type'=>'Organization','name'=>$r['author']],
  'publisher'=>['@type'=>'Organization','name'=>setting('siteName')],
  'itemReviewed'=>$r['broker_name']?['@type'=>'FinancialService','name'=>$r['broker_name']]:null,
  'reviewRating'=>$r['overall_score']!==null?[
    '@type'=>'Rating','ratingValue'=>$r['overall_score'],'bestRating'=>10,'worstRating'=>0
  ]:null,
]), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

header_html('reviews', [
  'title'=>$r['title'],
  'desc' =>$r['summary'] ?: $r['verdict'],
  'kw'   =>($r['broker_name']?$r['broker_name'].',':'').'经纪商评测,'.str_replace(',',',',$r['tags']),
  'jsonld'=>$jsonld,
  'og'=>['type'=>'article','image'=>$r['cover']],
]);
?>
<section class="sec-sm">
  <div class="container">
    <div class="crumb"><a href="/">首页</a><span class="sep">/</span><a href="/reviews.php">评测</a><span class="sep">/</span><span><?= h(mb_substr($r['title'],0,20)) ?></span></div>

    <!-- 评测 hero -->
    <div class="rev-hero <?= $has_img?'has-img':'' ?>">
      <?php if($has_img): ?><img class="rev-hero-bg" src="<?= h($r['cover']) ?>" alt="<?= h($r['title']) ?>"><?php endif; ?>
      <div class="rev-hero-in" <?= $has_img?'':'style="color:var(--ink)"' ?>>
        <?php if($r['tags']): ?><div class="rh-tags"><?php foreach(array_slice(explode(',',$r['tags']),0,3) as $t): ?><span class="tag" style="<?= $has_img?'background:rgba(255,255,255,.18);color:#fff':'' ?>"><?= h(trim($t)) ?></span><?php endforeach; ?></div><?php endif; ?>
        <h1><?= h($r['title']) ?></h1>
        <?php if($r['verdict']): ?><p class="rh-verdict"><?= h($r['verdict']) ?></p><?php endif; ?>
        <div class="rh-meta"><span><?= h($r['author']) ?></span><span><?= (int)$r['read_time'] ?> 分钟读</span><span><?= h(date('Y-m-d', $r['publish_at'] ?: $r['created_at'])) ?></span></div>
      </div>
    </div>

    <div class="detail-grid">
      <div>
        <!-- 总分 + 多维 -->
        <?php if($r['overall_score']!==null): ?>
        <div class="panel">
          <div class="score-hero" style="margin-bottom:<?= $scores?'22px':'0' ?>">
            <div class="ring big-ring <?= $tier ?>" data-p="<?= $r['overall_score']*10 ?>"><span class="ring-v <?= $tier ?>"><?= h($r['overall_score']) ?></span></div>
            <div class="sh-text"><div class="lbl">综合评分 / 10 · <?= h(score_label($r['overall_score'])) ?></div>
              <div class="verdict"><?= h($r['broker_name'] ?: $r['title']) ?></div></div>
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
        </div>
        <?php endif; ?>

        <!-- 优缺点 -->
        <?php if($pros || $cons): ?>
        <div class="panel">
          <div class="proscons">
            <div class="pc-box pros"><h4>✓ 优点</h4><ul><?php foreach($pros as $p): ?><li><?= h($p) ?></li><?php endforeach; ?></ul></div>
            <div class="pc-box cons"><h4>✕ 不足</h4><ul><?php foreach($cons as $c): ?><li><?= h($c) ?></li><?php endforeach; ?></ul></div>
          </div>
        </div>
        <?php endif; ?>

        <!-- 正文 -->
        <div class="panel">
          <div class="article"><?= $r['content'] ?></div>
        </div>
      </div>

      <!-- 侧栏 -->
      <aside class="sticky-side">
        <?php if($r['broker_id']): ?>
        <div class="panel" style="background:var(--grad);color:#fff;border:none">
          <div style="font-size:13px;opacity:.85;font-weight:600">本文评测对象</div>
          <div style="font-size:20px;font-weight:850;margin:6px 0 14px"><?= h($r['broker_name']) ?></div>
          <a class="btn btn-ghost" style="width:100%;background:#fff" href="/broker-detail.php?id=<?= h($r['broker_id']) ?>">查看经纪商详情 →</a>
        </div>
        <?php endif; ?>
        <div class="panel">
          <h3>评分维度说明</h3>
          <p style="font-size:13px;color:var(--ink-2);line-height:1.7">汇评测采用 6 维评分体系：<b><?= h(implode('、', review_dimensions())) ?></b>。各维度 0-10 分，加权得出综合评分。</p>
        </div>
        <div class="panel">
          <p style="font-size:12.5px;color:var(--ink-3);line-height:1.7">⚠️ 本评测基于公开信息与编辑团队测试，仅供参考，不构成投资建议。外汇及差价合约交易涉及高风险。</p>
        </div>
      </aside>
    </div>
  </div>
</section>
<?php footer_html();