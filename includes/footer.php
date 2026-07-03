<?php
/** 汇评测 — 公共底部 */
function footer_html() {
    $cfg = settings_all();
    $siteName = $cfg['siteName']   ?? '汇评测';
    $siteEn   = $cfg['siteNameEn'] ?? 'Huipingce';
    $slogan   = $cfg['siteSlogan'] ?? '';
    $icp      = $cfg['icp']        ?? '';
    $svc      = $cfg['serviceTime']?? '';
    $year     = date('Y');

    $icp_html = $icp ? '<a href="https://beian.miit.gov.cn" target="_blank" rel="nofollow">'.h($icp).'</a>' : '';
    $svc_html = $svc ? '<span>客服时间 '.h($svc).'</span>' : '';

    echo <<<HTML
</main>
<footer class="ftr">
  <div class="ftr-in">
    <div class="ftr-top">
      <div class="ftr-brand">
        <div class="ftr-logo"><span class="brand-mark">评</span>{$siteName} <em>{$siteEn}</em></div>
        <p class="ftr-slogan">{$slogan}</p>
      </div>
      <nav class="ftr-cols">
        <div class="ftr-col">
          <h4>栏目</h4>
          <a href="/regulators.php">监管机构</a>
          <a href="/brokers.php">外汇交易商</a>
          <a href="/articles.php">行业资讯</a>
          <a href="/exposure.php">曝光维权</a>
        </div>
        <div class="ftr-col">
          <h4>参与</h4>
          <a href="/register.php">注册会员</a>
          <a href="/exposure-submit.php">我要曝光</a>
          <a href="/brokers.php">给交易商打分</a>
        </div>
        <div class="ftr-col">
          <h4>关于</h4>
          <a href="/regulators.php">监管说明</a>
          <a href="/articles.php">评测方法</a>
        </div>
      </nav>
    </div>
    <div class="ftr-bottom">
      <p class="ftr-disc">风险提示：外汇及差价合约交易涉及高风险，可能导致本金损失。本站聚合的监管、评分与用户测评信息仅供参考，不构成投资建议。用户发布的评分、测评与曝光内容为其个人观点，不代表本站立场。请根据自身情况审慎决策。</p>
      <div class="ftr-meta">
        <span>© {$year} {$siteName} {$siteEn}</span>
        {$svc_html}
        {$icp_html}
      </div>
    </div>
  </div>
</footer>
</body>
</html>
HTML;
}
