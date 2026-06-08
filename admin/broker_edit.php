<?php
require_once __DIR__ . '/layout.php';
admin_head('brokers','经纪商编辑');

$id = $_GET['id'] ?? '';
$b = null;
if ($id) { $st=db()->prepare('SELECT * FROM brokers WHERE id=?'); $st->execute([$id]); $b=$st->fetch(); }
$v = function($k,$d='') use($b){ return $b ? h($b[$k] ?? $d) : $d; };

// 已关联实体
$linked = [];
if ($id) {
  $st=db()->prepare("SELECT e.*, r.name AS reg_name, r.flag AS reg_flag FROM broker_entity_map m
    JOIN reg_entities e ON m.entity_id=e.id LEFT JOIN regulators r ON e.regulator_id=r.id
    WHERE m.broker_id=? ORDER BY r.sort_order"); $st->execute([$id]); $linked=$st->fetchAll();
}
// 自动匹配建议（排除已关联）
$suggest = [];
if ($b) {
  $linkedIds = array_column($linked,'id');
  foreach (auto_match_entity_ids($b['name']) as $eid) {
    if (in_array($eid,$linkedIds)) continue;
    $st=db()->prepare("SELECT e.*,r.name AS reg_name,r.flag AS reg_flag FROM reg_entities e LEFT JOIN regulators r ON e.regulator_id=r.id WHERE e.id=?");
    $st->execute([$eid]); $row=$st->fetch(); if($row) $suggest[]=$row;
  }
}
?>
<?php admin_topbar($id?'编辑经纪商':'新建经纪商','<a class="btn btn-ghost" href="/admin/brokers.php">← 返回</a>'); ?>

<form method="post" action="/admin/api/broker_save.php" class="form-grid" style="grid-template-columns:1.4fr 1fr;align-items:start">
  <input type="hidden" name="id" value="<?= h($id) ?>">
  <div>
    <div class="card">
      <div class="form-grid">
        <div class="form-row"><label>品牌名 *</label><input class="inp" name="name" value="<?= $v('name') ?>" required></div>
        <div class="form-row"><label>英文名</label><input class="inp" name="name_en" value="<?= $v('name_en') ?>"></div>
      </div>
      <div class="form-grid-3">
        <div class="form-row"><label>代码</label><input class="inp" name="code" value="<?= $v('code') ?>"></div>
        <div class="form-row"><label>成立年份</label><input class="inp" type="number" name="established" value="<?= $v('established') ?>"></div>
        <div class="form-row"><label>账户类型</label><input class="inp" name="btype" value="<?= $b?$v('btype'):'ECN' ?>"></div>
      </div>
      <div class="form-row"><label>总部</label><input class="inp" name="headquarters" value="<?= $v('headquarters') ?>"></div>
      <div class="form-row"><label>一句话简介</label><textarea class="inp" name="summary" rows="2"><?= $v('summary') ?></textarea></div>
      <div class="form-grid-3">
        <div class="form-row"><label>平台</label><input class="inp" name="platform" value="<?= $b?$v('platform'):'MT4/MT5' ?>"></div>
        <div class="form-row"><label>最高杠杆</label><input class="inp" name="leverage" value="<?= $b?$v('leverage'):'1:500' ?>"></div>
        <div class="form-row"><label>最低入金 $</label><input class="inp" type="number" name="min_dep" value="<?= $b?$v('min_dep'):'0' ?>"></div>
      </div>
      <div class="form-grid-3">
        <div class="form-row"><label>点差</label><input class="inp" name="spread" value="<?= $b?$v('spread'):'浮动' ?>"></div>
        <div class="form-row"><label>官网</label><input class="inp" name="website" value="<?= $v('website') ?>"></div>
        <div class="form-row"><label>综合评分</label><input class="inp" type="number" step="0.1" name="score" value="<?= $b&&$b['score']!==null?$v('score'):'' ?>" placeholder="评测发布后自动"></div>
      </div>
      <div class="form-row" style="display:flex;gap:24px">
        <label style="display:flex;align-items:center;gap:6px;font-weight:600"><input type="checkbox" name="featured" value="1" <?= $b&&$b['featured']?'checked':'' ?>> 精选推荐</label>
        <label style="display:flex;align-items:center;gap:6px;font-weight:600"><input type="checkbox" name="verified" value="1" <?= $b&&$b['verified']?'checked':'' ?>> 已核验</label>
      </div>
      <button class="btn btn-primary" type="submit">💾 保存经纪商</button>
    </div>
  </div>

  <!-- 实体关联 -->
  <div>
    <div class="card">
      <label style="font-weight:800;font-size:14px;display:block;margin-bottom:10px">🔗 受监管实体关联</label>
      <?php if(!$id): ?>
        <p class="muted">先保存经纪商，再关联受监管实体。</p>
      <?php else: ?>
        <?php if($linked): ?>
        <div style="margin-bottom:14px">
          <?php foreach($linked as $e): ?>
          <div style="display:flex;align-items:center;gap:8px;padding:9px 0;border-bottom:1px solid var(--border)">
            <span><?= h($e['reg_flag'] ?: '🏛️') ?></span>
            <div style="flex:1;font-size:13px"><b><?= h($e['name']) ?></b><div class="muted"><?= h($e['reg_name']) ?> · <?= h($e['license_no']?:'—') ?></div></div>
            <button class="btn btn-ghost btn-sm btn-danger" type="button" onclick="entMap('unlink','<?= h($id) ?>',<?= (int)$e['id'] ?>)">移除</button>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?><p class="muted" style="margin-bottom:14px">尚未关联任何实体。</p><?php endif; ?>

        <?php if($suggest): ?>
        <div style="font-size:12px;font-weight:700;color:var(--ink-3);margin-bottom:6px">💡 自动匹配建议</div>
        <?php foreach($suggest as $e): ?>
        <div style="display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px dashed var(--border)">
          <span><?= h($e['reg_flag'] ?: '🏛️') ?></span>
          <div style="flex:1;font-size:13px"><b><?= h($e['name']) ?></b><div class="muted"><?= h($e['reg_name']) ?></div></div>
          <button class="btn btn-ghost btn-sm" type="button" onclick="entMap('link','<?= h($id) ?>',<?= (int)$e['id'] ?>)">+ 关联</button>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <div style="margin-top:14px">
          <input class="inp" id="entSearch" placeholder="搜索实体名 / 牌照号添加…">
          <div id="entResults" style="margin-top:8px"></div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</form>

<script>
function entMap(action, bid, eid){
  fetch('/admin/api/broker_entity_map.php',{method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({action:action,broker_id:bid,entity_id:eid})})
    .then(r=>r.json()).then(res=>{ if(res.ok) location.reload(); else alert(res.error||'失败'); });
}
(function(){
  var inp=document.getElementById('entSearch'); if(!inp) return;
  var bid='<?= h($id) ?>', t;
  inp.addEventListener('input',function(){
    clearTimeout(t); var q=inp.value.trim(); if(q.length<2){document.getElementById('entResults').innerHTML='';return;}
    t=setTimeout(function(){
      fetch('/admin/api/entity_search.php?q='+encodeURIComponent(q)).then(r=>r.json()).then(function(rows){
        document.getElementById('entResults').innerHTML=rows.map(function(e){
          return '<div style="display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid var(--border)"><div style="flex:1;font-size:13px"><b>'+e.name+'</b><div class="muted">'+(e.reg_name||'')+' · '+(e.license_no||'—')+'</div></div><button class="btn btn-ghost btn-sm" onclick="entMap(\'link\',\''+bid+'\','+e.id+')">+ 关联</button></div>';
        }).join('')||'<p class="muted">无匹配</p>';
      });
    },300);
  });
})();
</script>
<?php admin_foot();