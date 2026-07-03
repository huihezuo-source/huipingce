<?php
/**
 * 汇评测 — 公共头部
 * 调用：header_html($active, ['title'=>..,'desc'=>..,'kw'=>..,'og'=>[..],'jsonld'=>'..','canonical'=>'..'])
 * $active ∈ regulators|brokers|articles|exposure|''
 */
require_once __DIR__ . '/auth.php';

function canonical_url(string $override = ''): string {
    $base = rtrim(setting('siteUrl', 'https://www.huipingce.com'), '/');
    if ($override !== '') {
        if (preg_match('#^https?://#i', $override)) return $override;
        return $base . $override;
    }
    $script = $_SERVER['SCRIPT_NAME'] ?? '/';
    if ($script === '/index.php' || $script === '/') return $base . '/';

    $whitelist = ['id','slug','cat','broker','regulator','tag','type'];
    $kept = [];
    foreach ($whitelist as $k) {
        if (isset($_GET[$k]) && $_GET[$k] !== '') {
            $v = (string)$_GET[$k];
            if (preg_match('/^[A-Za-z0-9_\-\.]{1,128}$/u', $v)) $kept[$k] = $v;
        }
    }
    ksort($kept);
    $url = $base . $script;
    if ($kept) $url .= '?' . http_build_query($kept);
    return $url;
}

function header_html($active = '', $opts = []) {
    $cfg      = settings_all();
    $siteName = $cfg['siteName']    ?? '汇评测';
    $siteEn   = $cfg['siteNameEn']  ?? 'Huipingce';
    $siteDesc = $cfg['siteDesc']    ?? '';
    $siteKw   = $cfg['siteKeywords']?? '';

    $pt   = $opts['title'] ?? '';
    $title = $pt ? h($pt).' — '.h($siteName) : h($siteName).' · '.h($cfg['siteSlogan'] ?? $siteEn);
    $desc = h($opts['desc'] ?? $siteDesc);
    $kw   = h($opts['kw']   ?? $siteKw);

    $canonical = canonical_url($opts['canonical'] ?? '');
    $css_ver = @filemtime(__DIR__ . '/../assets/css/app.css') ?: time();
    $js_ver  = @filemtime(__DIR__ . '/../assets/js/app.js') ?: time();

    // OpenGraph
    $og = $opts['og'] ?? [];
    $og_type  = $og['type']  ?? 'website';
    $og_img   = $og['image'] ?? '';
    $og_tags  = '<meta property="og:type" content="'.h($og_type).'">'."\n";
    $og_tags .= '<meta property="og:title" content="'.($pt? h($pt):h($siteName)).'">'."\n";
    $og_tags .= '<meta property="og:description" content="'.$desc.'">'."\n";
    $og_tags .= '<meta property="og:url" content="'.h($canonical).'">'."\n";
    $og_tags .= '<meta property="og:site_name" content="'.h($siteName).'">'."\n";
    if ($og_img) $og_tags .= '<meta property="og:image" content="'.h($og_img).'">'."\n";
    $og_tags .= '<meta name="twitter:card" content="'.($og_img?'summary_large_image':'summary').'">';

    $jsonld = '';
    if (!empty($opts['jsonld'])) {
        $jsonld = '<script type="application/ld+json">'.$opts['jsonld'].'</script>';
    }

    // 四栏目导航
    $nav = [
        ['regulators', '/regulators.php', '监管'],
        ['brokers',    '/brokers.php',    '交易商'],
        ['articles',   '/articles.php',   '资讯'],
        ['exposure',   '/exposure.php',   '曝光'],
    ];
    $nav_html = '';
    foreach ($nav as $n) {
        $on = $active === $n[0] ? ' on' : '';
        $nav_html .= '<a class="nav-link'.$on.'" href="'.$n[1].'">'.$n[2].'</a>';
    }

    // 搜索框
    $q = h($_GET['q'] ?? '');
    $search_html = '<form class="hdr-search" action="/search.php" method="get" role="search">'
        . '<input type="text" name="q" value="'.$q.'" placeholder="搜索交易商 / 监管机构" autocomplete="off">'
        . '<button type="submit" aria-label="搜索">🔍</button></form>';

    // 会员区
    $u = current_user();
    if ($u) {
        $av   = user_avatar_html($u, 30);
        $name = h(user_display_name($u));
        $member_html = '<div class="hdr-user" tabindex="0">'
            . '<span class="hdr-user-btn">'.$av.'<span class="hdr-user-name">'.$name.'</span></span>'
            . '<div class="hdr-user-menu">'
            . '<a href="/account.php">个人中心</a>'
            . '<a href="/account.php?tab=reviews">我的测评</a>'
            . '<a href="/exposure-submit.php">我要曝光</a>'
            . '<a href="/logout.php" class="danger">退出登录</a>'
            . '</div></div>';
    } else {
        $member_html = '<a class="hdr-login" href="/login.php">登录</a>'
                     . '<a class="hdr-reg" href="/register.php">注册</a>';
    }

    echo <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<script>document.documentElement.className+=' js';</script>
<meta charset="utf-8">
<meta name="viewport" content="width=1200">
<title>{$title}</title>
<meta name="description" content="{$desc}">
<meta name="keywords" content="{$kw}">
<link rel="canonical" href="{$canonical}">
<meta name="theme-color" content="#f4f6fa">
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
{$og_tags}
{$jsonld}
<link rel="stylesheet" href="/assets/css/app.css?v={$css_ver}">
<script defer src="/assets/js/app.js?v={$js_ver}"></script>
</head>
<body data-page="{$active}">
<header class="hdr">
  <div class="hdr-in">
    <a class="brand" href="/">
      <span class="brand-mark" aria-hidden="true">评</span>
      <span class="brand-zh">{$siteName}</span>
      <span class="brand-en">{$siteEn}</span>
    </a>
    <nav class="nav">{$nav_html}</nav>
    {$search_html}
    <div class="hdr-cta">{$member_html}</div>
  </div>
</header>
<main class="page">
HTML;
}
