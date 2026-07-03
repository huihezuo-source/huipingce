# 汇评测 · 受监管实体采集器

全新独立采集各监管机构的「受监管实体公司」，入库 `reg_entities`，再由后台聚合到经纪商品牌。

## 配置

```bash
cp scripts/secret.php.example scripts/secret.php
# 编辑 secret.php，填 FCA API 凭证 + 入库地址 + cron_key
```

入库地址 `ingest_url` 指向 `admin/api/entities_bulk_insert.php`（用 `cron_key` 鉴权，无需登录）。
本地默认 `http://127.0.0.1:8800/...`，线上改成 `https://www.huipingce.com/...`。

## FCA（英国）— API，最规范，优先

1. 免费注册取 API key：https://register.fca.org.uk/Developer/s/
2. 维护品牌清单 `scripts/fca_brokers.txt`（每行一个品牌名）
3. 运行：

```bash
php scripts/collect_fca.php names scripts/fca_brokers.txt --dry   # 先 dry 看结果
php scripts/collect_fca.php names scripts/fca_brokers.txt         # 入库
# 或按已知 FRN 直接抓：
php scripts/collect_fca.php frn 122169 684312
```

## ASIC（澳大利亚）— 公开 CSV

1. 下载 AFS Licensees 数据集 CSV：https://data.gov.au （搜 "ASIC AFS Licensees"）
2. 放到 `scripts/_tmp/asic_afsl.csv`
3. 运行（自动过滤 derivatives / foreign exchange 相关持牌人）：

```bash
php scripts/collect_asic.php scripts/_tmp/asic_afsl.csv --dry
php scripts/collect_asic.php scripts/_tmp/asic_afsl.csv
```

## 入库后

1. 后台 → 受监管实体：核对采集结果
2. 后台 → 经纪商 → 编辑：用「自动匹配建议」把实体关联到品牌（或手动搜索关联）
3. 去重规则：`UNIQUE(regulator_id, license_no)`，重复运行只更新不重复插入

## 扩展新监管来源

照 `collect_fca.php` / `collect_asic.php` 的模式新增 `collect_<reg>.php`：
拉取 → 清洗成实体数组（含 `regulator_id`）→ `ingest_entities($entities)`。
后续可加 SFC（香港）、CySEC（塞浦路斯）、NFA（美国）等。

## 定时（线上 cron 示例）

```cron
# 每周一 03:00 跑 FCA 采集
0 3 * * 1 cd /www/wwwroot/huipingce.com && php scripts/collect_fca.php names scripts/fca_brokers.txt >> /tmp/hpc_fca.log 2>&1
```
