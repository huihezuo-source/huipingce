# 汇评测 (Huipingce) — 项目指南

## 项目概述
外汇经纪商**监管信息 + 用户测评**平台。结构仿**外汇天眼**（监管/交易商/资讯/曝光四栏目 + 关联），
界面与评分交互仿**豆瓣电影**（用户自由打分 1-5 星 + 写测评 + 评分分布直方图）。
区别于姊妹站「汇合作」(返佣聚合)。**PC 端优先**（暂不做移动端）。

线上：https://www.huipingce.com ｜ 服务器：`/www/wwwroot/huipingce.com`（宝塔 PHP 8.2 + MySQL）
GitHub：`huihezuo-source/huipingce`（公开）

## 四大栏目（仿天眼）
1. **监管** — 监管机构 + 各机构下「受监管实体公司」名录（栏目核心数据）
2. **交易商** — 经纪商品牌 + 关联受监管实体 + 豆瓣式用户评分块 + 相关曝光/资讯
3. **资讯** — 行业动态/交易商/监管快讯/曝光维权/原创评测（分类文章）
4. **曝光** — 用户提交的维权/投诉（无法出金/滑点/诱导欺诈…），后台核实后公开

## 评分体系（重要）
- **平台官方权威评分（brokers.score）本期留空占位**，等后续定「每家该按什么打多少分」的规则。
- **用户评分（豆瓣式）已上线**：`broker_reviews` 存 1-5 星 + 测评正文，每人每交易商一条（可改）。
  - 详情页大分 = 平均星级 × 2（0-10 制）+ 星标 + 评分分布直方图（力荐/推荐/还行/较差/很差）。
  - 聚合缓存回写 `brokers.user_rating_avg` / `user_rating_count`。
- 测评审核开关：`site_settings.reviewModeration`（0=直接展示；1=带文字的测评进后台审核队列）。

## 技术栈
- 后端：PHP 8.2（PDO，无框架）/ MySQL utf8mb4
- 前端：原生 HTML/CSS/JS。**豆瓣风设计系统**（绿主色 #0f9d58 + 琥珀评分 #ff9a1e + 卡片式）
- Web：Apache + .htaccess ｜ 采集：scripts/ 下 PHP 采集器 → entities_bulk_insert API 入库

## 目录结构
```
includes/   db.php(PDO+工具) / helpers.php(实体匹配+评分聚合) / auth.php(前台会员) / header.php / footer.php
assets/     css/app.css(豆瓣风前台) css/admin.css / js/app.js(星标打分/有用投票) js/admin.js
index.php                         首页（Hero+口碑榜+监管+资讯+曝光）
regulators.php / regulator-detail.php     监管机构 + 受监管实体名录
brokers.php / broker-detail.php           交易商列表 + 详情(豆瓣评分块+监管牌照+测评+曝光)
articles.php / article-detail.php         资讯
exposure.php / exposure-detail.php / exposure-submit.php   曝光台 + 提交
login.php / register.php / logout.php / account.php        前台会员
search.php                        全局搜索（交易商/监管/资讯）
api/        rate.php(打分测评) review_useful.php(有用投票)   ← 前台 AJAX
admin/      后台 CMS：index/brokers/regulators/reg_entities/articles/complaints/reviews(测评审核)/users/settings + api/
scripts/    采集器（collect_fca.php / collect_asic.php / lib.php）
sql/        schema.sql(建表+种子) / seed_sample.sql(演示数据 source=sample，上线前替换)
```

## 数据库核心表
- `regulators` — 监管机构（grade AAA..C / trust_score / entity_count 缓存）
- `reg_entities` — 受监管实体公司（regulator_id + license_no UNIQUE 去重）
- `brokers` — 交易商（score 官方分**暂缓留空** / user_rating_avg+count 用户分缓存 / complaint_count）
- `broker_entity_map` — 品牌↔实体关联（auto_match 建议 + 手动 link）
- `article_cats` / `articles` — 资讯分类 + 文章
- `complaints` — 曝光（type/status pending→processing→resolved→rejected / admin_reply）
- `site_users` — 前台会员（password_hash bcrypt）
- `broker_reviews` — 用户评分+测评（UNIQUE broker_id+user_id / status 审核）
- `review_votes` — 测评「有用」投票去重
- `editors` / `site_settings` / `logs`

## 关键约定
- DB 配置：`includes/db.php`，本地/线上用 `includes/db.local.php`（不进 git）覆盖
- 前台会员 session 键 `hpc_member`；后台 session 键 `hpc_user`（分离）
- 采集鉴权：`CRON_KEY`（默认 `huipingce2026`）→ `admin/api/entities_bulk_insert.php`
- 后台默认账号：`admin` / `huipingce2026`（SHA256，**上线后请改**）
- 演示会员密码统一 `demo1234`（seed_sample.sql，仅演示）
- SEO：`canonical_url()` 强制 https+www + 白名单参数；broker-detail 输出 Organization+AggregateRating JSON-LD

## 本地开发
```bash
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS huipingce_com CHARACTER SET utf8mb4"
mysql -uroot huipingce_com < sql/schema.sql
mysql -uroot huipingce_com < sql/seed_sample.sql   # 可选：演示数据（20 交易商+评分+资讯+曝光）
php -S 127.0.0.1:8899 -t .
# db.local.php 指向 root@127.0.0.1 / huipingce_com
```

## 待办 / 后续
- 官方权威评分算法（豪哥定规则后实现，填 brokers.score）
- 真实监管数据采集（FCA/ASIC key → scripts/），替换 seed_sample.sql 演示数据
- 移动端（PC 完成后再做）

## 注意
- Logo 用 base64 存 DB（regulators.logo / brokers.logo）
- 所有 DB 操作用 PDO 预处理；操作审计写 logs 表
- **合规**：曝光/测评为用户个人观点，前台已加免责声明；官方分未定前不对交易商下平台结论
