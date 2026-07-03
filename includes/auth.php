<?php
// ═══════════════════════════════════════
// 汇评测 — 前台会员认证（与后台 hpc_user 分离，键 hpc_member）
// ═══════════════════════════════════════
require_once __DIR__ . '/db.php';

function member_session_start() {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
}

// 当前登录会员（已 ban 视为未登录）；返回 null 或用户数组
function current_user() {
    static $checked = false; static $user = null;
    if ($checked) return $user;
    $checked = true;
    member_session_start();
    $id = $_SESSION['hpc_member'] ?? null;
    if (!$id) return $user = null;
    try {
        $st = db()->prepare('SELECT * FROM site_users WHERE id=? LIMIT 1');
        $st->execute([$id]);
        $u = $st->fetch();
        if ($u && $u['status'] === 'active') return $user = $u;
    } catch (PDOException $e) {}
    unset($_SESSION['hpc_member']);
    return $user = null;
}

function require_user($redirect = '') {
    $u = current_user();
    if (!$u) {
        $to = $redirect ?: ($_SERVER['REQUEST_URI'] ?? '/');
        header('Location: /login.php?next=' . urlencode($to));
        exit;
    }
    return $u;
}

function user_display_name($u) {
    if (!$u) return '匿名用户';
    $n = trim((string)($u['nickname'] ?? ''));
    return $n !== '' ? $n : ($u['username'] ?? '匿名用户');
}

function user_avatar_html($u, $size = 40) {
    $name = user_display_name($u);
    $av   = trim((string)($u['avatar'] ?? ''));
    $s    = (int)$size;
    if ($av !== '') {
        return '<img class="avatar" src="'.h($av).'" alt="'.h($name).'" style="width:'.$s.'px;height:'.$s.'px">';
    }
    // 无头像：取首字 + 稳定色
    $ch  = mb_substr($name, 0, 1, 'UTF-8');
    $hue = crc32($name) % 360;
    return '<span class="avatar avatar-txt" style="width:'.$s.'px;height:'.$s.'px;line-height:'.$s.'px;'
         . 'font-size:'.round($s*0.45).'px;background:hsl('.$hue.',55%,55%)">'.h($ch).'</span>';
}

// ── 注册 ──（返回 [ok, msgOrUser]）
function user_register($username, $password, $email = '', $nickname = '') {
    $username = trim($username);
    $email    = trim($email);
    $nickname = trim($nickname) ?: $username;

    if (!preg_match('/^[A-Za-z0-9_\x{4e00}-\x{9fa5}]{2,20}$/u', $username))
        return [false, '用户名需为 2-20 位字母/数字/下划线/中文'];
    if (mb_strlen($password) < 6)
        return [false, '密码至少 6 位'];
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))
        return [false, '邮箱格式不正确'];

    try {
        $st = db()->prepare('SELECT id FROM site_users WHERE username=? LIMIT 1');
        $st->execute([$username]);
        if ($st->fetch()) return [false, '该用户名已被注册'];

        $id = 'u_' . gen_id();
        $st = db()->prepare(
            'INSERT INTO site_users (id,username,email,pass_hash,nickname,status,created_at,ip)
             VALUES (?,?,?,?,?,?,?,?)'
        );
        $st->execute([
            $id, $username, $email,
            password_hash($password, PASSWORD_DEFAULT),
            $nickname, 'active', now_ts(), client_ip()
        ]);
        $u = ['id'=>$id,'username'=>$username,'nickname'=>$nickname,'avatar'=>'','status'=>'active'];
        return [true, $u];
    } catch (PDOException $e) {
        return [false, '注册失败，请稍后再试'];
    }
}

// ── 登录 ──（返回 [ok, msgOrUser]）
function user_login($username, $password) {
    $username = trim($username);
    try {
        $st = db()->prepare('SELECT * FROM site_users WHERE username=? LIMIT 1');
        $st->execute([$username]);
        $u = $st->fetch();
        if (!$u || !password_verify($password, $u['pass_hash']))
            return [false, '用户名或密码错误'];
        if ($u['status'] !== 'active')
            return [false, '该账号已被封禁'];
        member_session_start();
        $_SESSION['hpc_member'] = $u['id'];
        db()->prepare('UPDATE site_users SET last_login=?, ip=? WHERE id=?')
            ->execute([now_ts(), client_ip(), $u['id']]);
        return [true, $u];
    } catch (PDOException $e) {
        return [false, '登录失败，请稍后再试'];
    }
}

function user_logout() {
    member_session_start();
    unset($_SESSION['hpc_member']);
}

function client_ip() {
    return substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45);
}

// 登录后 next 跳转地址白名单（防开放重定向）
function safe_next($next, $fallback = '/') {
    $next = (string)$next;
    if ($next !== '' && $next[0] === '/' && strpos($next, '//') !== 0) return $next;
    return $fallback;
}
