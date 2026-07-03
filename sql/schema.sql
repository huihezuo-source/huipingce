-- ════════════════════════════════════════════════════════════════
-- 汇评测 huipingce.com — 数据库结构（全新空库）v2.0.0
-- 结构：仿外汇天眼(监管/交易商/资讯/曝光) + 豆瓣式用户评分
-- PHP 8.2 + MySQL 5.7+/8.0  utf8mb4
-- 注意：不带 USE / CREATE DATABASE（宝塔自处理）
-- 平台「官方评分」列(brokers.score)本期留空占位，等后续定规则
-- ════════════════════════════════════════════════════════════════
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ══════════════════════════════════════════════════════════════
-- 栏目1 · 监管
-- ══════════════════════════════════════════════════════════════

-- ── 监管机构主表 ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `regulators` (
  `id`          VARCHAR(40)  NOT NULL,
  `name`        VARCHAR(40)  NOT NULL COMMENT '简称 FCA/ASIC',
  `full_name`   VARCHAR(160) DEFAULT '' COMMENT '全称',
  `country`     VARCHAR(40)  DEFAULT '' COMMENT '国家/地区中文',
  `flag`        VARCHAR(10)  DEFAULT '' COMMENT 'emoji 国旗',
  `region`      VARCHAR(40)  DEFAULT '' COMMENT '欧洲/亚太/美洲/离岸',
  `grade`       ENUM('AAA','AA','A','B','C') DEFAULT 'A' COMMENT '监管等级',
  `trust_score` TINYINT      DEFAULT 0 COMMENT '信任分 0-100',
  `established` SMALLINT     DEFAULT NULL,
  `gov_type`    VARCHAR(30)  DEFAULT '' COMMENT '政府监管/自律组织',
  `logo`        MEDIUMTEXT   COMMENT 'base64',
  `description` TEXT,
  `website`     VARCHAR(255) DEFAULT '',
  `query_url`   VARCHAR(255) DEFAULT '' COMMENT '牌照查询入口',
  `entity_count` INT         DEFAULT 0 COMMENT '缓存：受监管实体数',
  `sort_order`  SMALLINT     DEFAULT 0,
  `created_at`  INT          NOT NULL DEFAULT 0,
  `updated_at`  INT          DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_grade` (`grade`),
  KEY `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 受监管实体公司 ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `reg_entities` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(220) NOT NULL COMMENT '官方注册公司名',
  `name_local`   VARCHAR(220) DEFAULT '' COMMENT '本地语言名',
  `regulator_id` VARCHAR(40)  NOT NULL,
  `license_no`   VARCHAR(120) DEFAULT '' COMMENT '注册/牌照号',
  `license_type` VARCHAR(300) DEFAULT '' COMMENT '权限范围',
  `client_type`  VARCHAR(80)  DEFAULT '' COMMENT '零售/专业/机构',
  `status`       ENUM('active','suspended','revoked','expired') DEFAULT 'active',
  `reg_date`     DATE         DEFAULT NULL,
  `country`      VARCHAR(80)  DEFAULT '',
  `city`         VARCHAR(80)  DEFAULT '',
  `website`      VARCHAR(220) DEFAULT '',
  `phone`        VARCHAR(60)  DEFAULT '',
  `email`        VARCHAR(200) DEFAULT '',
  `source`       VARCHAR(40)  DEFAULT '' COMMENT '采集来源 fca-api/asic',
  `note`         VARCHAR(500) DEFAULT '',
  `sort_order`   INT          DEFAULT 0,
  `created_at`   DATETIME     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reg` (`regulator_id`),
  KEY `idx_name` (`name`),
  KEY `idx_status` (`status`),
  UNIQUE KEY `uk_reg_license` (`regulator_id`,`license_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ══════════════════════════════════════════════════════════════
-- 栏目2 · 交易商
-- ══════════════════════════════════════════════════════════════

-- ── 经纪商品牌主表 ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `brokers` (
  `id`           VARCHAR(40)  NOT NULL,
  `name`         VARCHAR(120) NOT NULL COMMENT '品牌名',
  `name_en`      VARCHAR(120) DEFAULT '',
  `code`         VARCHAR(20)  DEFAULT '',
  `logo`         MEDIUMTEXT   COMMENT 'base64',
  `established`  SMALLINT     DEFAULT NULL COMMENT '成立年份',
  `country`      VARCHAR(80)  DEFAULT '' COMMENT '注册地/总部国家',
  `headquarters` VARCHAR(120) DEFAULT '',
  `website`      VARCHAR(255) DEFAULT '',
  `platform`     VARCHAR(80)  DEFAULT 'MT4/MT5',
  `min_dep`      INT          DEFAULT 0 COMMENT '最低入金 USD',
  `leverage`     VARCHAR(20)  DEFAULT '1:500',
  `spread`       VARCHAR(40)  DEFAULT '浮动',
  `btype`        VARCHAR(40)  DEFAULT 'ECN' COMMENT '业务模式 ECN/STP/MM',
  `scope`        VARCHAR(40)  DEFAULT '' COMMENT '展业范围 全球展业/区域性',
  -- 平台官方权威评分（本期留空占位，等后续定规则）
  `score`        DECIMAL(3,1) DEFAULT NULL COMMENT '官方综合评分 0-10（暂缓）',
  -- 豆瓣式用户评分缓存（由 broker_reviews 聚合）
  `user_rating_avg`   DECIMAL(3,2) DEFAULT NULL COMMENT '用户平均星级 1-5',
  `user_rating_count` INT          DEFAULT 0    COMMENT '用户评分人数',
  `summary`      VARCHAR(300) DEFAULT '' COMMENT '一句话简介',
  `intro`        MEDIUMTEXT   COMMENT '公司介绍长文（GEO）',
  `tags`         VARCHAR(255) DEFAULT '' COMMENT '逗号分隔标签',
  `featured`     TINYINT      DEFAULT 0,
  `verified`     TINYINT      DEFAULT 0 COMMENT '已核验',
  `complaint_count` INT       DEFAULT 0 COMMENT '缓存：曝光数',
  `sort_order`   SMALLINT     DEFAULT 0,
  `created_at`   INT          NOT NULL DEFAULT 0,
  `updated_at`   INT          DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_featured` (`featured`),
  KEY `idx_urating` (`user_rating_avg`),
  KEY `idx_ucount` (`user_rating_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 品牌 ↔ 受监管实体关联 ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS `broker_entity_map` (
  `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `broker_id` VARCHAR(40)  NOT NULL,
  `entity_id` INT UNSIGNED NOT NULL,
  `note`      VARCHAR(200) DEFAULT '',
  `created_at` INT         NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_broker_entity` (`broker_id`,`entity_id`),
  KEY `idx_broker` (`broker_id`),
  KEY `idx_entity` (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ══════════════════════════════════════════════════════════════
-- 栏目3 · 资讯
-- ══════════════════════════════════════════════════════════════

-- ── 资讯分类 ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `article_cats` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(40)  NOT NULL,
  `slug`       VARCHAR(40)  NOT NULL,
  `sort_order` SMALLINT     DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 资讯文章 ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `articles` (
  `id`         VARCHAR(40)  NOT NULL,
  `cat_id`     INT UNSIGNED DEFAULT NULL,
  `broker_id`  VARCHAR(40)  DEFAULT NULL COMMENT '关联交易商（可空）',
  `title`      VARCHAR(255) NOT NULL,
  `slug`       VARCHAR(160) DEFAULT '',
  `cover`      TEXT         COMMENT '封面图 URL',
  `summary`    TEXT         COMMENT '摘要',
  `content`    LONGTEXT     COMMENT 'HTML 正文',
  `source`     VARCHAR(80)  DEFAULT '' COMMENT '来源',
  `author`     VARCHAR(60)  DEFAULT '汇评测',
  `tags`       VARCHAR(255) DEFAULT '',
  `read_time`  TINYINT      DEFAULT 5,
  `views`      INT          DEFAULT 0,
  `featured`   TINYINT      DEFAULT 0,
  `status`     ENUM('draft','scheduled','published') DEFAULT 'draft',
  `publish_at` INT          DEFAULT NULL,
  `created_at` INT          NOT NULL DEFAULT 0,
  `updated_at` INT          DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status_pub` (`status`,`publish_at`),
  KEY `idx_cat` (`cat_id`),
  KEY `idx_broker` (`broker_id`),
  KEY `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ══════════════════════════════════════════════════════════════
-- 栏目4 · 曝光（用户维权/投诉）
-- ══════════════════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS `complaints` (
  `id`          VARCHAR(40)  NOT NULL,
  `broker_id`   VARCHAR(40)  DEFAULT NULL COMMENT '关联交易商（可空）',
  `broker_name` VARCHAR(160) DEFAULT '' COMMENT '未收录时的手填名',
  `user_id`     VARCHAR(40)  DEFAULT NULL COMMENT '提交会员（可空=匿名）',
  `nickname`    VARCHAR(60)  DEFAULT '匿名用户',
  `type`        ENUM('无法出金','滑点严重','诱导欺诈','虚假宣传','恶意喊单','其他') DEFAULT '其他',
  `title`       VARCHAR(200) NOT NULL,
  `content`     TEXT         COMMENT '曝光正文',
  `loss_amount` DECIMAL(14,2) DEFAULT NULL COMMENT '涉及金额 USD',
  `evidence`    TEXT         COMMENT '证据链接/图片URL，换行分隔',
  `contact`     VARCHAR(120) DEFAULT '' COMMENT '联系方式（不公开）',
  `status`      ENUM('pending','processing','resolved','rejected') DEFAULT 'pending',
  `admin_reply` TEXT         COMMENT '平台/交易商回应',
  `resolved_amount` DECIMAL(14,2) DEFAULT NULL COMMENT '已解决金额',
  `views`       INT          DEFAULT 0,
  `ip`          VARCHAR(45)  DEFAULT '',
  `created_at`  INT          NOT NULL DEFAULT 0,
  `updated_at`  INT          DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_broker` (`broker_id`),
  KEY `idx_type` (`type`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ══════════════════════════════════════════════════════════════
-- 用户参与 · 前台会员 + 豆瓣式评分/测评
-- ══════════════════════════════════════════════════════════════

-- ── 前台会员 ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `site_users` (
  `id`         VARCHAR(40)  NOT NULL,
  `username`   VARCHAR(60)  NOT NULL COMMENT '登录名',
  `email`      VARCHAR(160) DEFAULT '',
  `pass_hash`  VARCHAR(255) NOT NULL COMMENT 'password_hash bcrypt',
  `nickname`   VARCHAR(60)  DEFAULT '',
  `avatar`     MEDIUMTEXT   COMMENT 'base64 或 URL',
  `bio`        VARCHAR(200) DEFAULT '',
  `review_count` INT        DEFAULT 0 COMMENT '缓存：已通过测评数',
  `status`     ENUM('active','banned') DEFAULT 'active',
  `created_at` INT          NOT NULL DEFAULT 0,
  `last_login` INT          DEFAULT NULL,
  `ip`         VARCHAR(45)  DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 用户评分 + 测评（豆瓣短评核心）─────────────────────────────
CREATE TABLE IF NOT EXISTS `broker_reviews` (
  `id`          VARCHAR(40)  NOT NULL,
  `broker_id`   VARCHAR(40)  NOT NULL,
  `user_id`     VARCHAR(40)  NOT NULL,
  `stars`       TINYINT      NOT NULL DEFAULT 0 COMMENT '1-5 星',
  `content`     TEXT         COMMENT '测评正文（可空=只打分）',
  `useful_count` INT         DEFAULT 0 COMMENT '有用数',
  `status`      ENUM('pending','approved','rejected') DEFAULT 'approved',
  `ip`          VARCHAR(45)  DEFAULT '',
  `created_at`  INT          NOT NULL DEFAULT 0,
  `updated_at`  INT          DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_broker_user` (`broker_id`,`user_id`),
  KEY `idx_broker_status` (`broker_id`,`status`),
  KEY `idx_user` (`user_id`),
  KEY `idx_stars` (`stars`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 测评「有用」投票（防重复）──────────────────────────────────
CREATE TABLE IF NOT EXISTS `review_votes` (
  `review_id` VARCHAR(40) NOT NULL,
  `user_id`   VARCHAR(40) NOT NULL,
  `created_at` INT        NOT NULL DEFAULT 0,
  PRIMARY KEY (`review_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ══════════════════════════════════════════════════════════════
-- 后台 & 系统
-- ══════════════════════════════════════════════════════════════

-- ── 后台账号 ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `editors` (
  `id`         VARCHAR(40)  NOT NULL,
  `username`   VARCHAR(60)  NOT NULL,
  `name`       VARCHAR(60)  DEFAULT '',
  `pass_hash`  VARCHAR(64)  NOT NULL COMMENT 'SHA256',
  `role`       ENUM('superadmin','editor') DEFAULT 'editor',
  `created_at` INT          NOT NULL DEFAULT 0,
  `last_login` INT          DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 操作审计日志 ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `logs` (
  `id`          VARCHAR(40) NOT NULL,
  `action`      VARCHAR(60) DEFAULT '',
  `detail`      VARCHAR(500) DEFAULT '',
  `target_id`   VARCHAR(40) DEFAULT '',
  `operator`    VARCHAR(60) DEFAULT '',
  `operator_id` VARCHAR(40) DEFAULT '',
  `time`        INT         NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 站点配置 ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `site_settings` (
  `k` VARCHAR(60)  NOT NULL,
  `v` MEDIUMTEXT,
  PRIMARY KEY (`k`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ════════════════════════════════════════════════════════════════
-- 初始数据
-- ════════════════════════════════════════════════════════════════

-- 默认超管：admin / huipingce2026 （SHA256 哈希）
INSERT INTO `editors` (`id`,`username`,`name`,`pass_hash`,`role`,`created_at`) VALUES
('ed_root','admin','超级管理员',
 SHA2('huipingce2026',256),'superadmin',UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `username`=`username`;

-- 站点配置
INSERT INTO `site_settings` (`k`,`v`) VALUES
('siteName','汇评测'),
('siteNameEn','Huipingce'),
('siteSlogan','外汇经纪商监管信息与用户测评平台'),
('siteDesc','汇评测聚合全球外汇监管机构、受监管实体与经纪商信息，用户可自由打分、写测评、曝光维权，帮交易者更明智地选择经纪商。'),
('siteKeywords','外汇经纪商,外汇监管查询,经纪商测评,外汇曝光,交易商评分'),
('siteUrl','https://www.huipingce.com'),
('icp',''),
('serviceTime','09:00—22:00'),
('reviewModeration','0')
ON DUPLICATE KEY UPDATE `v`=VALUES(`v`);

-- 资讯分类（仿天眼）
INSERT INTO `article_cats` (`name`,`slug`,`sort_order`) VALUES
('行业动态','industry',10),
('交易商','broker',20),
('监管快讯','regulation',30),
('曝光维权','exposure',40),
('原创评测','original',50)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 监管机构种子（核心 8 家，采集时会补全实体）
INSERT INTO `regulators`
(`id`,`name`,`full_name`,`country`,`flag`,`region`,`grade`,`trust_score`,`established`,`gov_type`,`website`,`query_url`,`sort_order`,`created_at`) VALUES
('reg_fca','FCA','Financial Conduct Authority','英国','🇬🇧','欧洲','AAA',98,2013,'政府监管','https://www.fca.org.uk','https://register.fca.org.uk',10,UNIX_TIMESTAMP()),
('reg_asic','ASIC','Australian Securities & Investments Commission','澳大利亚','🇦🇺','亚太','AAA',96,1998,'政府监管','https://asic.gov.au','https://connectonline.asic.gov.au',20,UNIX_TIMESTAMP()),
('reg_sfc','SFC','Securities and Futures Commission','中国香港','🇭🇰','亚太','AAA',95,1989,'政府监管','https://www.sfc.hk','https://apps.sfc.hk/publicregWeb',30,UNIX_TIMESTAMP()),
('reg_cysec','CySEC','Cyprus Securities and Exchange Commission','塞浦路斯','🇨🇾','欧洲','AA',85,2001,'政府监管','https://www.cysec.gov.cy','https://www.cysec.gov.cy/en-GB/entities',40,UNIX_TIMESTAMP()),
('reg_nfa','NFA','National Futures Association','美国','🇺🇸','美洲','AAA',97,1982,'自律组织','https://www.nfa.futures.org','https://www.nfa.futures.org/basicnet',50,UNIX_TIMESTAMP()),
('reg_fsca','FSCA','Financial Sector Conduct Authority','南非','🇿🇦','非洲','A',72,2018,'政府监管','https://www.fsca.co.za','https://www.fsca.co.za',60,UNIX_TIMESTAMP()),
('reg_fsc_mu','FSC','Financial Services Commission','毛里求斯','🇲🇺','非洲','B',58,2001,'政府监管','https://www.fscmauritius.org','https://www.fscmauritius.org',70,UNIX_TIMESTAMP()),
('reg_vfsc','VFSC','Vanuatu Financial Services Commission','瓦努阿图','🇻🇺','离岸','C',40,1993,'政府监管','https://www.vfsc.vu','https://www.vfsc.vu',80,UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);
