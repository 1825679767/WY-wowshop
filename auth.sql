CREATE TABLE web购买记录 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    账号 VARCHAR(255) NOT NULL,
    角色名字 VARCHAR(255) NOT NULL,
    商品ID INT NOT NULL,
    商品名字 VARCHAR(255) NOT NULL,
    花费金币 DECIMAL(10, 2) NOT NULL,
    购买时间 TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE 商品信息 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    商品ID INT NOT NULL,
    分类 VARCHAR(255) NOT NULL,
    图片 VARCHAR(255) NOT NULL,
    名称 VARCHAR(255) NOT NULL,
    描述 TEXT NOT NULL,
    数量 INT NOT NULL,
    价格 DECIMAL(10, 2) NOT NULL
);


INSERT INTO 商品信息 (商品ID, 分类, 图片, 名称, 描述, 数量, 价格) VALUES
(2454, '武器', '/static/lb.png', '寒冰之刃', '一把散发着寒气的魔法剑', 10, 1000.00),
(2454, '护甲', '/static/lb.png', '光明守护者胸甲', '提供强大防御的板甲', 5, 1500.00),
(2454, '药水', '/static/lb.png', '强效治疗药水', '恢复大量生命值的药水', 20, 50.00),
(2454, '坐骑', '/static/lb.png', '黑色战马', '一匹强壮的战马，适合战斗', 2, 5000.00),
(2454, '宠物', '/static/lb.png', '火焰小龙', '一只可爱的宠物龙，能喷火', 8, 300.00),
(2454, '武器', '/static/lb.png', '圣光之剑', '一把充满圣光力量的长剑', 3, 2000.00),
(2454, '护甲', '/static/lb.png', '奥术师之袍', '增强魔法力量的法师长袍', 7, 1200.00),
(2454, '药水', '/static/lb.png', '法力恢复药水', '快速恢复法力值的药水', 30, 40.00),
(2454, '坐骑', '/static/lb.png', '风暴狮鹫', '可以飞行的狮鹫坐骑', 1, 8000.00),
(2454, '宠物', '/static/lb.png', '淡水鱼人', '来自河流的友好鱼人宠物', 15, 200.00),
(2454, '武器', '/static/lb.png', '狂暴战斧', '适合狂暴战士的巨型战斧', 4, 1800.00),
(2454, '护甲', '/static/lb.png', '影行者护甲', '适合潜行者的轻型护甲', 6, 1300.00),
(2454, '药水', '/static/lb.png', '自然之力药剂', '临时增加自然系法术威力的药剂', 25, 60.00),
(2454, '坐骑', '/static/lb.png', '工程学飞行器', '由工程学制造的高速飞行器', 2, 6000.00),
(2454, '宠物', '/static/lb.png', '小火花', '一只活泼的火元素宠物', 10, 250.00),
(2454, '武器', '/static/lb.png', '埃提耶什，守护者的传说之杖', '强大的法师武器，蕴含无尽奥术能量', 1, 10000.00),
(2454, '护甲', '/static/lb.png', '暗影编织长袍', '增强暗影魔法的精美长袍', 3, 2000.00),
(2454, '药水', '/static/lb.png', '隐形药水', '使用后短时间内隐形的神奇药水', 15, 100.00),
(2454, '坐骑', '/static/lb.png', '织锦飞毯', '来自赞达拉的魔法飞行坐骑', 3, 7000.00),
(2454, '宠物', '/static/lb.png', '机械松鼠', '由侏儒工程师制造的可爱机械宠物', 12, 350.00);