<?php
// ═══════════════════════════════════════
// 汇评测 huipingce.com — 数据库 & 核心工具
// ═══════════════════════════════════════

// 本地覆盖（db.local.php 不进 git，用于本地/线上不同配置）
if (is_file(__DIR__ . '/db.local.php')) {
    require __DIR__ . '/db.local.php';
} else {
    define('DB_HOST', '127.0.0.1');
    define('DB_NAME', 'huipingce_com');
    define('DB_USER', 'huipingce_com');
    define('DB_PASS', 'CHANGE_ME');
}

// 采集脚本批量入库鉴权 key
if (!defined('CRON_KEY')) define('CRON_KEY', 'huipingce2026');

function db() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

// ── 配置读取 ──
function setting($key, $default = '') {
    static $cache = [];
    if (!array_key_exists($key, $cache)) {
        try {
            $st = db()->prepare('SELECT v FROM site_settings WHERE k=? LIMIT 1');
            $st->execute([$key]);
            $row = $st->fetch();
            $cache[$key] = $row ? $row['v'] : $default;
        } catch (PDOException $e) {
            $cache[$key] = $default;
        }
    }
    return $cache[$key];
}

function settings_all() {
    static $all = null;
    if ($all === null) {
        $all = [];
        try {
            foreach (db()->query('SELECT k,v FROM site_settings')->fetchAll() as $r) {
                $all[$r['k']] = $r['v'];
            }
        } catch (PDOException $e) {}
    }
    return $all;
}

// ── 通用工具 ──
function h($s)    { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function gen_id() { return bin2hex(random_bytes(8)); }
function now_ts() { return time(); }

// onclick 属性里安全的 JS 字符串
function js($s) { return "'".str_replace(["\\","'"], ["\\\\","\\'"], (string)$s)."'"; }

function json_out($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// 读 JSON 请求体
function json_in() {
    $raw = file_get_contents('php://input');
    $d = json_decode($raw, true);
    return is_array($d) ? $d : [];
}

// slug 生成（评测 URL 友好）
function slugify($s) {
    $s = strtolower(trim((string)$s));
    $s = preg_replace('/[^a-z0-9\x{4e00}-\x{9fa5}]+/u', '-', $s);
    return trim($s, '-');
}

// ── 操作日志 ──
function write_log($action, $detail, $target_id = '') {
    if (session_status() === PHP_SESSION_NONE) @session_start();
    $s = $_SESSION['hpc_user'] ?? null;
    $op    = $s ? $s['name'] : '系统';
    $op_id = $s ? $s['id']   : '';
    try {
        $st = db()->prepare(
            'INSERT INTO logs (id,action,detail,target_id,operator,operator_id,time) VALUES (?,?,?,?,?,?,?)'
        );
        $st->execute([gen_id(), $action, mb_substr($detail,0,500), $target_id, $op, $op_id, now_ts()]);
    } catch (PDOException $e) {}
}

// ── 后台 Auth ──
function get_session() {
    if (session_status() === PHP_SESSION_NONE) @session_start();
    return $_SESSION['hpc_user'] ?? null;
}
function require_login() {
    $s = get_session();
    if (!$s) { header('Location: /admin/login.php'); exit; }
    return $s;
}
function require_superadmin() {
    $s = require_login();
    if ($s['role'] !== 'superadmin') { http_response_code(403); die('权限不足'); }
    return $s;
}

// ── 后台分页 ──
function admin_paginate($page, $total, $per = 20) {
    $pages  = max(1, (int)ceil($total / $per));
    $page   = max(1, min((int)$page, $pages));
    $offset = ($page - 1) * $per;
    return ['page'=>$page,'pages'=>$pages,'offset'=>$offset,'per'=>$per,'total'=>$total];
}
function admin_pager_html($pg, $base_url = '') {
    if ($pg['pages'] <= 1) return '';
    $h = '<div class="pager">';
    $h .= '<span class="pager-total">共 '.$pg['total'].' 条</span>';
    if ($pg['page'] > 1) $h .= '<a href="'.$base_url.'page='.($pg['page']-1).'">‹</a>';
    $start = max(1, $pg['page'] - 3);
    $end   = min($pg['pages'], $pg['page'] + 3);
    for ($i=$start; $i<=$end; $i++) {
        $cls = $i === $pg['page'] ? ' class="on"' : '';
        $h .= '<a'.$cls.' href="'.$base_url.'page='.$i.'">'.$i.'</a>';
    }
    if ($pg['page'] < $pg['pages']) $h .= '<a href="'.$base_url.'page='.($pg['page']+1).'">›</a>';
    return $h.'</div>';
}

require_once __DIR__ . '/helpers.php';
