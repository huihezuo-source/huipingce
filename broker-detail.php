<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';

$id = $_GET['id'] ?? '';
$st = db()->prepare('SELECT * FROM brokers WHERE id=? LIMIT 1');
$st->execute([$id]);
$b = $st->fetch();
if (!$b) { http_response_code(404); header_html(''); echo '<div class="empty"><div class="ico">🚫</div><p>交易商不存在或已下架</p><a class="btn" href="/brokers.php">返回列表</a></div>'; footer_html(); exit; }

$stats  = broker_rating_stats($b['id']);
$ents   = broker_entities($b['id'], $b['name']);
$me     = current_user();

// 当前用户已有的评分
$myReview = null;
if ($me) {
    $q = db()->prepare('SELECT * FROM broker_reviews WHERE broker_id=? AND user_id=? LIMIT 1');
    $q->execute([$b['id'], $me['id']]);
    $myReview = $q->fetch() ?: null;
}

// 测评列表（已通过 + 自己的）
$rst = db()->prepare(
    "SELECT r.*, u.nickname, u.username, u.avatar
     FROM broker_reviews r LEFT JOIN site_users u ON r.user_id=u.id
     WHERE r.broker_id=? AND (r.status='approved' ".($me?"OR r.user_id=".db()->quote($me['id']):'').")
       AND r.content IS NOT NULL AND r.content<>''
     ORDER BY r.useful_count DESC, r.created_at DESC LIMIT 50"
);
$rst->execute([$b['id']]);
$reviews = $rst->fetchAll();

// 我已投「有用」的
$voted = [];
if ($me && $reviews) {
    $ids = array_column($reviews,'id');
    $ph = implode(',', array_fill(0,count($ids),'?'));
    $vq = db()->prepare("SELECT review_id FROM review_votes WHERE user_id=? AND review_id IN ($ph)");
    $vq->execute(array_merge([$me['id']], $ids));
    $voted = array_column($vq->fetchAll(),'review_id');
}

// 相关曝光 / 资讯
$exps = db()->prepare("SELECT * FROM complaints WHERE broker_id=? AND status<>'rejected' ORDER BY created_at DESC LIMIT 5");
$exps->execute([$b['id']]); $exps = $exps->fetchAll();
$arts = db()->prepare("SELECT * FROM articles WHERE broker_id=? AND ".published_where()." ORDER BY COALESCE(publish_at,created_at) DESC LIMIT 5");
$arts->execute([$b['id']]); $arts = $arts->fetchAll();

$tier = score_tier($stats['score10']);

// JSON-LD
$jsonld = json_encode([
    '@context'=>'https://schema.org','@type'=>'Organization',
    'name'=>$b['name'],'url'=>$b['website'] ?: canonical_url(),
] + ($stats['count']>0 ? ['aggregateRating'=>[
    '@type'=>'AggregateRating','ratingValue'=>$stats['avg'],'bestRating'=>5,'worstRating'=>1,'ratingCount'=>$stats['count']
]] : []), JSON_UNESCAPED_UNICODE);

header_html('brokers', [
    'title'=>$b['name'].' 怎么样 · 监管牌照与用户评价',
    'desc'=>($b['summary'] ?: $b['name'].' 的监管牌照、受监管实体与用户真实打分测评').'。',
    'jsonld'=>$jsonld,
]);
?>
<div class="crumb"><a href="/">首页</a> › <a href="/brokers.php">交易商</a> › <b><?=h($b['name'])?></b></div>

<!-- 头部 + 豆瓣评分块 -->
<div class="card bd-head">
  <?=logo_html($b['logo'],$b['name'],'bd-logo')?>
  <div class="bd-head-main">
    <h1><?=h($b['name'])?>
      <?php if($b['name_en']): ?><span class="en"><?=h($b['name_en'])?></span><?php endif; ?>
      <?php if($b['verified']): ?><span class="badge badge-green">✓ 已核验</span><?php endif; ?>
      <?php if((int)$b['complaint_count']>0): ?><a href="#exposure" class="badge badge-red"><?=$b['complaint_count']?> 条曝光</a><?php endif; ?>
    </h1>
    <?php if($b['summary']): ?><p class="bd-summary"><?=h($b['summary'])?></p><?php endif; ?>
    <div class="bd-facts">
      <?php if($b['established']): ?><span>成立年份 <b><?=h($b['established'])?></b></span><?php endif; ?>
      <?php if($b['country']): ?><span>注册地 <b><?=h($b['country'])?></b></span><?php endif; ?>
      <?php if($b['headquarters']): ?><span>总部 <b><?=h($b['headquarters'])?></b></span><?php endif; ?>
      <?php if($b['platform']): ?><span>平台 <b><?=h($b['platform'])?></b></span><?php endif; ?>
      <?php if($b['leverage']): ?><span>最高杠杆 <b><?=h($b['leverage'])?></b></span><?php endif; ?>
      <?php if((int)$b['min_dep']>0): ?><span>最低入金 <b>$<?=number_format($b['min_dep'])?></b></span><?php endif; ?>
      <?php if($b['btype']): ?><span>模式 <b><?=h($b['btype'])?></b></span><?php endif; ?>
      <?php if($b['website']): ?><span><a href="<?=h($b['website'])?>" target="_blank" rel="nofollow noopener">官网 ↗</a></span><?php endif; ?>
    </div>
    <?php if($b['tags']): ?><div style="margin-top:10px"><?php foreach(explode(',',$b['tags']) as $t){ if(trim($t)) echo '<span class="tag">'.h(trim($t)).'</span>'; } ?></div><?php endif; ?>
  </div>
  <div class="bd-rating">
    <div class="bd-rating-left">
      <?php if($stats['count']>0): ?>
        <div class="rating-num <?=$tier?>"><?=number_format($stats['score10'],1)?></div>
        <?=stars_html($stats['avg'])?>
        <div class="cnt"><?=$stats['count']?> 人评价</div>
        <div class="muted" style="font-size:12px;margin-top:2px"><?=score_label($stats['score10'])?></div>
      <?php else: ?>
        <div class="rating-num" style="font-size:26px;color:var(--faint)">暂无<br>评分</div>
        <div class="noscore">还没有人评价<br>来做第一个打分的人</div>
      <?php endif; ?>
    </div>
    <?php if($stats['count']>0): ?>
    <div class="rating-hist">
      <?php $labels=[5=>'力荐',4=>'推荐',3=>'还行',2=>'较差',1=>'很差']; foreach([5,4,3,2,1] as $s): ?>
        <div class="hist-row">
          <span class="lbl"><?=$labels[$s]?></span>
          <span class="hist-bar"><i style="width:<?=$stats['pct'][$s]?>%"></i></span>
          <span class="pct"><?=$stats['pct'][$s]?>%</span>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="layout">
  <div class="bd-body">
    <!-- 打分/测评框 -->
    <div class="panel card" id="rate">
      <div class="panel-hd">我要点评</div>
      <?php if($me): ?>
        <form id="rateForm" data-broker="<?=h($b['id'])?>">
          <div class="rate-box" style="flex-direction:column;align-items:flex-start">
            <div style="display:flex;align-items:center;gap:14px">
              <span class="rate-title">我的评分</span>
              <span class="rate-stars">
                <?php for($i=1;$i<=5;$i++): ?><span class="rs" data-v="<?=$i?>">★</span><?php endfor; ?>
              </span>
              <span class="rate-word" id="rateWord"><?=$myReview?star_word($myReview['stars']):'点击星星打分'?></span>
              <input type="hidden" id="rateStars" value="<?=$myReview?(int)$myReview['stars']:0?>">
            </div>
            <textarea id="rateContent" placeholder="说说你在这家交易商的真实体验：出入金、点差、客服、平台稳定性……（选填）"><?=$myReview?h($myReview['content']):''?></textarea>
            <div style="display:flex;justify-content:space-between;align-items:center;width:100%">
              <span class="muted" style="font-size:12px"><?=$myReview?'你已评价过，再次提交将更新':'每个交易商可评价一次，可随时修改'?></span>
              <button class="btn btn-primary" type="submit"><?=$myReview?'更新评价':'发布评价'?></button>
            </div>
          </div>
        </form>
      <?php else: ?>
        <div class="rate-box">
          <span class="rate-login-tip">登录后即可给 <b><?=h($b['name'])?></b> 打分、写测评</span>
          <a class="btn btn-primary" href="/login.php?next=<?=urlencode('/broker-detail.php?id='.$b['id'])?>">登录 / 注册</a>
        </div>
      <?php endif; ?>
    </div>

    <!-- 监管牌照 -->
    <div class="panel card">
      <div class="panel-hd">监管牌照 <span class="cnt"><?=count($ents)?> 个受监管实体</span></div>
      <?php if(!$ents): ?>
        <div class="empty" style="padding:26px"><p>暂未收录该交易商的受监管实体信息</p></div>
      <?php else: ?>
        <table class="ent-table">
          <thead><tr><th>监管机构</th><th>持牌实体</th><th>牌照号</th><th>类型</th><th>状态</th></tr></thead>
          <tbody>
          <?php foreach($ents as $e): [$sl,$sc]=entity_status_label($e['status']); ?>
            <tr>
              <td><a class="ent-reg" href="/regulator-detail.php?id=<?=h($e['regulator_id'])?>">
                <span><?=h($e['reg_flag'])?></span>
                <span class="grade <?=grade_class($e['reg_grade'])?>"><?=h($e['reg_name'])?></span>
              </a></td>
              <td><?=h($e['name'])?><?php if($e['name_local']): ?><br><span class="muted" style="font-size:12px"><?=h($e['name_local'])?></span><?php endif; ?></td>
              <td><?=h($e['license_no']?:'—')?></td>
              <td><?=h($e['license_type']?:($e['client_type']?:'—'))?></td>
              <td><span class="pill <?=$sc?>"><?=$sl?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <!-- 公司介绍 -->
    <?php if(trim((string)$b['intro'])!==''): ?>
    <div class="panel card">
      <div class="panel-hd">公司介绍</div>
      <div class="art-body"><?=$b['intro']?></div>
    </div>
    <?php endif; ?>

    <!-- 用户测评 -->
    <div class="panel card">
      <div class="panel-hd">用户测评 <span class="cnt"><?=count($reviews)?> 条</span></div>
      <?php if(!$reviews): ?>
        <div class="empty" style="padding:30px"><div class="ico">💬</div><p>还没有测评，<?=$me?'来写第一条':'登录后来写第一条'?></p></div>
      <?php else: foreach($reviews as $r):
        $uname = $r['nickname'] ?: ($r['username'] ?: '匿名用户');
        $uobj  = ['nickname'=>$r['nickname'],'username'=>$r['username'],'avatar'=>$r['avatar']];
        $isVoted = in_array($r['id'],$voted,true); ?>
        <div class="review-item">
          <?=user_avatar_html($uobj,40)?>
          <div class="review-main">
            <div class="review-hd">
              <span class="uname"><?=h($uname)?></span>
              <?=stars_html($r['stars'])?>
              <span class="badge badge-amber"><?=star_word($r['stars'])?></span>
              <?php if($r['status']==='pending'): ?><span class="badge badge-gray">审核中</span><?php endif; ?>
              <span class="rtime"><?=time_ago($r['created_at'])?></span>
            </div>
            <div class="review-body"><?=nl2br(h($r['content']))?></div>
            <div class="review-foot">
              <span class="review-useful <?=$isVoted?'voted':''?>" data-review="<?=h($r['id'])?>">👍 有用 (<span class="uc"><?=(int)$r['useful_count']?></span>)</span>
            </div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <!-- 侧栏 -->
  <aside>
    <div class="card side-card" id="exposure">
      <h3>⚠️ 相关曝光</h3>
      <?php if(!$exps): ?><p class="muted" style="font-size:13px">暂无曝光记录</p>
      <?php else: foreach($exps as $c): [$sl,$sc]=complaint_status_label($c['status']); ?>
        <div style="padding:9px 0;border-bottom:1px solid var(--line)">
          <a href="/exposure-detail.php?id=<?=h($c['id'])?>" style="color:var(--ink);font-size:13.5px;font-weight:600"><?=h(mb_strimwidth($c['title'],0,40,'…','UTF-8'))?></a>
          <div style="margin-top:4px"><span class="badge badge-red"><?=h($c['type'])?></span> <span class="pill <?=$sc?>"><?=$sl?></span></div>
        </div>
      <?php endforeach; endif; ?>
      <a class="btn btn-block" style="margin-top:12px" href="/exposure-submit.php?broker=<?=urlencode($b['id'])?>">曝光这家交易商</a>
    </div>

    <?php if($arts): ?>
    <div class="card side-card">
      <h3>📰 相关资讯</h3>
      <div class="side-list">
        <?php foreach($arts as $a): ?>
          <a href="/article-detail.php?id=<?=h($a['id'])?>"><?=h(mb_strimwidth($a['title'],0,42,'…','UTF-8'))?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </aside>
</div>

<?php footer_html(); ?>
