# 汇评测 (Huipingce) — 项目指南

## 项目概述
外汇经纪商**评测**平台，为外汇用户提供经纪商全面评测，帮用户更好地选择经纪商。
区别于姊妹站「汇合作」(返佣聚合)，汇评测专注**评测 + 监管核验**。

线上：https://www.huipingce.com ｜ 服务器：`/www/wwwroot/huipingce.com`（宝塔 PHP 8.2 + MySQL）

## 三大栏目
1. **监管机构** — 展示各监管机构下「受监管实体公司」信息（栏目核心数据）
2. **外汇经纪商** — 品牌名 + 关联各监管机构下的受监管实体
3. **评测** — IGN 风格评测，每日更新，多维评分（总分 + 6 维度雷达/进度条）

## 技术栈
- 后端：PHP 8.2（PDO，无框架）/ MySQL utf8mb4
- 前端：原生 HTML/CSS/JS（浅色现代设计系统，科技感微交互）
- Web：Apache + .htaccess
- 采集：scripts/ 下 PHP 采集器 → entities_bulk_insert API 入库

## 目录结构
```
includes/      db.php(PDO+工具) / helpers.php(匹配+评分) / header.php(SEO) / footer.php
assets/        css/app.css(前台设计系统) css/admin.css / js/app.js(微交互) js/admin.js
index.php      首页
regulators.php / regulator-detail.php   监管机构 + 受监管实体名录
brokers.php / broker-detail.php         经纪商 + 关联实体 + 评测预览
reviews.php / review-detail.php         评测列表 + 详情(多维评分+Schema.org Review)
admin/         后台 CMS（layout/login + reviews/brokers/regulators/entities/settings + api/）
scripts/       采集器（collect_fca.php / collect_asic.php / lib.php）+ README
sql/schema.sql 建表 + 初始数据（8 监管种子 + 默认超管）
sitemap.php / robots.txt / .htaccess
```

## 数据库核心表
- `regulators` — 监管机构（grade AAA..C / trust_score / entity_count 缓存）
- `reg_entities` — 受监管实体公司（regulator_id + license_no，UNIQUE 去重）
- `brokers` — 经纪商品牌（score 综合评分缓存）
- `broker_entity_map` — 品牌↔实体关联（auto_match 建议 + 手动 link）
- `reviews` — 评测（overall_score / verdict / pros/cons JSON / status / publish_at）
- `review_scores` — 多维评分（review_id × dimension × score，加权聚合总分）
- `editors` / `site_settings` / `logs`

## 关键约定
- DB 配置：`includes/db.php`，本地用 `includes/db.local.php`（不进 git）覆盖
- 采集鉴权：`CRON_KEY`（默认 `huipingce2026`）→ `admin/api/entities_bulk_insert.php`
- 后台默认账号：`admin` / `huipingce2026`（SHA256，首次登录后请改）
- 评分维度：site_settings.reviewDimensions（默认 6 维：监管安全/交易成本/出入金/平台体验/客户服务/产品种类）
- 总分聚合：`aggregate_review_score()` 各维度加权均值；评测发布时同步 brokers.score 缓存
- SEO：`canonical_url()` 强制 https+www + 白名单参数；review-detail 输出 Schema.org Review

## 评测工作流（每日更新）
后台 → 评测 → 新建：填标题/verdict/摘要 + 富文本正文 + 拖 6 维评分滑块（总分自动聚合）
+ pros/cons + 选评测对象经纪商 → 发布。前台 review-detail 渲染雷达/进度条 + Schema。

## 采集工作流（收录经纪商/实体）
见 `scripts/README.md`。FCA(API) / ASIC(CSV) → 入库 reg_entities → 后台经纪商编辑里
用「自动匹配建议」关联实体到品牌。

## 本地开发
```bash
php -S 127.0.0.1:8800 -t /Users/zhangxiaohua/Desktop/huipingce
mysql -uroot huipingce_com < sql/schema.sql
# 可选样例数据：mysql -uroot huipingce_com < scripts/seed_dev.sql（仅本地，不上线）
```

## 注意
- Logo 用 base64 存 DB（regulators.logo / brokers.logo）
- 所有 DB 操作用 PDO 预处理
- 操作审计写 logs 表
