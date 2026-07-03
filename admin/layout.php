<?php
// 后台公共框架
require_once __DIR__ . '/../includes/db.php';

function admin_head($active='', $title='') {
    $s = require_login();
    $siteName = setting('siteName','汇评测');
    $nav = [
        ['index','/admin/index.php','📊 概览'],
        ['brokers','/admin/brokers.php','🏦 交易商'],
        ['regulators','/admin/regulators.php','🛡️ 监管机构'],
        ['entities','/admin/reg_entities.php','🗂️ 受监管实体'],
        ['articles','/admin/articles.php','📰 资讯'],
        ['complaints','/admin/complaints.php','⚠️ 曝光'],
        ['reviews','/admin/reviews.php','⭐ 用户测评'],
        ['users','/admin/users.php','👥 会员'],
        ['settings','/admin/settings.php','⚙️ 设置'],
    ];
    $navhtml='';
    foreach($nav as $n){ $on=$active===$n[0]?' class="on"':''; $navhtml.='<a'.$on.' href="'.$n[1].'">'.$n[2].'</a>'; }
    $uname = h($s['name'] ?: $s['username']);
    echo <<<HTML
<!DOCTYPE html><html lang="zh-CN"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$title} · {$siteName}后台</title>
<link rel="icon" href="/assets/img/favicon.svg">
<link rel="stylesheet" href="/assets/css/admin.css">
</head><body>
<div class="adm">
<aside class="adm-side">
  <a class="adm-logo" href="/admin/index.php"><span class="brand-mark"></span>{$siteName}<em>ADMIN</em></a>
  <nav class="adm-nav">{$navhtml}</nav>
  <div class="adm-user"><span>👤 {$uname}</span><a href="/admin/logout.php">退出</a></div>
</aside>
<main class="adm-main">
HTML;
}
function admin_foot() {
    echo "</main></div>\n<script src=\"/assets/js/admin.js\"></script>\n</body></html>";
}
// flash 消息
function admin_topbar($title, $actions=''){ echo '<div class="adm-top"><h1>'.h($title).'</h1><div class="adm-actions">'.$actions.'</div></div>'; }
