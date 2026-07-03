-- ══ 汇评测 示例数据（source=sample，演示用，真实上线前替换）══
-- 生成：gen_seed.py  幂等：先清空业务表再插入
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;
DELETE FROM `broker_reviews`;
DELETE FROM `review_votes`;
DELETE FROM `complaints`;
DELETE FROM `articles`;
DELETE FROM `broker_entity_map`;
DELETE FROM `reg_entities`;
DELETE FROM `brokers`;
DELETE FROM `site_users`;
SET FOREIGN_KEY_CHECKS=1;

INSERT INTO brokers (id,name,name_en,established,country,headquarters,website,platform,min_dep,leverage,spread,btype,scope,verified,summary,tags,sort_order,created_at) VALUES
('ic-markets','IC Markets','IC Markets',2007,'澳大利亚','悉尼','https://www.icmarkets.com','MT4/MT5/cTrader',200,'1:500','0.0 起','ECN','全球展业',1,'澳洲老牌 ECN 券商，以原始点差和低延迟撮合著称，主打高频与剥头皮交易者。','ECN,低点差,MT4,MT5',200,UNIX_TIMESTAMP()-0*3600),
('pepperstone','Pepperstone','Pepperstone',2010,'澳大利亚','墨尔本','https://pepperstone.com','MT4/MT5/cTrader',0,'1:500','0.0 起','ECN','全球展业',1,'多国持牌的澳洲券商，执行速度快，客服口碑良好，Razor 账户点差有竞争力。','多监管,快速执行,MT4',190,UNIX_TIMESTAMP()-1*3600),
('xm','XM','XM Group',2009,'塞浦路斯','利马索尔','https://www.xm.com','MT4/MT5',5,'1:1000','1.0 起','MM/STP','全球展业',1,'面向零售的大型经纪商，入金门槛低、赠金活动多，中文服务完善。','低门槛,赠金,MT4,MT5',180,UNIX_TIMESTAMP()-2*3600),
('exness','Exness','Exness',2008,'塞浦路斯','利马索尔','https://www.exness.com','MT4/MT5',10,'1:2000','0.0 起','ECN/STP','全球展业',1,'以超高杠杆、秒级出金和稳定点差闻名，深受东南亚与中东交易者欢迎。','高杠杆,秒出金,MT5',170,UNIX_TIMESTAMP()-3*3600),
('fxtm','FXTM','ForexTime',2011,'塞浦路斯','利马索尔','https://www.forextime.com','MT4/MT5',10,'1:1000','0.1 起','ECN/STP','全球展业',1,'富拓，多牌照零售券商，教育资源丰富，本地化服务到位。','多监管,教育,MT4',160,UNIX_TIMESTAMP()-4*3600),
('ig','IG','IG Group',1974,'英国','伦敦','https://www.ig.com','自研/MT4',0,'1:30','0.6 起','MM','全球展业',1,'英国上市老牌券商，成立近半个世纪，产品线极广，监管等级高。','上市公司,老牌,FCA',150,UNIX_TIMESTAMP()-5*3600),
('oanda','OANDA','OANDA',1996,'美国','纽约','https://www.oanda.com','MT4/自研',0,'1:50','0.8 起','MM','全球展业',1,'美国老牌券商，以透明的历史点差数据和无最低入金著称。','老牌,NFA,透明',140,UNIX_TIMESTAMP()-6*3600),
('saxo','Saxo Bank','Saxo Bank',1992,'丹麦','哥本哈根','https://www.home.saxo','自研 SaxoTrader',2000,'1:30','0.4 起','MM','全球展业',1,'丹麦持牌银行券商，产品覆盖全球市场，适合高净值与专业客户。','银行牌照,专业,多品种',130,UNIX_TIMESTAMP()-7*3600),
('tickmill','Tickmill','Tickmill',2014,'英国','伦敦','https://www.tickmill.com','MT4/MT5',100,'1:500','0.0 起','ECN','全球展业',1,'以低佣金 ECN 账户受欢迎，点差与手续费结构清晰。','ECN,低佣金,MT4',120,UNIX_TIMESTAMP()-8*3600),
('fp-markets','FP Markets','First Prudential Markets',2005,'澳大利亚','悉尼','https://www.fpmarkets.com','MT4/MT5/cTrader',100,'1:500','0.0 起','ECN/DMA','全球展业',1,'澳洲 DMA/ECN 券商，产品线丰富，出入金渠道多样。','DMA,ECN,澳洲',110,UNIX_TIMESTAMP()-9*3600),
('vantage','Vantage','Vantage Markets',2009,'澳大利亚','悉尼','https://www.vantagemarkets.com','MT4/MT5',50,'1:500','0.0 起','ECN/STP','全球展业',1,'万致，澳洲背景零售券商，App 体验较好，营销活跃。','App,ECN,澳洲',100,UNIX_TIMESTAMP()-10*3600),
('thinkmarkets','ThinkMarkets','ThinkMarkets',2010,'澳大利亚','墨尔本','https://www.thinkmarkets.com','MT4/MT5/ThinkTrader',0,'1:500','0.0 起','ECN/STP','全球展业',1,'智汇，自研 ThinkTrader 平台，多国持牌，界面现代。','自研平台,多监管',90,UNIX_TIMESTAMP()-11*3600),
('dukascopy','Dukascopy','Dukascopy Bank',1998,'瑞士','日内瓦','https://www.dukascopy.com','JForex/MT4',100,'1:200','0.1 起','ECN','全球展业',1,'瑞士银行牌照券商，JForex 平台深受量化与专业交易者青睐。','瑞士银行,JForex,专业',80,UNIX_TIMESTAMP()-12*3600),
('swissquote','Swissquote','Swissquote Bank',1996,'瑞士','格兰','https://www.swissquote.com','MT4/Advanced Trader',5000,'1:100','1.0 起','MM','全球展业',1,'瑞士上市银行券商，资金安全性高，适合稳健型高净值客户。','瑞士银行,上市,稳健',70,UNIX_TIMESTAMP()-13*3600),
('hfm','HFM','HF Markets',2010,'塞浦路斯','利马索尔','https://www.hfm.com','MT4/MT5',0,'1:1000','0.0 起','ECN/STP','全球展业',1,'原 HotForex，多牌照零售券商，账户类型多、赠金活动频繁。','多监管,赠金,MT4',60,UNIX_TIMESTAMP()-14*3600),
('fxpro','FxPro','FxPro',2006,'英国','伦敦','https://www.fxpro.com','MT4/MT5/cTrader',100,'1:200','0.0 起','NDD','全球展业',1,'浦汇，英塞双牌无交易员台券商，执行透明，中文服务成熟。','NDD,FCA,老牌',50,UNIX_TIMESTAMP()-15*3600),
('admiral','Admirals','Admiral Markets',2001,'爱沙尼亚','塔林','https://admiralmarkets.com','MT4/MT5',100,'1:500','0.0 起','STP/ECN','全球展业',1,'艾德米拉，欧洲背景券商，教育与分析工具丰富。','欧洲,教育,MT5',40,UNIX_TIMESTAMP()-16*3600),
('easymarkets','easyMarkets','easyMarkets',2001,'塞浦路斯','利马索尔','https://www.easymarkets.com','MT4/MT5/自研',25,'1:2000','1.0 起','MM','全球展业',1,'以固定点差、负余额保护和内置风控工具（dealCancellation）为特色。','固定点差,风控,新手友好',30,UNIX_TIMESTAMP()-17*3600),
('doo-prime','Doo Prime','Doo Prime',2017,'毛里求斯','路易港','https://www.dooprime.com','MT4/MT5',100,'1:1000','0.1 起','STP','区域性',0,'裕泰，Doo Group 旗下零售券商，亚太市场活跃，牌照以离岸为主。','亚太,离岸,STP',20,UNIX_TIMESTAMP()-18*3600),
('go-markets','GO Markets','GO Markets',2006,'澳大利亚','墨尔本','https://www.gomarkets.com','MT4/MT5',200,'1:500','0.0 起','ECN/STP','全球展业',1,'高汇，澳洲老牌零售券商，点差稳定，本地化服务较好。','澳洲,ECN,老牌',10,UNIX_TIMESTAMP()-19*3600);

INSERT INTO reg_entities (name,regulator_id,license_no,license_type,client_type,status,source) VALUES
('International Capital Markets Pty Ltd','reg_asic','335692','AFS 全牌照 (MM)','零售/专业','active','sample'),
('IC Markets (EU) Ltd','reg_cysec','362/18','CIF 投资牌照','零售/专业','active','sample'),
('Pepperstone Group Limited','reg_asic','414530','AFS 全牌照 (MM)','零售/专业','active','sample'),
('Pepperstone Limited','reg_fca','684312','STP/ECN 全牌照','零售/专业','active','sample'),
('Pepperstone EU Limited','reg_cysec','388/20','CIF 投资牌照','零售/专业','active','sample'),
('Trading Point of Financial Instruments Ltd','reg_cysec','120/10','CIF 投资牌照','零售/专业','active','sample'),
('Trading Point of Financial Instruments Pty Ltd','reg_asic','443670','AFS 牌照','零售/专业','suspended','sample'),
('Exness (Cy) Ltd','reg_cysec','178/12','CIF 投资牌照','零售/专业','active','sample'),
('Exness (UK) Ltd','reg_fca','730729','投资中介牌照','零售/专业','active','sample'),
('ForexTime Ltd','reg_cysec','185/12','CIF 投资牌照','零售/专业','active','sample'),
('Exinity UK Ltd','reg_fca','777911','投资中介牌照','零售/专业','active','sample'),
('IG Markets Limited','reg_fca','195355','MM 全牌照','零售/专业','active','sample'),
('IG Markets Limited (AU)','reg_asic','220440','AFS 全牌照','零售/专业','active','sample'),
('OANDA Corporation','reg_nfa','0325821','RFED/FCM 牌照','零售/专业','active','sample'),
('OANDA Europe Ltd','reg_fca','542574','MM 全牌照','零售/专业','active','sample'),
('Saxo Capital Markets UK Ltd','reg_fca','551422','MM 全牌照','零售/专业','active','sample'),
('Tickmill UK Ltd','reg_fca','717270','STP/ECN 全牌照','零售/专业','active','sample'),
('Tickmill Europe Ltd','reg_cysec','278/15','CIF 投资牌照','零售/专业','active','sample'),
('First Prudential Markets Pty Ltd','reg_asic','286354','AFS 全牌照','零售/专业','active','sample'),
('FP Markets EU','reg_cysec','371/18','CIF 投资牌照','零售/专业','active','sample'),
('Vantage Global Prime Pty Ltd','reg_asic','428901','AFS 全牌照','零售/专业','active','sample'),
('TF Global Markets (Aust) Pty Ltd','reg_asic','424700','AFS 全牌照','零售/专业','active','sample'),
('TF Global Markets (UK) Ltd','reg_fca','629628','MM 全牌照','零售/专业','active','sample'),
('Dukascopy (UK) — 演示','reg_fca','OFFSHORE-DEMO','经纪牌照','零售/专业','expired','sample'),
('Swissquote Ltd','reg_fca','562170','MM 全牌照','零售/专业','active','sample'),
('HF Markets (Europe) Ltd','reg_cysec','183/12','CIF 投资牌照','零售/专业','active','sample'),
('HF Markets (UK) Ltd','reg_fca','801701','投资中介牌照','零售/专业','active','sample'),
('HFM (South Africa)','reg_fsca','46632','FSP 牌照','零售/专业','active','sample'),
('FxPro UK Limited','reg_fca','509956','MM 全牌照','零售/专业','active','sample'),
('FxPro Financial Services Ltd','reg_cysec','078/07','CIF 投资牌照','零售/专业','active','sample'),
('Admiral Markets UK Ltd','reg_fca','595450','MM 全牌照','零售/专业','active','sample'),
('Admirals Europe Ltd','reg_cysec','201/13','CIF 投资牌照','零售/专业','active','sample'),
('Easy Forex Trading Ltd','reg_cysec','079/07','CIF 投资牌照','零售/专业','active','sample'),
('Easy Markets Pty Ltd','reg_asic','246566','AFS 牌照','零售/专业','active','sample'),
('Doo Prime Vanuatu — 演示','reg_fsc_mu','DEMO-700123','离岸经纪牌照','零售/专业','active','sample'),
('Doo Prime Ltd','reg_vfsc','40456','离岸经纪牌照','零售/专业','active','sample'),
('GO Markets Pty Ltd','reg_asic','254963','AFS 全牌照','零售/专业','active','sample'),
('GO Markets EU','reg_cysec','322/17','CIF 投资牌照','零售/专业','suspended','sample');

INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'ic-markets', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='International Capital Markets Pty Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'ic-markets', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='IC Markets (EU) Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'pepperstone', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='Pepperstone Group Limited' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'pepperstone', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='Pepperstone Limited' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'pepperstone', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='Pepperstone EU Limited' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'xm', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='Trading Point of Financial Instruments Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'xm', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='Trading Point of Financial Instruments Pty Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'exness', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='Exness (Cy) Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'exness', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='Exness (UK) Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'fxtm', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='ForexTime Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'fxtm', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='Exinity UK Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'ig', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='IG Markets Limited' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'ig', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='IG Markets Limited (AU)' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'oanda', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='OANDA Corporation' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'oanda', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='OANDA Europe Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'saxo', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='Saxo Capital Markets UK Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'tickmill', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='Tickmill UK Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'tickmill', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='Tickmill Europe Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'fp-markets', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='First Prudential Markets Pty Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'fp-markets', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='FP Markets EU' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'vantage', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='Vantage Global Prime Pty Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'thinkmarkets', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='TF Global Markets (Aust) Pty Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'thinkmarkets', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='TF Global Markets (UK) Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'dukascopy', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='Dukascopy (UK) — 演示' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'swissquote', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='Swissquote Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'hfm', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='HF Markets (Europe) Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'hfm', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='HF Markets (UK) Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'hfm', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='HFM (South Africa)' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'fxpro', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='FxPro UK Limited' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'fxpro', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='FxPro Financial Services Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'admiral', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='Admiral Markets UK Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'admiral', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='Admirals Europe Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'easymarkets', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='Easy Forex Trading Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'easymarkets', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='Easy Markets Pty Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'doo-prime', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='Doo Prime Vanuatu — 演示' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'doo-prime', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='Doo Prime Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'go-markets', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='GO Markets Pty Ltd' LIMIT 1;
INSERT INTO broker_entity_map (broker_id,entity_id,created_at) SELECT 'go-markets', id, UNIX_TIMESTAMP() FROM reg_entities WHERE name='GO Markets EU' LIMIT 1;

UPDATE regulators r SET entity_count=(SELECT COUNT(*) FROM reg_entities e WHERE e.regulator_id=r.id);

INSERT INTO site_users (id,username,email,pass_hash,nickname,status,created_at) VALUES
('u_demo00','demo00','','$2y$12$6V6G7UixlZg1Ht6c2/sts.dolVxojWeQdwKBFjLGoZv1viRypRx72','老K交易笔记','active',UNIX_TIMESTAMP()-0*7200),
('u_demo01','demo01','','$2y$12$6V6G7UixlZg1Ht6c2/sts.dolVxojWeQdwKBFjLGoZv1viRypRx72','江海一舟','active',UNIX_TIMESTAMP()-1*7200),
('u_demo02','demo02','','$2y$12$6V6G7UixlZg1Ht6c2/sts.dolVxojWeQdwKBFjLGoZv1viRypRx72','割肉了阿强','active',UNIX_TIMESTAMP()-2*7200),
('u_demo03','demo03','','$2y$12$6V6G7UixlZg1Ht6c2/sts.dolVxojWeQdwKBFjLGoZv1viRypRx72','BostonScalper','active',UNIX_TIMESTAMP()-3*7200),
('u_demo04','demo04','','$2y$12$6V6G7UixlZg1Ht6c2/sts.dolVxojWeQdwKBFjLGoZv1viRypRx72','不再抄底的鱼','active',UNIX_TIMESTAMP()-4*7200),
('u_demo05','demo05','','$2y$12$6V6G7UixlZg1Ht6c2/sts.dolVxojWeQdwKBFjLGoZv1viRypRx72','点差猎人','active',UNIX_TIMESTAMP()-5*7200),
('u_demo06','demo06','','$2y$12$6V6G7UixlZg1Ht6c2/sts.dolVxojWeQdwKBFjLGoZv1viRypRx72','MT4老司机','active',UNIX_TIMESTAMP()-6*7200),
('u_demo07','demo07','','$2y$12$6V6G7UixlZg1Ht6c2/sts.dolVxojWeQdwKBFjLGoZv1viRypRx72','南半球的风','active',UNIX_TIMESTAMP()-7*7200),
('u_demo08','demo08','','$2y$12$6V6G7UixlZg1Ht6c2/sts.dolVxojWeQdwKBFjLGoZv1viRypRx72','只做欧美盘','active',UNIX_TIMESTAMP()-8*7200),
('u_demo09','demo09','','$2y$12$6V6G7UixlZg1Ht6c2/sts.dolVxojWeQdwKBFjLGoZv1viRypRx72','隔夜不留仓','active',UNIX_TIMESTAMP()-9*7200),
('u_demo10','demo10','','$2y$12$6V6G7UixlZg1Ht6c2/sts.dolVxojWeQdwKBFjLGoZv1viRypRx72','跑单选手小林','active',UNIX_TIMESTAMP()-10*7200),
('u_demo11','demo11','','$2y$12$6V6G7UixlZg1Ht6c2/sts.dolVxojWeQdwKBFjLGoZv1viRypRx72','稳健派老陈','active',UNIX_TIMESTAMP()-11*7200),
('u_demo12','demo12','','$2y$12$6V6G7UixlZg1Ht6c2/sts.dolVxojWeQdwKBFjLGoZv1viRypRx72','反着买别墅','active',UNIX_TIMESTAMP()-12*7200),
('u_demo13','demo13','','$2y$12$6V6G7UixlZg1Ht6c2/sts.dolVxojWeQdwKBFjLGoZv1viRypRx72','晒单不隔夜','active',UNIX_TIMESTAMP()-13*7200),
('u_demo14','demo14','','$2y$12$6V6G7UixlZg1Ht6c2/sts.dolVxojWeQdwKBFjLGoZv1viRypRx72','离岸避雷针','active',UNIX_TIMESTAMP()-14*7200),
('u_demo15','demo15','','$2y$12$6V6G7UixlZg1Ht6c2/sts.dolVxojWeQdwKBFjLGoZv1viRypRx72','流动性搬运工','active',UNIX_TIMESTAMP()-15*7200);

INSERT INTO broker_reviews (id,broker_id,user_id,stars,content,useful_count,status,created_at) VALUES
('rv_demo000','ic-markets','u_demo09',5,'点差是真的稳，剥头皮没被砍单，出金隔天到账。',1,'approved',UNIX_TIMESTAMP()-0*3600-1*60),
('rv_demo001','ic-markets','u_demo10',5,'点差是真的稳，剥头皮没被砍单，出金隔天到账。',5,'approved',UNIX_TIMESTAMP()-1*3600-2*60),
('rv_demo002','ic-markets','u_demo06',5,'点差是真的稳，剥头皮没被砍单，出金隔天到账。',5,'approved',UNIX_TIMESTAMP()-2*3600-3*60),
('rv_demo003','ic-markets','u_demo05',3,'点差浮动有点大，稳定性一般，先小仓试水。',10,'approved',UNIX_TIMESTAMP()-3*3600-4*60),
('rv_demo004','ic-markets','u_demo01',5,NULL,32,'approved',UNIX_TIMESTAMP()-8*3600-5*60),
('rv_demo005','pepperstone','u_demo12',4,'点差中规中矩，赠金活动挺多但记得看清出金条件。',4,'approved',UNIX_TIMESTAMP()-0*3600-6*60),
('rv_demo006','pepperstone','u_demo07',2,NULL,35,'approved',UNIX_TIMESTAMP()-1*3600-7*60),
('rv_demo007','pepperstone','u_demo09',4,NULL,14,'approved',UNIX_TIMESTAMP()-2*3600-8*60),
('rv_demo008','pepperstone','u_demo14',4,NULL,11,'approved',UNIX_TIMESTAMP()-3*3600-9*60),
('rv_demo009','pepperstone','u_demo01',5,'监管齐全，资金分离做得到位，作为主账户放心。',24,'approved',UNIX_TIMESTAMP()-5*3600-10*60),
('rv_demo010','pepperstone','u_demo04',5,NULL,3,'approved',UNIX_TIMESTAMP()-6*3600-11*60),
('rv_demo011','pepperstone','u_demo11',3,NULL,33,'approved',UNIX_TIMESTAMP()-9*3600-12*60),
('rv_demo012','xm','u_demo15',5,'监管齐全，资金分离做得到位，作为主账户放心。',39,'approved',UNIX_TIMESTAMP()-0*3600-13*60),
('rv_demo013','xm','u_demo11',2,'滑点比宣传的明显，挂单经常成交在不利价。',29,'approved',UNIX_TIMESTAMP()-1*3600-14*60),
('rv_demo014','xm','u_demo02',4,NULL,28,'approved',UNIX_TIMESTAMP()-2*3600-15*60),
('rv_demo015','xm','u_demo04',5,NULL,38,'approved',UNIX_TIMESTAMP()-3*3600-16*60),
('rv_demo016','xm','u_demo13',5,NULL,14,'approved',UNIX_TIMESTAMP()-4*3600-17*60),
('rv_demo017','xm','u_demo14',4,'老牌子用着踏实，产品线广，新手学习成本略高。',36,'approved',UNIX_TIMESTAMP()-6*3600-18*60),
('rv_demo018','xm','u_demo07',4,NULL,4,'approved',UNIX_TIMESTAMP()-7*3600-19*60),
('rv_demo019','xm','u_demo01',3,'点差浮动有点大，稳定性一般，先小仓试水。',0,'approved',UNIX_TIMESTAMP()-8*3600-20*60),
('rv_demo020','xm','u_demo09',3,'不好不坏，出金正常但审核偶尔要一两天。',23,'approved',UNIX_TIMESTAMP()-10*3600-21*60),
('rv_demo021','xm','u_demo00',2,'隔夜库存费偏高，长线拿着有点肉疼。',11,'approved',UNIX_TIMESTAMP()-12*3600-22*60),
('rv_demo022','xm','u_demo12',5,'入金到账快，出金也没设卡，属于让人省心的那种。',31,'approved',UNIX_TIMESTAMP()-13*3600-23*60),
('rv_demo023','exness','u_demo14',5,'入金到账快，出金也没设卡，属于让人省心的那种。',6,'approved',UNIX_TIMESTAMP()-0*3600-24*60),
('rv_demo024','exness','u_demo07',5,'用了两年多，服务器很少断线，客服中文在线响应快。',35,'approved',UNIX_TIMESTAMP()-1*3600-25*60),
('rv_demo025','exness','u_demo05',3,'营销电话有点多，交易本身没什么大问题。',29,'approved',UNIX_TIMESTAMP()-2*3600-26*60),
('rv_demo026','exness','u_demo13',4,'点差中规中矩，赠金活动挺多但记得看清出金条件。',33,'approved',UNIX_TIMESTAMP()-3*3600-27*60),
('rv_demo027','exness','u_demo03',5,'入金到账快，出金也没设卡，属于让人省心的那种。',29,'approved',UNIX_TIMESTAMP()-4*3600-28*60),
('rv_demo028','exness','u_demo04',5,NULL,38,'approved',UNIX_TIMESTAMP()-5*3600-29*60),
('rv_demo029','exness','u_demo11',4,'客服态度好，问题基本能解决，就是响应有时要等。',37,'approved',UNIX_TIMESTAMP()-8*3600-30*60),
('rv_demo030','exness','u_demo06',4,NULL,9,'approved',UNIX_TIMESTAMP()-9*3600-31*60),
('rv_demo031','exness','u_demo01',2,'出金拖过一次，催了客服才到，体验一般。',36,'approved',UNIX_TIMESTAMP()-10*3600-32*60),
('rv_demo032','fxtm','u_demo14',5,'用了两年多，服务器很少断线，客服中文在线响应快。',22,'approved',UNIX_TIMESTAMP()-0*3600-33*60),
('rv_demo033','fxtm','u_demo03',4,'整体不错，就是佣金账户手续费略高，适合量大的人。',17,'approved',UNIX_TIMESTAMP()-1*3600-34*60),
('rv_demo034','fxtm','u_demo00',2,NULL,12,'approved',UNIX_TIMESTAMP()-2*3600-35*60),
('rv_demo035','fxtm','u_demo12',4,'客服态度好，问题基本能解决，就是响应有时要等。',1,'approved',UNIX_TIMESTAMP()-3*3600-36*60),
('rv_demo036','fxtm','u_demo07',5,NULL,37,'approved',UNIX_TIMESTAMP()-6*3600-37*60),
('rv_demo037','fxtm','u_demo01',2,'隔夜库存费偏高，长线拿着有点肉疼。',9,'approved',UNIX_TIMESTAMP()-7*3600-38*60),
('rv_demo038','fxtm','u_demo04',5,'用了两年多，服务器很少断线，客服中文在线响应快。',11,'approved',UNIX_TIMESTAMP()-8*3600-39*60),
('rv_demo039','fxtm','u_demo13',3,'还行吧，非农和数据行情扩点比较明显，日内注意点。',16,'approved',UNIX_TIMESTAMP()-9*3600-40*60),
('rv_demo040','ig','u_demo12',4,'整体不错，就是佣金账户手续费略高，适合量大的人。',20,'approved',UNIX_TIMESTAMP()-0*3600-41*60),
('rv_demo041','ig','u_demo03',4,'整体不错，就是佣金账户手续费略高，适合量大的人。',17,'approved',UNIX_TIMESTAMP()-1*3600-42*60),
('rv_demo042','ig','u_demo15',5,'入金到账快，出金也没设卡，属于让人省心的那种。',5,'approved',UNIX_TIMESTAMP()-3*3600-43*60),
('rv_demo043','ig','u_demo11',5,'监管齐全，资金分离做得到位，作为主账户放心。',20,'approved',UNIX_TIMESTAMP()-4*3600-44*60),
('rv_demo044','ig','u_demo02',5,'原始点差账户体验一流，滑点在可接受范围内，非农也没夸张扩点。',37,'approved',UNIX_TIMESTAMP()-5*3600-45*60),
('rv_demo045','ig','u_demo04',4,'点差中规中矩，赠金活动挺多但记得看清出金条件。',24,'approved',UNIX_TIMESTAMP()-9*3600-46*60),
('rv_demo046','ig','u_demo09',5,'用了两年多，服务器很少断线，客服中文在线响应快。',8,'approved',UNIX_TIMESTAMP()-10*3600-47*60),
('rv_demo047','ig','u_demo06',4,NULL,36,'approved',UNIX_TIMESTAMP()-11*3600-48*60),
('rv_demo048','oanda','u_demo01',5,'入金到账快，出金也没设卡，属于让人省心的那种。',17,'approved',UNIX_TIMESTAMP()-0*3600-49*60),
('rv_demo049','oanda','u_demo08',5,'监管齐全，资金分离做得到位，作为主账户放心。',12,'approved',UNIX_TIMESTAMP()-1*3600-50*60),
('rv_demo050','oanda','u_demo10',5,'原始点差账户体验一流，滑点在可接受范围内，非农也没夸张扩点。',26,'approved',UNIX_TIMESTAMP()-2*3600-51*60),
('rv_demo051','oanda','u_demo06',5,'入金到账快，出金也没设卡，属于让人省心的那种。',25,'approved',UNIX_TIMESTAMP()-4*3600-52*60),
('rv_demo052','oanda','u_demo05',5,'用了两年多，服务器很少断线，客服中文在线响应快。',23,'approved',UNIX_TIMESTAMP()-5*3600-53*60),
('rv_demo053','oanda','u_demo09',5,'入金到账快，出金也没设卡，属于让人省心的那种。',37,'approved',UNIX_TIMESTAMP()-6*3600-54*60),
('rv_demo054','oanda','u_demo14',5,'用了两年多，服务器很少断线，客服中文在线响应快。',14,'approved',UNIX_TIMESTAMP()-7*3600-55*60),
('rv_demo055','oanda','u_demo07',5,'入金到账快，出金也没设卡，属于让人省心的那种。',6,'approved',UNIX_TIMESTAMP()-8*3600-56*60),
('rv_demo056','saxo','u_demo13',3,NULL,21,'approved',UNIX_TIMESTAMP()-0*3600-57*60),
('rv_demo057','saxo','u_demo15',4,NULL,7,'approved',UNIX_TIMESTAMP()-1*3600-58*60),
('rv_demo058','saxo','u_demo03',4,'客服态度好，问题基本能解决，就是响应有时要等。',24,'approved',UNIX_TIMESTAMP()-3*3600-59*60),
('rv_demo059','saxo','u_demo00',3,'营销电话有点多，交易本身没什么大问题。',8,'approved',UNIX_TIMESTAMP()-4*3600-60*60),
('rv_demo060','saxo','u_demo08',3,'不好不坏，出金正常但审核偶尔要一两天。',5,'approved',UNIX_TIMESTAMP()-6*3600-61*60),
('rv_demo061','saxo','u_demo02',5,NULL,20,'approved',UNIX_TIMESTAMP()-7*3600-62*60),
('rv_demo062','saxo','u_demo04',5,NULL,2,'approved',UNIX_TIMESTAMP()-8*3600-63*60),
('rv_demo063','saxo','u_demo05',3,'还行吧，非农和数据行情扩点比较明显，日内注意点。',15,'approved',UNIX_TIMESTAMP()-9*3600-64*60),
('rv_demo064','saxo','u_demo09',5,NULL,1,'approved',UNIX_TIMESTAMP()-10*3600-65*60),
('rv_demo065','saxo','u_demo10',2,'隔夜库存费偏高，长线拿着有点肉疼。',30,'approved',UNIX_TIMESTAMP()-11*3600-66*60),
('rv_demo066','tickmill','u_demo05',5,'点差是真的稳，剥头皮没被砍单，出金隔天到账。',21,'approved',UNIX_TIMESTAMP()-0*3600-67*60),
('rv_demo067','tickmill','u_demo01',5,'原始点差账户体验一流，滑点在可接受范围内，非农也没夸张扩点。',20,'approved',UNIX_TIMESTAMP()-2*3600-68*60),
('rv_demo068','tickmill','u_demo04',5,'用了两年多，服务器很少断线，客服中文在线响应快。',12,'approved',UNIX_TIMESTAMP()-3*3600-69*60),
('rv_demo069','tickmill','u_demo09',5,'点差是真的稳，剥头皮没被砍单，出金隔天到账。',21,'approved',UNIX_TIMESTAMP()-4*3600-70*60),
('rv_demo070','tickmill','u_demo06',3,'还行吧，非农和数据行情扩点比较明显，日内注意点。',27,'approved',UNIX_TIMESTAMP()-5*3600-71*60),
('rv_demo071','tickmill','u_demo13',5,'点差是真的稳，剥头皮没被砍单，出金隔天到账。',23,'approved',UNIX_TIMESTAMP()-6*3600-72*60),
('rv_demo072','tickmill','u_demo07',4,'整体不错，就是佣金账户手续费略高，适合量大的人。',17,'approved',UNIX_TIMESTAMP()-7*3600-73*60),
('rv_demo073','tickmill','u_demo15',4,'平台稳定，App 偶尔加载慢，其他没毛病。',28,'approved',UNIX_TIMESTAMP()-9*3600-74*60),
('rv_demo074','tickmill','u_demo03',4,'客服态度好，问题基本能解决，就是响应有时要等。',38,'approved',UNIX_TIMESTAMP()-10*3600-75*60),
('rv_demo075','tickmill','u_demo12',5,'用了两年多，服务器很少断线，客服中文在线响应快。',24,'approved',UNIX_TIMESTAMP()-11*3600-76*60),
('rv_demo076','fp-markets','u_demo06',4,NULL,30,'approved',UNIX_TIMESTAMP()-0*3600-77*60),
('rv_demo077','fp-markets','u_demo13',4,'点差中规中矩，赠金活动挺多但记得看清出金条件。',38,'approved',UNIX_TIMESTAMP()-1*3600-78*60),
('rv_demo078','fp-markets','u_demo11',3,'点差浮动有点大，稳定性一般，先小仓试水。',34,'approved',UNIX_TIMESTAMP()-2*3600-79*60),
('rv_demo079','fp-markets','u_demo10',1,'重仓时莫名断线，事后不认账，谨慎。',39,'approved',UNIX_TIMESTAMP()-3*3600-80*60),
('rv_demo080','fp-markets','u_demo14',5,NULL,4,'approved',UNIX_TIMESTAMP()-4*3600-81*60),
('rv_demo081','fp-markets','u_demo03',4,NULL,5,'approved',UNIX_TIMESTAMP()-5*3600-82*60),
('rv_demo082','fp-markets','u_demo12',5,NULL,5,'approved',UNIX_TIMESTAMP()-6*3600-83*60),
('rv_demo083','fp-markets','u_demo07',5,NULL,8,'approved',UNIX_TIMESTAMP()-10*3600-84*60),
('rv_demo084','fp-markets','u_demo09',3,'还行吧，非农和数据行情扩点比较明显，日内注意点。',31,'approved',UNIX_TIMESTAMP()-11*3600-85*60),
('rv_demo085','fp-markets','u_demo02',4,'点差中规中矩，赠金活动挺多但记得看清出金条件。',13,'approved',UNIX_TIMESTAMP()-12*3600-86*60),
('rv_demo086','fp-markets','u_demo00',2,'出金拖过一次，催了客服才到，体验一般。',36,'approved',UNIX_TIMESTAMP()-13*3600-87*60),
('rv_demo087','vantage','u_demo14',4,'整体不错，就是佣金账户手续费略高，适合量大的人。',30,'approved',UNIX_TIMESTAMP()-0*3600-88*60),
('rv_demo088','vantage','u_demo07',4,NULL,3,'approved',UNIX_TIMESTAMP()-1*3600-89*60),
('rv_demo089','vantage','u_demo01',3,NULL,20,'approved',UNIX_TIMESTAMP()-2*3600-90*60),
('rv_demo090','vantage','u_demo12',1,'离岸牌照为主，出了问题基本没处说理。',17,'approved',UNIX_TIMESTAMP()-3*3600-91*60),
('rv_demo091','vantage','u_demo03',2,'隔夜库存费偏高，长线拿着有点肉疼。',27,'approved',UNIX_TIMESTAMP()-4*3600-92*60),
('rv_demo092','vantage','u_demo04',1,NULL,35,'approved',UNIX_TIMESTAMP()-7*3600-93*60),
('rv_demo093','vantage','u_demo09',1,NULL,19,'approved',UNIX_TIMESTAMP()-8*3600-94*60),
('rv_demo094','vantage','u_demo10',5,NULL,32,'approved',UNIX_TIMESTAMP()-9*3600-95*60),
('rv_demo095','vantage','u_demo06',5,'监管齐全，资金分离做得到位，作为主账户放心。',14,'approved',UNIX_TIMESTAMP()-10*3600-96*60),
('rv_demo096','vantage','u_demo13',3,NULL,16,'approved',UNIX_TIMESTAMP()-11*3600-97*60),
('rv_demo097','vantage','u_demo00',4,NULL,10,'approved',UNIX_TIMESTAMP()-13*3600-98*60),
('rv_demo098','thinkmarkets','u_demo08',3,NULL,0,'approved',UNIX_TIMESTAMP()-0*3600-99*60),
('rv_demo099','thinkmarkets','u_demo02',2,'出金拖过一次，催了客服才到，体验一般。',25,'approved',UNIX_TIMESTAMP()-1*3600-100*60),
('rv_demo100','thinkmarkets','u_demo14',4,NULL,9,'approved',UNIX_TIMESTAMP()-3*3600-101*60),
('rv_demo101','thinkmarkets','u_demo03',4,NULL,11,'approved',UNIX_TIMESTAMP()-4*3600-102*60),
('rv_demo102','thinkmarkets','u_demo12',3,NULL,26,'approved',UNIX_TIMESTAMP()-5*3600-103*60),
('rv_demo103','thinkmarkets','u_demo04',4,NULL,32,'approved',UNIX_TIMESTAMP()-7*3600-104*60),
('rv_demo104','dukascopy','u_demo07',4,NULL,23,'approved',UNIX_TIMESTAMP()-0*3600-105*60),
('rv_demo105','dukascopy','u_demo09',4,NULL,12,'approved',UNIX_TIMESTAMP()-1*3600-106*60),
('rv_demo106','dukascopy','u_demo02',2,'滑点比宣传的明显，挂单经常成交在不利价。',16,'approved',UNIX_TIMESTAMP()-2*3600-107*60),
('rv_demo107','dukascopy','u_demo08',5,NULL,37,'approved',UNIX_TIMESTAMP()-3*3600-108*60),
('rv_demo108','dukascopy','u_demo10',5,NULL,34,'approved',UNIX_TIMESTAMP()-4*3600-109*60),
('rv_demo109','dukascopy','u_demo06',5,'监管齐全，资金分离做得到位，作为主账户放心。',20,'approved',UNIX_TIMESTAMP()-10*3600-110*60),
('rv_demo110','dukascopy','u_demo12',5,NULL,1,'approved',UNIX_TIMESTAMP()-13*3600-111*60),
('rv_demo111','dukascopy','u_demo03',5,'监管齐全，资金分离做得到位，作为主账户放心。',25,'approved',UNIX_TIMESTAMP()-14*3600-112*60),
('rv_demo112','swissquote','u_demo01',3,NULL,25,'approved',UNIX_TIMESTAMP()-0*3600-113*60),
('rv_demo113','swissquote','u_demo00',5,NULL,8,'approved',UNIX_TIMESTAMP()-1*3600-114*60),
('rv_demo114','swissquote','u_demo12',5,'监管齐全，资金分离做得到位，作为主账户放心。',19,'approved',UNIX_TIMESTAMP()-3*3600-115*60),
('rv_demo115','swissquote','u_demo07',5,'原始点差账户体验一流，滑点在可接受范围内，非农也没夸张扩点。',2,'approved',UNIX_TIMESTAMP()-4*3600-116*60),
('rv_demo116','swissquote','u_demo09',5,'用了两年多，服务器很少断线，客服中文在线响应快。',20,'approved',UNIX_TIMESTAMP()-5*3600-117*60),
('rv_demo117','swissquote','u_demo02',5,'入金到账快，出金也没设卡，属于让人省心的那种。',17,'approved',UNIX_TIMESTAMP()-6*3600-118*60),
('rv_demo118','swissquote','u_demo08',5,NULL,2,'approved',UNIX_TIMESTAMP()-7*3600-119*60),
('rv_demo119','swissquote','u_demo06',5,'监管齐全，资金分离做得到位，作为主账户放心。',30,'approved',UNIX_TIMESTAMP()-8*3600-120*60),
('rv_demo120','hfm','u_demo08',1,NULL,8,'approved',UNIX_TIMESTAMP()-0*3600-121*60),
('rv_demo121','hfm','u_demo10',2,'出金拖过一次，催了客服才到，体验一般。',21,'approved',UNIX_TIMESTAMP()-1*3600-122*60),
('rv_demo122','hfm','u_demo02',3,'不好不坏，出金正常但审核偶尔要一两天。',12,'approved',UNIX_TIMESTAMP()-2*3600-123*60),
('rv_demo123','hfm','u_demo12',1,'重仓时莫名断线，事后不认账，谨慎。',11,'approved',UNIX_TIMESTAMP()-3*3600-124*60),
('rv_demo124','hfm','u_demo07',4,'整体不错，就是佣金账户手续费略高，适合量大的人。',31,'approved',UNIX_TIMESTAMP()-4*3600-125*60),
('rv_demo125','hfm','u_demo13',2,NULL,3,'approved',UNIX_TIMESTAMP()-5*3600-126*60),
('rv_demo126','hfm','u_demo15',3,'点差浮动有点大，稳定性一般，先小仓试水。',5,'approved',UNIX_TIMESTAMP()-6*3600-127*60),
('rv_demo127','fxpro','u_demo07',4,'整体不错，就是佣金账户手续费略高，适合量大的人。',31,'approved',UNIX_TIMESTAMP()-0*3600-128*60),
('rv_demo128','fxpro','u_demo13',3,'不好不坏，出金正常但审核偶尔要一两天。',38,'approved',UNIX_TIMESTAMP()-1*3600-129*60),
('rv_demo129','fxpro','u_demo01',3,NULL,36,'approved',UNIX_TIMESTAMP()-3*3600-130*60),
('rv_demo130','fxpro','u_demo08',5,'监管齐全，资金分离做得到位，作为主账户放心。',9,'approved',UNIX_TIMESTAMP()-4*3600-131*60),
('rv_demo131','fxpro','u_demo06',4,'老牌子用着踏实，产品线广，新手学习成本略高。',11,'approved',UNIX_TIMESTAMP()-5*3600-132*60),
('rv_demo132','fxpro','u_demo09',3,'还行吧，非农和数据行情扩点比较明显，日内注意点。',1,'approved',UNIX_TIMESTAMP()-6*3600-133*60),
('rv_demo133','fxpro','u_demo05',2,'滑点比宣传的明显，挂单经常成交在不利价。',11,'approved',UNIX_TIMESTAMP()-8*3600-134*60),
('rv_demo134','fxpro','u_demo14',5,'监管齐全，资金分离做得到位，作为主账户放心。',30,'approved',UNIX_TIMESTAMP()-10*3600-135*60),
('rv_demo135','fxpro','u_demo15',3,'不好不坏，出金正常但审核偶尔要一两天。',23,'approved',UNIX_TIMESTAMP()-11*3600-136*60),
('rv_demo136','fxpro','u_demo03',3,'还行吧，非农和数据行情扩点比较明显，日内注意点。',22,'approved',UNIX_TIMESTAMP()-12*3600-137*60),
('rv_demo137','fxpro','u_demo12',4,'点差中规中矩，赠金活动挺多但记得看清出金条件。',7,'approved',UNIX_TIMESTAMP()-13*3600-138*60),
('rv_demo138','fxpro','u_demo11',4,'平台稳定，App 偶尔加载慢，其他没毛病。',15,'approved',UNIX_TIMESTAMP()-14*3600-139*60),
('rv_demo139','admiral','u_demo07',4,'整体不错，就是佣金账户手续费略高，适合量大的人。',31,'approved',UNIX_TIMESTAMP()-0*3600-140*60),
('rv_demo140','admiral','u_demo13',5,NULL,38,'approved',UNIX_TIMESTAMP()-1*3600-141*60),
('rv_demo141','admiral','u_demo03',3,'点差浮动有点大，稳定性一般，先小仓试水。',24,'approved',UNIX_TIMESTAMP()-2*3600-142*60),
('rv_demo142','admiral','u_demo00',5,'监管齐全，资金分离做得到位，作为主账户放心。',8,'approved',UNIX_TIMESTAMP()-3*3600-143*60),
('rv_demo143','admiral','u_demo08',5,'监管齐全，资金分离做得到位，作为主账户放心。',3,'approved',UNIX_TIMESTAMP()-5*3600-144*60),
('rv_demo144','admiral','u_demo10',4,'点差中规中矩，赠金活动挺多但记得看清出金条件。',30,'approved',UNIX_TIMESTAMP()-6*3600-145*60),
('rv_demo145','admiral','u_demo06',3,'还行吧，非农和数据行情扩点比较明显，日内注意点。',8,'approved',UNIX_TIMESTAMP()-7*3600-146*60),
('rv_demo146','admiral','u_demo09',3,'还行吧，非农和数据行情扩点比较明显，日内注意点。',31,'approved',UNIX_TIMESTAMP()-8*3600-147*60),
('rv_demo147','admiral','u_demo01',5,'原始点差账户体验一流，滑点在可接受范围内，非农也没夸张扩点。',24,'approved',UNIX_TIMESTAMP()-10*3600-148*60),
('rv_demo148','admiral','u_demo12',5,'用了两年多，服务器很少断线，客服中文在线响应快。',33,'approved',UNIX_TIMESTAMP()-11*3600-149*60),
('rv_demo149','admiral','u_demo15',5,'入金到账快，出金也没设卡，属于让人省心的那种。',14,'approved',UNIX_TIMESTAMP()-12*3600-150*60),
('rv_demo150','easymarkets','u_demo08',4,'点差中规中矩，赠金活动挺多但记得看清出金条件。',24,'approved',UNIX_TIMESTAMP()-0*3600-151*60),
('rv_demo151','easymarkets','u_demo02',2,NULL,33,'approved',UNIX_TIMESTAMP()-1*3600-152*60),
('rv_demo152','easymarkets','u_demo00',5,NULL,4,'approved',UNIX_TIMESTAMP()-2*3600-153*60),
('rv_demo153','easymarkets','u_demo12',3,NULL,25,'approved',UNIX_TIMESTAMP()-3*3600-154*60),
('rv_demo154','easymarkets','u_demo03',5,NULL,8,'approved',UNIX_TIMESTAMP()-4*3600-155*60),
('rv_demo155','doo-prime','u_demo05',2,NULL,29,'approved',UNIX_TIMESTAMP()-0*3600-156*60),
('rv_demo156','doo-prime','u_demo01',5,'原始点差账户体验一流，滑点在可接受范围内，非农也没夸张扩点。',8,'approved',UNIX_TIMESTAMP()-1*3600-157*60),
('rv_demo157','doo-prime','u_demo00',1,'出金一再拖延，客服踢皮球，已经在维权了。',36,'approved',UNIX_TIMESTAMP()-2*3600-158*60),
('rv_demo158','doo-prime','u_demo04',1,'出金一再拖延，客服踢皮球，已经在维权了。',4,'approved',UNIX_TIMESTAMP()-3*3600-159*60),
('rv_demo159','doo-prime','u_demo09',2,'出金拖过一次，催了客服才到，体验一般。',20,'approved',UNIX_TIMESTAMP()-4*3600-160*60),
('rv_demo160','doo-prime','u_demo02',3,'还行吧，非农和数据行情扩点比较明显，日内注意点。',12,'approved',UNIX_TIMESTAMP()-5*3600-161*60),
('rv_demo161','doo-prime','u_demo10',1,NULL,5,'approved',UNIX_TIMESTAMP()-8*3600-162*60),
('rv_demo162','doo-prime','u_demo06',2,'滑点比宣传的明显，挂单经常成交在不利价。',13,'approved',UNIX_TIMESTAMP()-10*3600-163*60),
('rv_demo163','doo-prime','u_demo13',2,'出金拖过一次，催了客服才到，体验一般。',31,'approved',UNIX_TIMESTAMP()-12*3600-164*60),
('rv_demo164','go-markets','u_demo01',5,'点差是真的稳，剥头皮没被砍单，出金隔天到账。',1,'approved',UNIX_TIMESTAMP()-0*3600-165*60),
('rv_demo165','go-markets','u_demo08',4,'整体不错，就是佣金账户手续费略高，适合量大的人。',20,'approved',UNIX_TIMESTAMP()-1*3600-166*60),
('rv_demo166','go-markets','u_demo06',5,NULL,17,'approved',UNIX_TIMESTAMP()-2*3600-167*60),
('rv_demo167','go-markets','u_demo09',5,'点差是真的稳，剥头皮没被砍单，出金隔天到账。',7,'approved',UNIX_TIMESTAMP()-3*3600-168*60),
('rv_demo168','go-markets','u_demo14',3,NULL,25,'approved',UNIX_TIMESTAMP()-4*3600-169*60),
('rv_demo169','go-markets','u_demo07',3,'不好不坏，出金正常但审核偶尔要一两天。',23,'approved',UNIX_TIMESTAMP()-5*3600-170*60),
('rv_demo170','go-markets','u_demo03',4,'点差中规中矩，赠金活动挺多但记得看清出金条件。',30,'approved',UNIX_TIMESTAMP()-8*3600-171*60);

UPDATE brokers b SET
  user_rating_count=(SELECT COUNT(*) FROM broker_reviews r WHERE r.broker_id=b.id AND r.status='approved' AND r.stars BETWEEN 1 AND 5),
  user_rating_avg=(SELECT ROUND(AVG(r.stars),2) FROM broker_reviews r WHERE r.broker_id=b.id AND r.status='approved' AND r.stars BETWEEN 1 AND 5);

INSERT INTO articles (id,cat_id,broker_id,title,summary,content,author,tags,views,status,publish_at) VALUES
('art_demo00',(SELECT id FROM article_cats WHERE slug='industry'),NULL,'2024 年零售外汇行业监管趋势：多国收紧杠杆上限','随着 ESMA 与 ASIC 相继调整零售杠杆政策，主流券商纷纷推出分层账户以适配不同司法辖区……','<p>随着 ESMA 与 ASIC 相继调整零售杠杆政策，主流券商纷纷推出分层账户以适配不同司法辖区……</p><p>（示例正文，正式上线后由后台编辑器撰写完整内容。）</p>','汇评测','industry,监管,杠杆',150,'published',UNIX_TIMESTAMP()-0*86400),
('art_demo01',(SELECT id FROM article_cats WHERE slug='regulation'),NULL,'FCA 更新受监管实体名录：如何用官方注册号自查一家券商','本文手把手教你在 FCA Register 用 FRN 号核对券商主体、业务范围与客户资金保护状态……','<p>本文手把手教你在 FCA Register 用 FRN 号核对券商主体、业务范围与客户资金保护状态……</p><p>（示例正文，正式上线后由后台编辑器撰写完整内容。）</p>','汇评测','FCA,监管查询,自查',151,'published',UNIX_TIMESTAMP()-1*86400),
('art_demo02',(SELECT id FROM article_cats WHERE slug='broker'),'ic-markets','IC Markets 出入金全流程实测：从电汇到加密通道要多久','我们用真实账户测试了 IC Markets 的六种出入金方式，记录每种通道的到账时间与手续费……','<p>我们用真实账户测试了 IC Markets 的六种出入金方式，记录每种通道的到账时间与手续费……</p><p>（示例正文，正式上线后由后台编辑器撰写完整内容。）</p>','汇评测','IC Markets,出入金,实测',152,'published',UNIX_TIMESTAMP()-2*86400),
('art_demo03',(SELECT id FROM article_cats WHERE slug='broker'),'exness','Exness 高杠杆机制解析：无限杠杆到底是怎么回事','Exness 的动态杠杆规则常被误读，本文从保证金计算角度拆解其“无限杠杆”的真实含义与风险……','<p>Exness 的动态杠杆规则常被误读，本文从保证金计算角度拆解其“无限杠杆”的真实含义与风险……</p><p>（示例正文，正式上线后由后台编辑器撰写完整内容。）</p>','汇评测','Exness,杠杆,风险',153,'published',UNIX_TIMESTAMP()-3*86400),
('art_demo04',(SELECT id FROM article_cats WHERE slug='original'),NULL,'如何看懂一家券商的“双牌照”：FCA + 离岸牌的真实含义','很多券商同时持有 FCA 与离岸牌照，你的账户到底归谁管？本文讲清主体归属与资金保护差异……','<p>很多券商同时持有 FCA 与离岸牌照，你的账户到底归谁管？本文讲清主体归属与资金保护差异……</p><p>（示例正文，正式上线后由后台编辑器撰写完整内容。）</p>','汇评测','双牌照,离岸,科普',154,'published',UNIX_TIMESTAMP()-4*86400),
('art_demo05',(SELECT id FROM article_cats WHERE slug='exposure'),NULL,'从一起出金纠纷看：遇到无法出金该如何有效维权','一位交易者的出金被拖延 40 天，我们复盘其维权全过程，总结出可复制的四步维权路径……','<p>一位交易者的出金被拖延 40 天，我们复盘其维权全过程，总结出可复制的四步维权路径……</p><p>（示例正文，正式上线后由后台编辑器撰写完整内容。）</p>','汇评测','维权,出金,曝光',155,'published',UNIX_TIMESTAMP()-5*86400),
('art_demo06',(SELECT id FROM article_cats WHERE slug='industry'),NULL,'点差、佣金、库存费：交易成本的三笔账你算清了吗','低点差不等于低成本，本文用一手欧美的完整持仓周期，拆解三类成本对最终盈亏的影响……','<p>低点差不等于低成本，本文用一手欧美的完整持仓周期，拆解三类成本对最终盈亏的影响……</p><p>（示例正文，正式上线后由后台编辑器撰写完整内容。）</p>','汇评测','交易成本,点差,库存费',156,'published',UNIX_TIMESTAMP()-6*86400),
('art_demo07',(SELECT id FROM article_cats WHERE slug='broker'),'pepperstone','Pepperstone Razor 账户点差实测:与标准账户差多少','连续五个交易日抓取两类账户的欧美、黄金点差，用数据说明 Razor 账户的真实成本优势……','<p>连续五个交易日抓取两类账户的欧美、黄金点差，用数据说明 Razor 账户的真实成本优势……</p><p>（示例正文，正式上线后由后台编辑器撰写完整内容。）</p>','汇评测','Pepperstone,点差,实测',157,'published',UNIX_TIMESTAMP()-7*86400);

INSERT INTO complaints (id,broker_id,broker_name,user_id,nickname,type,title,content,loss_amount,status,admin_reply,resolved_amount,views,created_at) VALUES
('cp_demo00','doo-prime','','u_demo00','老K交易笔记','无法出金','申请出金 3000 美金，30 天仍未到账','10 月初提交出金申请，平台先以“风控审核”为由拖延，后要求补充资料，至今未到账。已保留全部聊天记录。',3000,'pending',NULL,NULL,30,UNIX_TIMESTAMP()-0*43200),
('cp_demo01','hfm','','u_demo01','江海一舟','滑点严重','非农行情挂止损被滑点 30 点成交','非农数据公布时，我的止损单在明显偏离的价格成交，滑点远超正常范围，客服称属市场波动。',800,'processing',NULL,NULL,67,UNIX_TIMESTAMP()-1*43200),
('cp_demo02',NULL,'某不明平台','u_demo02','割肉了阿强','诱导欺诈','自称“XX 资管”喊单群诱导入金到不明平台','被拉进喊单群，“老师”带单前几单盈利，加大入金后连续爆仓，平台无任何监管信息。',12000,'pending',NULL,NULL,104,UNIX_TIMESTAMP()-2*43200),
('cp_demo03','exness','','u_demo03','BostonScalper','其他','入金渠道显示成功但账户未到账','通过第三方渠道入金，银行已扣款但 MT5 账户未显示，提交工单后 2 天解决并补偿手续费。',500,'resolved','经核实为第三方通道延迟，平台已补入并赔付手续费，纠纷解决。',500,141,UNIX_TIMESTAMP()-3*43200),
('cp_demo04',NULL,'某离岸券商','u_demo04','不再抄底的鱼','虚假宣传','宣称“FCA 监管”实际为离岸主体','广告页标注受 FCA 监管，核对 FRN 号后发现该号对应主体业务范围不含差价合约，涉嫌误导。',0,'processing',NULL,NULL,178,UNIX_TIMESTAMP()-4*43200),
('cp_demo05','doo-prime','','u_demo05','点差猎人','恶意喊单','客户经理频繁电话催促加仓','开户后客户经理每天来电劝说加大仓位、追加入金，交易亏损后失联。',4500,'pending',NULL,NULL,215,UNIX_TIMESTAMP()-5*43200);

UPDATE brokers b SET complaint_count=(SELECT COUNT(*) FROM complaints c WHERE c.broker_id=b.id AND c.status<>'rejected');

-- 演示会员密码统一为 demo1234