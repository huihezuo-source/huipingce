# 汇评测 Huipingce

外汇经纪商**监管信息 + 用户测评**平台 —— 看清每一家交易商。
结构仿外汇天眼（监管 / 交易商 / 资讯 / 曝光），界面与评分仿豆瓣（用户打分 + 写测评 + 评分分布）。

🌐 https://www.huipingce.com

## 四大栏目
- **监管** — 全球监管机构 + 其下受监管实体公司名录
- **交易商** — 品牌 + 关联受监管实体 + 豆瓣式用户评分 + 相关曝光/资讯
- **资讯** — 行业动态 / 交易商 / 监管快讯 / 曝光维权 / 原创评测
- **曝光** — 用户维权投诉，后台核实后公开

## 评分说明
- **平台官方权威评分**：本期**留空占位**，等后续定规则。
- **用户评分**（已上线，豆瓣式）：注册会员给交易商打 1-5 星 + 写测评，详情页展示 0-10 大分 + 星标 + 评分分布直方图。

## 技术栈
PHP 8.2 + MySQL (PDO/utf8mb4) · 原生前端 · Apache · 豆瓣风设计系统（绿+琥珀）· **PC 优先**

## 快速开始（本地）
```bash
# 1. 建库导入结构
mysql -uroot -e "CREATE DATABASE huipingce_com DEFAULT CHARSET utf8mb4"
mysql -uroot huipingce_com < sql/schema.sql
mysql -uroot huipingce_com < sql/seed_sample.sql   # 可选：演示数据（20 交易商+评分+资讯+曝光）

# 2. 本地 DB 配置（不进 git）
#    新建 includes/db.local.php，填 DB_HOST/DB_NAME/DB_USER/DB_PASS

# 3. 起服务
php -S 127.0.0.1:8899 -t .
# 前台 http://127.0.0.1:8899/  ｜ 后台 /admin/  (admin / huipingce2026)
# 演示会员密码统一 demo1234
```

## 部署（宝塔）
```bash
cd /www/wwwroot/huipingce.com && git pull
# 首次：导入 sql/schema.sql（可选 seed_sample.sql）；配置 includes/db.local.php 正式库；改后台默认密码
```

## 采集
见 [scripts/README.md](scripts/README.md)。真实监管数据入库后请替换 seed_sample.sql 演示数据。

## 目录 & 约定
见 [CLAUDE.md](CLAUDE.md)。
