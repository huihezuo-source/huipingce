# 汇评测 Huipingce

外汇经纪商**评测**平台 — 为外汇用户提供经纪商全面评测，帮你选对经纪商。

🌐 https://www.huipingce.com

## 三大栏目
- **监管机构** — 全球监管机构 + 其下受监管实体公司名录
- **外汇经纪商** — 品牌 + 关联各监管下的受监管实体 + 综合评分
- **评测** — IGN 风格深度评测，多维评分（总分 + 6 维度），每日更新

## 技术栈
PHP 8.2 + MySQL (PDO/utf8mb4) · 原生前端 · Apache · 浅色现代设计系统

## 快速开始（本地）
```bash
# 1. 建库导入结构
mysql -uroot -e "CREATE DATABASE huipingce_com DEFAULT CHARSET utf8mb4"
mysql -uroot huipingce_com < sql/schema.sql

# 2. 本地 DB 配置（不进 git）
cp includes/db.local.php.example includes/db.local.php   # 按需新建并填配置

# 3. 起服务
php -S 127.0.0.1:8800
# 前台 http://127.0.0.1:8800/  ｜ 后台 http://127.0.0.1:8800/admin/  (admin / huipingce2026)
```

## 部署（宝塔）
```bash
cd /www/wwwroot/huipingce.com && git pull
# 首次：导入 sql/schema.sql；配置 includes/db.local.php 正式库；改后台默认密码
```

## 采集
见 [scripts/README.md](scripts/README.md)。

## 目录
见 [CLAUDE.md](CLAUDE.md)。
