<?php
/**
 * 汇评测 — 公共头部
 * 调用：header_html($active, ['title'=>..,'desc'=>..,'kw'=>..,'og'=>[..],'jsonld'=>'..','canonical'=>'..'])
 */

function canonical_url(string $override = ''): string {
    $base = rtrim(setting('siteUrl', 'https://www.huipingce.com'), '/');
    if ($override !== '') {
        if (preg_match('#^https?://#i', $override)) return $override;
        return $base . $override;
    }
    $script = $_SERVER['SCRIPT_NAME'] ?? '/';
    if ($script === '/index.php' || $script === '/') return $base . '/';

    $whitelist = ['id','slug','cat','broker','regulator','tag'];
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

    // 三栏目导航
    $nav = [
        ['regulators', '/regulators.php', '监管机构'],
        ['brokers',    '/brokers.php',    '外汇经纪商'],
        ['reviews',    '/reviews.php',    '评测'],
    ];
    $nav_pc = $nav_mb = '';
    foreach ($nav as $n) {
        $on = $active === $n[0] ? ' on' : '';
        $nav_pc .= '<a class="nav-link'.$on.'" href="'.$n[1].'">'.$n[2].'</a>';
        $nav_mb .= '<a class="m-nav-link'.$on.'" href="'.$n[1].'">'.$n[2].'</a>';
    }

    echo <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<script>document.documentElement.className+=' js';</script>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$title}</title>
<meta name="description" content="{$desc}">
<meta name="keywords" content="{$kw}">
<link rel="canonical" href="{$canonical}">
<meta name="theme-color" content="#3b5bff">
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
{$og_tags}
{$jsonld}
<link rel="stylesheet" href="/assets/css/app.css?v={$css_ver}">
<script defer src="/assets/js/app.js?v={$js_ver}"></script>
</head>
<body data-page="{$active}">
<header class="hdr" id="hdr">
  <div class="hdr-in">
    <a class="brand" href="/">
      <span class="brand-mark" aria-hidden="true"></span>
      <span class="brand-zh">{$siteName}</span>
      <span class="brand-en">{$siteEn}</span>
    </a>
    <nav class="nav">{$nav_pc}</nav>
    <div class="hdr-cta">
      <button class="m-btn" id="mBtn" aria-label="菜单" onclick="hpcToggleMenu()"><span></span><span></span><span></span></button>
    </div>
  </div>
  <div class="m-nav" id="mNav"><div class="m-nav-in">{$nav_mb}</div></div>
</header>
<main class="page">
HTML;
}
