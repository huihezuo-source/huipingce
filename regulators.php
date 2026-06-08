<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

$grade = $_GET['grade'] ?? '';
$valid = ['AAA','AA','A','B','C'];
$where = ''; $args = [];
if (in_array($grade, $valid, true)) { $where = 'WHERE grade=?'; $args[] = $grade; }

$regs = db()->prepare("SELECT * FROM regulators $where ORDER BY FIELD(grade,'AAA','AA','A','B','C'), sort_order ASC");
$regs->execute($args);
$regs = $regs->fetchAll();

$total_ent = (int)db()->query('SELECT COUNT(*) FROM reg_entities')->fetchColumn();

header_html('regulators', [
  'title'=>'全球外汇监管机构数据库',
  'desc' =>'汇评测收录全球主要外汇监管机构，展示各机构下受监管实体公司、牌照号与监管等级，帮你核验经纪商真实合规身份。',
  'kw'   =>'外汇监管机构,FCA,ASIC,CySEC,受监管实体,牌照查询',
]);
?>
<section class="sec-sm">
  <div class="container">
    <div class="crumb"><a href="/">首页</a><span class="sep">/</span><span>监管机构</span></div>
    <div class="sec-head">
      <div>
        <span class="eyebrow">Regulators</span>
        <h2>全球监管机构</h2>
        <p style="color:var(--ink-2);margin-top:6px">收录 <b><?= count($regs) ?></b> 家监管机构 · <b><?= $total_ent ?></b> 个受监管实体</p>
      </div>
    </div>
    <div class="chips" style="margin-bottom:24px">
      <a class="chip<?= $grade===''?' on':'' ?>" href="/regulators.php">全部</a>
      <?php foreach ($valid as $g): ?>
      <a class="chip<?= $grade===$g?' on':'' ?>" href="/regulators.php?grade=<?= $g ?>"><?= $g ?> · <?= h(grade_label($g)) ?></a>
      <?php endforeach; ?>
    </div>

    <?php if ($regs): ?>
    <div class="grid grid-3">
      <?php foreach ($regs as $rg): ?>
      <a class="card reg-card reveal" href="/regulator-detail.php?id=<?= h($rg['id']) ?>">
        <div class="reg-top">
          <span class="reg-flag"><?= h($rg['flag'] ?: '🏛️') ?></span>
          <div class="reg-name"><?= h($rg['name']) ?> <span class="badge <?= grade_class($rg['grade']) ?>"><?= h($rg['grade']) ?></span>
            <div class="full"><?= h($rg['full_name']) ?></div>
          </div>
        </div>
        <div class="reg-mid">
          <div class="reg-trust">
            <div class="tn"><span>信任度</span><span><?= (int)$rg['trust_score'] ?>/100</span></div>
            <div class="bar-track"><div class="bar-fill <?= score_tier($rg['trust_score']/10) ?>" data-w="<?= (int)$rg['trust_score'] ?>"></div></div>
          </div>
        </div>
        <div class="reg-foot">
          <span class="ec"><?= h($rg['country']) ?> · <?= h($rg['region']) ?></span>
          <span class="ec"><b><?= (int)$rg['entity_count'] ?></b> 家实体</span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <div class="empty"><div class="ico">🏛️</div><h3>暂无该等级监管机构</h3></div>
    <?php endif; ?>
  </div>
</section>
<?php footer_html();