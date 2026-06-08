<?php
// ═══════════════════════════════════════
// 汇评测 — 业务辅助函数
// ═══════════════════════════════════════

// ── 评分维度（IGN 多维）──
function review_dimensions() {
    static $dims = null;
    if ($dims === null) {
        $raw = setting('reviewDimensions', '监管安全,交易成本,出入金,平台体验,客户服务,产品种类');
        $dims = array_values(array_filter(array_map('trim', explode(',', $raw))));
    }
    return $dims;
}

// 各维度加权聚合为总分（0-10），无评分返回 null
function aggregate_review_score(array $scores) {
    $sum = 0; $wsum = 0;
    foreach ($scores as $s) {
        $w = isset($s['weight']) ? (float)$s['weight'] : 1.0;
        if ($w <= 0) $w = 1.0;
        $sum  += (float)$s['score'] * $w;
        $wsum += $w;
    }
    if ($wsum <= 0) return null;
    return round($sum / $wsum, 1);
}

// 评分 → 语义色（CSS class 后缀）
function score_tier($v) {
    $v = (float)$v;
    if ($v >= 8.5) return 'great';
    if ($v >= 7.0) return 'good';
    if ($v >= 5.0) return 'mid';
    return 'bad';
}
// 评分 → 中文评级词
function score_label($v) {
    $v = (float)$v;
    if ($v >= 9.0) return '卓越';
    if ($v >= 8.0) return '优秀';
    if ($v >= 7.0) return '良好';
    if ($v >= 6.0) return '尚可';
    if ($v >= 5.0) return '一般';
    return '欠佳';
}

// 监管评级 → 色板 class
function grade_class($grade) {
    $map = ['AAA'=>'g-aaa','AA'=>'g-aa','A'=>'g-a','B'=>'g-b','C'=>'g-c'];
    return $map[strtoupper((string)$grade)] ?? 'g-a';
}
function grade_label($grade) {
    $map = ['AAA'=>'顶级监管','AA'=>'优质监管','A'=>'标准监管','B'=>'普通监管','C'=>'离岸监管'];
    return $map[strtoupper((string)$grade)] ?? '标准监管';
}

// 实体状态 → 中文 + class
function entity_status_label($st) {
    $map = [
        'active'    => ['有效',  's-active'],
        'suspended' => ['暂停',  's-warn'],
        'revoked'   => ['已吊销','s-bad'],
        'expired'   => ['已过期','s-bad'],
    ];
    return $map[$st] ?? ['—','s-mute'];
}

// ── 品牌名关键词提取（英文主体 + 中文主体）──
function extract_broker_keywords($name) {
    $keywords = [];
    if (preg_match('/[A-Za-z][A-Za-z0-9 \-\.\']{0,40}/', $name, $m)) {
        $kw = trim($m[0]);
        $kw = preg_replace('/\s+(Ltd|Limited|Inc|Corp|Corporation|Co\.?|PLC|LLC|Pty)\s*$/i', '', $kw);
        $kw = trim($kw);
        if (mb_strlen($kw) >= 2) $keywords[] = $kw;
    }
    if (preg_match_all('/[\x{4e00}-\x{9fa5}]{2,}/u', $name, $ms)) {
        foreach ($ms[0] as $cn) if (!in_array($cn, $keywords, true)) $keywords[] = $cn;
    }
    return $keywords;
}

/**
 * 自动匹配：按品牌名在 reg_entities 里找候选受监管实体 id
 * 英文用 REGEXP 词边界，避免 "EC Markets" 误命中 "HANTEC MARKETS"
 */
function auto_match_entity_ids($broker_name) {
    $broker_name = trim((string)$broker_name);
    if ($broker_name === '') return [];
    $keywords = extract_broker_keywords($broker_name);
    if (empty($keywords)) return [];

    $regex_esc = function($s){ return preg_replace('/([\\\\.+*?\[\]\(\)\{\}^$|])/', '\\\\$1', $s); };
    $parts = []; $vals = [];
    foreach ($keywords as $kw) {
        if (preg_match('/[\x{4e00}-\x{9fff}]/u', $kw)) {
            $parts[] = '(name LIKE ? OR name_local LIKE ?)';
            $vals[] = '%'.$kw.'%'; $vals[] = '%'.$kw.'%';
        } else {
            $parts[] = '(name REGEXP ? OR name_local REGEXP ?)';
            $p = '(^|[^A-Za-z0-9])'.$regex_esc($kw).'([^A-Za-z0-9]|$)';
            $vals[] = $p; $vals[] = $p;
        }
    }
    $sql = 'SELECT id FROM reg_entities WHERE ('.implode(' OR ', $parts).') LIMIT 200';
    $st = db()->prepare($sql);
    $st->execute($vals);
    return array_column($st->fetchAll(), 'id');
}

/**
 * 取某经纪商关联的受监管实体（手动 map 为准；空则回退自动匹配）
 * 返回带监管机构信息的实体列表
 */
function broker_entities($broker_id, $broker_name = '') {
    $ids = [];
    try {
        $st = db()->prepare('SELECT entity_id FROM broker_entity_map WHERE broker_id=?');
        $st->execute([$broker_id]);
        $ids = array_column($st->fetchAll(), 'entity_id');
    } catch (PDOException $e) {}

    if (empty($ids) && $broker_name !== '') {
        $ids = auto_match_entity_ids($broker_name);
    }
    if (empty($ids)) return [];

    $ph = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT e.*, r.name AS reg_name, r.full_name AS reg_full, r.flag AS reg_flag,
                   r.grade AS reg_grade, r.country AS reg_country, r.sort_order AS reg_sort
            FROM reg_entities e
            LEFT JOIN regulators r ON e.regulator_id = r.id
            WHERE e.id IN ($ph)
            ORDER BY r.sort_order ASC, e.status ASC, e.name ASC";
    $st = db()->prepare($sql);
    $st->execute($ids);
    return $st->fetchAll();
}

// 取一篇评测的多维评分
function review_scores($review_id) {
    $st = db()->prepare('SELECT * FROM review_scores WHERE review_id=? ORDER BY sort_order ASC, id ASC');
    $st->execute([$review_id]);
    return $st->fetchAll();
}

// 取某经纪商最新已发布评测
function broker_latest_review($broker_id) {
    if (!$broker_id) return null;
    $st = db()->prepare(
        "SELECT * FROM reviews WHERE broker_id=? AND status='published'
         AND (publish_at IS NULL OR publish_at <= ?)
         ORDER BY COALESCE(publish_at, created_at) DESC LIMIT 1"
    );
    $st->execute([$broker_id, now_ts()]);
    return $st->fetch() ?: null;
}

// 已发布筛选条件片段（评测列表通用）
function published_where() {
    return "status='published' AND (publish_at IS NULL OR publish_at <= ".now_ts().")";
}

// JSON 字段安全解码为数组
function json_arr($raw) {
    $d = json_decode((string)$raw, true);
    return is_array($d) ? $d : [];
}
