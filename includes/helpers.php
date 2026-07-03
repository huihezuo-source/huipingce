<?php
// ═══════════════════════════════════════
// 汇评测 — 业务辅助函数
// ═══════════════════════════════════════

// ── 评分 → 语义色 / 评级词（0-10 制，用户评分聚合后用）──
function score_tier($v) {
    $v = (float)$v;
    if ($v >= 8.5) return 'great';
    if ($v >= 7.0) return 'good';
    if ($v >= 5.0) return 'mid';
    return 'bad';
}
function score_label($v) {
    $v = (float)$v;
    if ($v >= 9.0) return '口碑极佳';
    if ($v >= 8.0) return '口碑优秀';
    if ($v >= 7.0) return '口碑良好';
    if ($v >= 6.0) return '褒贬不一';
    if ($v >= 5.0) return '争议较多';
    return '口碑欠佳';
}

// ── 豆瓣式星级词（1-5 星）──
function star_word($stars) {
    $map = [5=>'力荐',4=>'推荐',3=>'还行',2=>'较差',1=>'很差'];
    return $map[(int)$stars] ?? '';
}

// ── 监管评级 → 色板 class / 中文 ──
function grade_class($grade) {
    $map = ['AAA'=>'g-aaa','AA'=>'g-aa','A'=>'g-a','B'=>'g-b','C'=>'g-c'];
    return $map[strtoupper((string)$grade)] ?? 'g-a';
}
function grade_label($grade) {
    $map = ['AAA'=>'顶级监管','AA'=>'优质监管','A'=>'标准监管','B'=>'普通监管','C'=>'离岸监管'];
    return $map[strtoupper((string)$grade)] ?? '标准监管';
}

// ── 实体状态 → 中文 + class ──
function entity_status_label($st) {
    $map = [
        'active'    => ['有效',  's-active'],
        'suspended' => ['暂停',  's-warn'],
        'revoked'   => ['已吊销','s-bad'],
        'expired'   => ['已过期','s-bad'],
    ];
    return $map[$st] ?? ['—','s-mute'];
}

// ── 曝光类型 / 状态 ──
function complaint_types() {
    return ['无法出金','滑点严重','诱导欺诈','虚假宣传','恶意喊单','其他'];
}
function complaint_status_label($st) {
    $map = [
        'pending'    => ['待核实','c-pending'],
        'processing' => ['处理中','c-processing'],
        'resolved'   => ['已解决','c-resolved'],
        'rejected'   => ['未受理','c-rejected'],
    ];
    return $map[$st] ?? ['—','c-pending'];
}

// ══════════════════════════════════════════════════════════════
// 豆瓣式用户评分聚合
// ══════════════════════════════════════════════════════════════

/**
 * 某经纪商用户评分统计
 * 返回 count / avg(1-5) / score10(0-10) / dist[5..1] / pct[5..1]
 */
function broker_rating_stats($broker_id) {
    $out = ['count'=>0,'avg'=>0.0,'score10'=>0.0,'dist'=>[5=>0,4=>0,3=>0,2=>0,1=>0],'pct'=>[5=>0,4=>0,3=>0,2=>0,1=>0]];
    try {
        $st = db()->prepare(
            "SELECT stars, COUNT(*) c FROM broker_reviews
             WHERE broker_id=? AND status='approved' AND stars BETWEEN 1 AND 5
             GROUP BY stars"
        );
        $st->execute([$broker_id]);
        $total = 0; $sum = 0;
        foreach ($st->fetchAll() as $r) {
            $s = (int)$r['stars']; $c = (int)$r['c'];
            $out['dist'][$s] = $c; $total += $c; $sum += $s * $c;
        }
        if ($total > 0) {
            $out['count']   = $total;
            $out['avg']     = round($sum / $total, 2);
            $out['score10'] = round($sum / $total * 2, 1);
            foreach ($out['dist'] as $s => $c) $out['pct'][$s] = round($c * 100 / $total);
        }
    } catch (PDOException $e) {}
    return $out;
}

// 重算并回写 brokers 评分缓存
function recompute_broker_rating($broker_id) {
    $s = broker_rating_stats($broker_id);
    try {
        $st = db()->prepare('UPDATE brokers SET user_rating_avg=?, user_rating_count=? WHERE id=?');
        $st->execute([$s['count'] ? $s['avg'] : null, $s['count'], $broker_id]);
    } catch (PDOException $e) {}
    return $s;
}

// 重算某经纪商曝光数缓存
function recompute_broker_complaints($broker_id) {
    if (!$broker_id) return;
    try {
        $st = db()->prepare("SELECT COUNT(*) FROM complaints WHERE broker_id=? AND status<>'rejected'");
        $st->execute([$broker_id]);
        $n = (int)$st->fetchColumn();
        db()->prepare('UPDATE brokers SET complaint_count=? WHERE id=?')->execute([$n, $broker_id]);
    } catch (PDOException $e) {}
}

// ══════════════════════════════════════════════════════════════
// 品牌名关键词提取 + 受监管实体自动匹配（沿用天眼逻辑）
// ══════════════════════════════════════════════════════════════
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
    try {
        $sql = 'SELECT id FROM reg_entities WHERE ('.implode(' OR ', $parts).') LIMIT 200';
        $st = db()->prepare($sql);
        $st->execute($vals);
        return array_column($st->fetchAll(), 'id');
    } catch (PDOException $e) { return []; }
}

/**
 * 取某经纪商关联的受监管实体（手动 map 为准；空则回退自动匹配）
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

// 某经纪商监管概览：最高等级 + 牌照数（用于列表卡片标签）
function broker_reg_summary($broker_id, $broker_name = '') {
    $ents = broker_entities($broker_id, $broker_name);
    $order = ['AAA'=>5,'AA'=>4,'A'=>3,'B'=>2,'C'=>1];
    $best = ''; $bestv = 0; $active = 0;
    foreach ($ents as $e) {
        if ($e['status'] === 'active') $active++;
        $g = strtoupper((string)$e['reg_grade']);
        if (($order[$g] ?? 0) > $bestv) { $bestv = $order[$g]; $best = $g; }
    }
    return ['count'=>count($ents),'active'=>$active,'best_grade'=>$best,'entities'=>$ents];
}

// ── 资讯：已发布筛选片段 ──
function published_where($alias = '') {
    $a = $alias ? $alias.'.' : '';
    return "{$a}status='published' AND ({$a}publish_at IS NULL OR {$a}publish_at <= ".now_ts().")";
}

function article_cats() {
    static $c = null;
    if ($c === null) {
        try {
            $c = db()->query('SELECT * FROM article_cats ORDER BY sort_order ASC, id ASC')->fetchAll();
        } catch (PDOException $e) { $c = []; }
    }
    return $c;
}

// JSON 字段安全解码为数组
function json_arr($raw) {
    $d = json_decode((string)$raw, true);
    return is_array($d) ? $d : [];
}

// 相对时间
function time_ago($ts) {
    $ts = (int)$ts;
    if ($ts <= 0) return '';
    $d = time() - $ts;
    if ($d < 60)      return '刚刚';
    if ($d < 3600)    return floor($d/60).' 分钟前';
    if ($d < 86400)   return floor($d/3600).' 小时前';
    if ($d < 2592000) return floor($d/86400).' 天前';
    return date('Y-m-d', $ts);
}

// 只读星标（0-5，支持小数，用 fg 裁切实现半星）
function stars_html($v5, $cls = '') {
    $v5  = max(0, min(5, (float)$v5));
    $pct = $v5 / 5 * 100;
    $full = '★★★★★';
    return '<span class="stars '.$cls.'"><span class="s-bg">'.$full.'</span>'
         . '<span class="s-fg" style="width:'.$pct.'%">'.$full.'</span></span>';
}

// logo：有图出图，无图出首字占位
function logo_html($logo, $name, $cls) {
    if (trim((string)$logo) !== '') {
        return '<img class="'.$cls.'" src="'.h($logo).'" alt="'.h($name).'">';
    }
    $ch = mb_substr(preg_replace('/\s+/u', '', (string)$name), 0, 1, 'UTF-8');
    return '<div class="'.$cls.' ph">'.h($ch).'</div>';
}

// 数字缩写（1.2k / 3.4w）
function num_short($n) {
    $n = (int)$n;
    if ($n >= 10000) return round($n/10000, 1).'w';
    if ($n >= 1000)  return round($n/1000, 1).'k';
    return (string)$n;
}
