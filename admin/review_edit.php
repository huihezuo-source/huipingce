<?php
require_once __DIR__ . '/layout.php';
admin_head('reviews','评测编辑');

$id = $_GET['id'] ?? '';
$r = null; $scoreMap = [];
if ($id) {
  $st = db()->prepare('SELECT * FROM reviews WHERE id=?'); $st->execute([$id]); $r = $st->fetch();
  if ($r) foreach (review_scores($id) as $s) $scoreMap[$s['dimension']] = $s['score'];
}
$dims = review_dimensions();
$brokers = db()->query('SELECT id,name FROM brokers ORDER BY name')->fetchAll();
$pros = $r ? json_arr($r['pros']) : [];
$cons = $r ? json_arr($r['cons']) : [];
$val = function($k,$d='') use($r){ return $r ? h($r[$k] ?? $d) : $d; };
?>
<?php admin_topbar($id?'编辑评测':'新建评测',
  '<a class="btn btn-ghost" href="/admin/reviews.php">← 返回列表</a>'); ?>
<input type="hidden" id="rvId" value="<?= h($id) ?>">

<div class="form-grid" style="grid-template-columns:1.6fr 1fr;align-items:start">
  <!-- 左：正文 -->
  <div>
    <div class="card">
      <div class="form-row"><label>评测标题 <span class="hint">IGN 风格，含经纪商名 + 角度</span></label>
        <input class="inp" id="f_title" value="<?= $val('title') ?>" placeholder="如：Sample Markets 深度评测：多重监管下的真 ECN 体验"></div>
      <div class="form-row"><label>一句话总评 verdict <span class="hint">列表卡 + hero 金句</span></label>
        <input class="inp" id="f_verdict" value="<?= $val('verdict') ?>"></div>
      <div class="form-row"><label>列表摘要 summary</label>
        <textarea class="inp" id="f_summary" rows="2"><?= $val('summary') ?></textarea></div>
    </div>

    <div class="card">
      <label style="font-weight:700;font-size:13px;display:block;margin-bottom:8px">评测正文</label>
      <div class="rt-tools">
        <button type="button" onclick="rtBlock('h2')">H2</button>
        <button type="button" onclick="rtBlock('h3')">H3</button>
        <button type="button" onclick="rtBlock('p')">正文</button>
        <button type="button" onclick="rtCmd('bold')"><b>B</b></button>
        <button type="button" onclick="rtCmd('italic')"><i>I</i></button>
        <button type="button" onclick="rtCmd('insertUnorderedList')">• 列表</button>
        <button type="button" onclick="rtCmd('insertOrderedList')">1. 列表</button>
        <button type="button" onclick="rtBlock('blockquote')">引用</button>
        <button type="button" onclick="rtLink()">🔗 链接</button>
        <button type="button" onclick="rtCmd('removeFormat')">清除格式</button>
      </div>
      <div class="rt-edit" id="rtEdit" contenteditable="true"><?= $r ? $r['content'] : '<h2>监管与实体结构</h2><p>……</p>' ?></div>
    </div>

    <div class="card">
      <div class="form-grid">
        <div class="form-row"><label>优点 <span class="hint">每行一条</span></label>
          <textarea class="inp" id="f_pros" rows="5"><?= h(implode("\n",$pros)) ?></textarea></div>
        <div class="form-row"><label>不足 <span class="hint">每行一条</span></label>
          <textarea class="inp" id="f_cons" rows="5"><?= h(implode("\n",$cons)) ?></textarea></div>
      </div>
    </div>
  </div>

  <!-- 右：评分 + 元信息 -->
  <div>
    <div class="card">
      <label style="font-weight:800;font-size:14px;display:block;margin-bottom:10px">多维评分</label>
      <?php foreach($dims as $i=>$d): $sv = $scoreMap[$d] ?? 7.5; ?>
      <div class="score-edit" data-dim="<?= h($d) ?>" data-weight="1">
        <span class="dim"><?= h($d) ?></span>
        <input type="range" min="0" max="10" step="0.1" value="<?= h($sv) ?>">
        <span class="sv"><?= h(number_format((float)$sv,1)) ?></span>
      </div>
      <?php endforeach; ?>
      <div class="overall-box">
        <div class="ov" id="ovVal"><?= $r && $r['overall_score']!==null ? h($r['overall_score']) : '0.0' ?></div>
        <div>
          <div style="font-weight:700">综合评分 / 10</div>
          <label style="font-size:12px;color:var(--ink-3);font-weight:500;display:flex;align-items:center;gap:6px;margin-top:4px">
            <input type="checkbox" id="ovManual"> 手动覆盖
          </label>
          <input type="hidden" id="overallInput" value="<?= $r && $r['overall_score']!==null ? h($r['overall_score']) : '' ?>">
        </div>
      </div>
      <p style="font-size:12px;color:var(--ink-3);margin-top:8px">默认按各维度均值自动计算，可勾选手动覆盖后在上方数字处直接改 hidden 值。</p>
    </div>

    <div class="card">
      <div class="form-row"><label>评测对象（经纪商）</label>
        <select class="inp" id="f_broker">
          <option value="">— 综合评测（不绑定）—</option>
          <?php foreach($brokers as $bk): ?>
          <option value="<?= h($bk['id']) ?>" <?= $r && $r['broker_id']===$bk['id']?'selected':'' ?>><?= h($bk['name']) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="form-row"><label>封面图 URL <span class="hint">可空，空则用占位</span></label>
        <input class="inp" id="f_cover" value="<?= $val('cover') ?>"></div>
      <div class="form-row"><label>标签 <span class="hint">逗号分隔</span></label>
        <input class="inp" id="f_tags" value="<?= $val('tags') ?>" placeholder="ECN,低成本,多重监管"></div>
      <div class="form-grid">
        <div class="form-row"><label>作者</label><input class="inp" id="f_author" value="<?= $r?$val('author'):'评测组' ?>"></div>
        <div class="form-row"><label>阅读分钟</label><input class="inp" id="f_readtime" type="number" value="<?= $r?$val('read_time'):'6' ?>"></div>
      </div>
      <div class="form-row"><label>定时发布时间 <span class="hint">仅定时发布用</span></label>
        <input class="inp" id="f_publish" type="datetime-local" value="<?= $r && $r['publish_at'] ? date('Y-m-d\TH:i',$r['publish_at']) : '' ?>"></div>
    </div>

    <div class="card" id="saveBtns" style="display:flex;gap:10px;flex-wrap:wrap">
      <button class="btn btn-primary" onclick="saveReview('published')">✅ 发布</button>
      <button class="btn btn-ghost" onclick="saveReview('draft')">存草稿</button>
      <button class="btn btn-ghost" onclick="saveReview('scheduled')">⏰ 定时</button>
    </div>
  </div>
</div>
<?php admin_foot();