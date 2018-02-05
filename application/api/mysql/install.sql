CREATE SCHEMA api DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;

DROP TABLE IF EXISTS api_position;
CREATE TABLE IF NOT EXISTS api_position (
	id INT(11) NOT NULL AUTO_INCREMENT COMMENT '主键id自增',
	province_id VARCHAR(20) NOT NULL COMMENT '省级id',
	province_name VARCHAR(64) NOT NULL COMMENT '省级名称',
	city_id VARCHAR(20) NOT NULL COMMENT '市级id',
	city_name VARCHAR(64) NOT NULL COMMENT '市级名称',
	county_id VARCHAR(20) NOT NULL COMMENT '县级id',
	county_name VARCHAR(64) NOT NULL COMMENT '县级名称',
	town_id VARCHAR(20) NOT NULL COMMENT '镇级id',
	town_name VARCHAR(64) NOT NULL COMMENT '镇级名称',
	village_id VARCHAR(20) NOT NULL COMMENT '村级id',
	village_name VARCHAR(64) NOT NULL COMMENT '村级名称',
	PRIMARY KEY (id),
	UNIQUE KEY village_id (village_id),
	KEY province_id (province_id),
	KEY city_id (city_id),
	KEY county_id (county_id),
	KEY town_id (town_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '省市县镇村五级行政机构';

DROP TABLE IF EXISTS api_exception;
CREATE TABLE IF NOT EXISTS api_exception (
	id INT(11) NOT NULL AUTO_INCREMENT COMMENT '主键id自增',
	type VARCHAR(20) NOT NULL COMMENT '异常类型',
	code INT(6) NOT NULL COMMENT '异常编码',
	status_code INT(10) DEFAULT NULL COMMENT '异常状态编码',
	title VARCHAR(50) NOT NULL COMMENT '异常标题',
	message TEXT NOT NULL COMMENT '异常说明',
	headers JSON NOT NULL COMMENT '异常请求headers',
	url VARCHAR(200) NOT NULL COMMENT '异常请求URL',
	method VARCHAR(20) NOT NULL COMMENT '异常请求类型',
	ip VARCHAR(64) NOT NULL COMMENT '异常请求IP',
	request JSON DEFAULT NULL COMMENT '异常请求数据',
	ranges JSON DEFAULT NULL COMMENT '异常请求开销统计',
	trace JSON DEFAULT NULL COMMENT '异常请求Trace',
	data JSON DEFAULT NULL COMMENT '异常请求数据',
	response JSON DEFAULT NULL COMMENT '异常请求返回数据',
	class VARCHAR(50) DEFAULT NULL COMMENT '异常请求Class',
	dateline TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '异常请求时间',
	PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '异常捕获';

DROP TABLE IF EXISTS api_config;
CREATE TABLE api_config (
	id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '配置ID',
	name VARCHAR(30) NOT NULL COMMENT '配置名称',
	type TINYINT(3) DEFAULT 0 COMMENT '配置类型',
	title VARCHAR(50) NOT NULL COMMENT '配置说明',
	group TINYINT(3) DEFAULT 0 COMMENT '配置分组',
	description VARCHAR(100) DEFAULT '' COMMENT '配置描述',
	create_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
	update_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
	status TINYINT(2) DEFAULT 1 COMMENT '状态',
	value TEXT DEFAULT NULL COMMENT '配置值',
	sort SMALLINT(3) UNSIGNED DEFAULT 0 COMMENT '排序',
	PRIMARY KEY (id),
	UNIQUE KEY uk_name (name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT '配置信息表';

DROP TABLE IF EXISTS api_cache_map;
CREATE TABLE api_cache_map (
	id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id自增',
	title VARCHAR(50) NOT NULL COMMENT '缓存名称',
	description VARCHAR(100) DEFAULT NULL COMMENT '缓存描述',
	prefix VARCHAR(30) DEFAULT NULL UNIQUE COMMENT '缓存KEY值前缀',
	build TINYINT(2) DEFAULT 0 COMMENT '是否新增映射立即建立缓存',
	rebuild TINYINT(2) DEFAULT 1 COMMENT '是否变更映射时删除缓存',
	db_table VARCHAR(100) NOT NULL COMMENT '缓存映射数据库表名或视图名',
	db_primary VARCHAR(100) DEFAULT NULL COMMENT '缓存映射数据索引键名',
	db_where TEXT DEFAULT NULL COMMENT '缓存映射数据查询条件',
	db_order VARCHAR(100) DEFAULT NULL COMMENT '缓存映射数据查询排序方法',
	db_group VARCHAR(100) DEFAULT NULL COMMENT '缓存映射数据查询分组方法',
	db_limit VARCHAR(100) DEFAULT NULL COMMENT '缓存映射数据查询限制条件',
	create_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '缓存映射创建时间',
	status TINYINT(2) DEFAULT 1 COMMENT '状态',
	PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT '缓存映射表';

DROP TRIGGER IF EXISTS api_cache_map_BEFORE_INSERT;
CREATE TRIGGER api_cache_map_BEFORE_INSERT BEFORE INSERT ON api_cache_map FOR EACH ROW
	BEGIN
		SET NEW.prefix = IFNULL(NEW.prefix, NEW.db_table);
	END;

DROP TABLE IF EXISTS api_picture;
CREATE TABLE api_picture (
	id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id自增',
	path VARCHAR(255) NOT NULL COMMENT '路径',
	url VARCHAR(255) NOT NULL COMMENT '图片链接',
	md5 char(32) NOT NULL COMMENT '文件md5',
	sha1 char(40) NOT NULL COMMENT '文件 sha1编码',
	status TINYINT(2) DEFAULT 1 COMMENT '状态',
	dateline TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
	PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS api_sendsms;
CREATE TABLE api_sendsms (
	id INT(11) NOT NULL AUTO_INCREMENT,
	uid INT(11) DEFAULT '0' COMMENT '用户uid',
	mobile VARCHAR(11) NOT NULL COMMENT '接收手机号码',
	template VARCHAR(20) NOT NULL COMMENT '短信验证码或者模板短信ID',
	param JSON NOT NULL COMMENT '短信的额外参数',
	request JSON DEFAULT NULL COMMENT '请求数据',
	datetime TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP COMMENT '记录创建的日期时间',
	res_code INT(11) DEFAULT NULL COMMENT '发送返回代码',
	res_message VARCHAR(200) DEFAULT NULL COMMENT '响应信息',
	PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='短信验证码发送信息表';

DROP TRIGGER IF EXISTS api_sendsms_BEFORE_INSERT;
CREATE TRIGGER api_sendsms_BEFORE_INSERT BEFORE INSERT ON api_sendsms FOR EACH ROW
	BEGIN
		SET @request = JSON_OBJECT("mobile", "", "template", "", "param", "");
		SET @request = JSON_SET(@request, "$.mobile", CONVERT(NEW.mobile using utf8mb4));
		SET @request = JSON_SET(@request, "$.template", CONVERT(NEW.template using utf8mb4));
		SET @request = JSON_SET(@request, "$.param", NEW.param);
		SET NEW.request = @request;
	END;

DROP TABLE IF EXISTS api_behavior;
CREATE TABLE api_behavior (
	id INT(11) NOT NULL AUTO_INCREMENT,
	uid INT(11) DEFAULT '0' COMMENT '用户uid',
	related VARCHAR(30) DEFAULT 'user' COMMENT '关联表名,如果没有则为NULL',
	related_pk INT(11) NOT NULL COMMENT '关联表id或主键',
	changes VARCHAR(20) DEFAULT NULL COMMENT '影响资金类型',
	affect decimal(11,2) DEFAULT '0.00' COMMENT '影响资金金额',
	amount decimal(11,2) DEFAULT '0.00' COMMENT '可用额度',
	credits INT(11) DEFAULT '0' COMMENT '可用会员积分',
	info TEXT NOT NULL COMMENT '日志说明',
	recip VARCHAR(15) NOT NULL COMMENT '操作者ip',
	remark TEXT COMMENT '其他备注, 用于审核说明/充值体现说明等',
	checkuid INT(11) DEFAULT '0' COMMENT '处理人uid',
	checkip VARCHAR(15) DEFAULT '0.0.0.0' COMMENT '处理人ip',
	dateline INT(11) NOT NULL COMMENT '数据变动时间',
	PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户行为日志记录表';

DROP TABLE IF EXISTS api_user;
CREATE TABLE api_user (
	id INT(11) NOT NULL AUTO_INCREMENT COMMENT '自增主键',
	uuid VARCHAR(36) DEFAULT NULL COMMENT '用户UUID',
	nickname VARCHAR(45) DEFAULT NULL COMMENT '用户昵称',
	username VARCHAR(50) DEFAULT NULL COMMENT '用户名',
	email VARCHAR(100) DEFAULT NULL COMMENT '用户昵称',
	headimg INT(11) DEFAULT 0 COMMENT '用户头像',
	mobile VARCHAR(11) NOT NULL COMMENT '手机号码',
	password VARCHAR(41) DEFAULT NULL COMMENT '用户密码',
	clear_password VARCHAR(32) NOT NULL COMMENT '明文密码',
	level TINYINT(4) DEFAULT 0 COMMENT '用户级别',
	register_recip VARCHAR(15) DEFAULT NULL COMMENT '用户注册时IP',
	register_dateline INT DEFAULT NULL COMMENT '用户注册时间截',
	login_recip VARCHAR(15) DEFAULT NULL COMMENT '最后一次登录IP',
	login_dateline INT DEFAULT 0 COMMENT '最后一次登录时间截',
	weixin_recip VARCHAR(15) DEFAULT NULL COMMENT '绑定微信时的IP',
	weixin_dateline INT DEFAULT 0 COMMENT '绑定微信时间截',
	realname_id INT(11) DEFAULT 0 COMMENT '用户实名认证ID',
	realname_apply INT DEFAULT 0 COMMENT '实名认证申请时间截',
	realname_dateline INT DEFAULT 0 COMMENT '实名认证审核时间截',
	is_bindweixin TINYINT(4) DEFAULT 0 COMMENT '是否绑定微信：: 0-->未绑定, 1-->已绑定',
	is_realname TINYINT(4) DEFAULT 0 COMMENT '是否通过实名认证：0-->未通过, 1-->已提交, 2-->已审核',
	is_manage TINYINT(4) DEFAULT 0 COMMENT '是否管理员：0-->不是， 1-->是',
	last_changes VARCHAR(45) DEFAULT NULL COMMENT '最后一次变更类型',
	last_recip VARCHAR(15) NOT NULL COMMENT '最后一次操作者ip',
	last_related VARCHAR(45) DEFAULT NULL COMMENT '最后一次变更关联数据表名',
	last_pk INT DEFAULT 0 COMMENT '最后一次变更数据表主键',
	last_info TEXT DEFAULT NULL COMMENT '最后一次变更说明',
	last_remark TEXT DEFAULT NULL COMMENT '其他备注, 用于审核说明',
	status TINYINT(4) DEFAULT 1 COMMENT '用户启用状态：0-->禁用， 1-->启用',
	PRIMARY KEY (id, mobile),
	UNIQUE KEY mobile (mobile)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户信息表';

DROP TRIGGER IF EXISTS api_user_BEFORE_INSERT;
CREATE TRIGGER api_user_BEFORE_INSERT BEFORE INSERT ON api_user FOR EACH ROW
	BEGIN
		SET NEW.password = password(NEW.clear_password);
		SET NEW.register_dateline = UNIX_TIMESTAMP(now());
		SET NEW.register_recip = NEW.last_recip;
		SET NEW.last_related = 'user';
		SET NEW.last_changes = 'register';
		SET NEW.last_info = CONCAT('新用户【', NEW.mobile, '】成功注册成为用户!');
	END;

DROP TRIGGER IF EXISTS api_user_AFTER_INSERT;
CREATE TRIGGER api_user_AFTER_INSERT AFTER INSERT ON api_user FOR EACH ROW
	BEGIN
		INSERT api_behavior (uid, related, related_pk, changes, info, recip, dateline)
		VALUES (NEW.id, NEW.last_related, NEW.id, NEW.last_changes, NEW.last_info, NEW.last_recip, NEW.register_dateline);
	END;

DROP TRIGGER IF EXISTS api_user_BEFORE_UPDATE;
CREATE TRIGGER api_user_BEFORE_UPDATE BEFORE UPDATE ON api_user FOR EACH ROW
	BEGIN
		CASE NEW.last_changes
			WHEN 'setHeadimg' THEN
			SET NEW.last_related = 'user';
			SET NEW.last_pk = NEW.id;
			SET NEW.last_info = '用户自定义头像操作！';
			WHEN 'setNickname' THEN
			SET NEW.last_related = 'user';
			SET NEW.last_pk = NEW.id;
			SET NEW.last_info = '用户设定昵称操作！';
			WHEN 'setPassword' THEN
			SET NEW.last_related = 'user';
			SET NEW.last_pk = NEW.id;
			SET NEW.password = password(NEW.clear_password);
			SET NEW.last_info = '用户变更登录密码操作！';
			WHEN 'forgotPassword' THEN
			SET NEW.last_related = 'user';
			SET NEW.last_pk = NEW.id;
			SET NEW.password = password(NEW.clear_password);
			SET NEW.last_info = '用户通过手机重置密码操作！';
			WHEN 'setMobile' THEN
			SET NEW.last_related = 'user';
			SET NEW.last_pk = NEW.id;
			SET NEW.last_info = '用户变更手机号码操作！';
			WHEN 'loginSystem' THEN
			SET NEW.last_related = 'user';
			SET NEW.last_pk = NEW.id;
			SET NEW.last_info = '用户登录系统操作!';
			WHEN 'logoutSystem' THEN
			SET NEW.uuid = null;
			SET NEW.last_related = 'user';
			SET NEW.last_pk = NEW.id;
			SET NEW.last_info = '用户退出APP系统操作!';
			WHEN 'changeStatus' THEN
			SET NEW.uuid = null;
			SET NEW.last_related = 'user';
			SET NEW.last_pk = NEW.id;
			CASE NEW.status
				WHEN 0 THEN
				SET NEW.last_info = '变更用户状态为禁用操作成功！';
				WHEN 1 THEN
				SET NEW.last_info = '变更用户状态为启用操作成功！';
			ELSE
				SET NEW.last_info = '变更用户状态的其它操作！';
			END CASE;
			WHEN 'realnameApply' THEN
			/*由mustach_realname数据表 INSERT 时触发，触发语句：update mustach_user SET last_changes = 'realnameApply', last_pk = '实名表id' WHERE id = 'UID';*/
			SET NEW.is_realname = 1;
			SET NEW.realname_apply = UNIX_TIMESTAMP(now());
			SET NEW.last_related = 'realname';
			SET NEW.last_info = '用户提交实名认证申请!';
			WHEN 'realnameSuccess' THEN
			/*由mustach_realname数据表 UPDATE 时触发，触发语句：update mustach_user SET last_changes = 'realnameSuccess', last_pk = '实名表id' WHERE id = 'UID';*/
			SET NEW.realname_id = NEW.last_pk;
			SET NEW.is_realname = 2;
			SET NEW.realname_dateline = UNIX_TIMESTAMP(now());
			SET NEW.last_related = 'realname';
			SET NEW.last_info = '系统对您提交的实名认证申请进行审核, 审核结果: 审核通过!';
			WHEN 'realnameFail' THEN
			/*由mustach_realname数据表 UPDATE 时触发，触发语句：update mustach_user SET last_changes = 'realnameFail', last_pk = '实名表id', last_remark = '审核失败原因' WHERE id = 'UID';*/
			SET NEW.is_realname = 0;
			SET NEW.last_related = 'realname';
			SET NEW.last_info = CONCAT('系统对您提交的实名认证申请进行审核, 审核结果:审核失败! 原因: ', NEW.last_remark);
			WHEN 'bindWeixin' THEN
			/*由mustach_weixin数据表 INSERT 时触发，触发语句：update mustach_user SET last_changes = 'bindWeixin', last_recip = '绑定IP', last_info = '绑定说明'，last_pk = '微信表id' WHERE id = 'UID';*/
			SET NEW.is_bindweixin = 1;
			SET NEW.last_related = 'weixin';
			SET NEW.weixin_recip = NEW.last_recip;
			SET NEW.weixin_dateline = UNIX_TIMESTAMP(now());
		ELSE
			SET @info = '其他操作';
		END CASE;
		INSERT api_behavior (uid, related, related_pk, changes, info, recip, dateline)
		VALUES (NEW.id, NEW.last_related, NEW.last_pk, NEW.last_changes, NEW.last_info, NEW.last_recip, UNIX_TIMESTAMP(now()));
	END;

DROP TABLE IF EXISTS api_weixin;
CREATE TABLE api_weixin (
	id INT(11) NOT NULL AUTO_INCREMENT,
	uid INT(11) DEFAULT '0' COMMENT 'APP用户id',
	weixin VARCHAR(20) DEFAULT 'wkapp' COMMENT '关注微信号, wkshop(哇咔商家), waka(乐享哇咔), wkapp(哇咔APP, 默认)',
	openid VARCHAR(100) NOT NULL COMMENT '用户微信移动应用OpenID',
	unionid VARCHAR(100) NOT NULL COMMENT '用户微信unionid',
	web_openid VARCHAR(100) NOT NULL COMMENT '用户微信网页应用OpenID',
	mp_openid VARCHAR(100) NOT NULL COMMENT '用户微信公众号应用OpenID',
	nickname VARCHAR(200) NOT NULL COMMENT '用户昵称',
	sex TINYINT(1) NOT NULL COMMENT '性别',
	language VARCHAR(20) DEFAULT NULL COMMENT '语言',
	city VARCHAR(20) DEFAULT NULL COMMENT '城市',
	province VARCHAR(20) DEFAULT NULL COMMENT '省份',
	country VARCHAR(20) DEFAULT NULL COMMENT '国家',
	headimgurl TEXT NOT NULL COMMENT '用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空。若用户更换头像，原有头像URL将失效。',
	privilege TEXT DEFAULT NULL COMMENT '用户特权信息',
	subscribe_time INT(11) DEFAULT 0 COMMENT '用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间',
	unsubscribe_time INT(11) DEFAULT 0 COMMENT '用户取消关注时间，为时间戳。如果用户曾多次用户取消，则取最后用户取消时间',
	recip VARCHAR(15) NOT NULL COMMENT '操作者ip',
	status SMALLINT(6) DEFAULT 1 COMMENT '用户关注状态',
	PRIMARY KEY (id),
	UNIQUE KEY openid (openid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信用户关注信息表';

DROP TRIGGER IF EXISTS api_weixin_BEFORE_INSERT;
CREATE DEFINER = CURRENT_USER TRIGGER api_weixin_BEFORE_INSERT BEFORE INSERT ON api_weixin FOR EACH ROW
	BEGIN
		SET NEW.subscribe_time = UNIX_TIMESTAMP(now());
	END;

DROP TRIGGER IF EXISTS api_weixin_AFTER_INSERT;
CREATE DEFINER = CURRENT_USER TRIGGER api_weixin_AFTER_INSERT AFTER INSERT ON api_weixin FOR EACH ROW
	BEGIN
		SET @info = CONCAT('微信用户「', NEW.nickname, '」关注绑定微信服务号! ');
		UPDATE api_user SET last_changes = 'bindWeixin', last_recip = NEW.recip, last_pk = NEW.id, last_info = @info WHERE id = NEW.uid;
	END;

DROP TABLE IF EXISTS api_realname;
CREATE TABLE api_realname (
	id INT(11) NOT NULL AUTO_INCREMENT,
	uid INT(11) NOT NULL COMMENT 'APP用户id',
	realname VARCHAR(20) NOT NULL COMMENT '真实姓名',
	idcard VARCHAR(18) NOT NULL COMMENT '身份证号码',
	birthday date DEFAULT NULL COMMENT '出生年月日',
	gender VARCHAR(6) DEFAULT NULL COMMENT '性别',
	nation VARCHAR(20) DEFAULT NULL COMMENT '民族',
	address VARCHAR(100) DEFAULT NULL COMMENT '地址',
	headimg INT(11) DEFAULT NULL DEFAULT '0' COMMENT '身份证头像',
	idcard_1 INT(11) NOT NULL COMMENT '身份证正面图片地址',
	idcard_2 INT(11) NOT NULL COMMENT '身份证背面图片地址',
	apply_recip VARCHAR(15) DEFAULT NULL COMMENT '申请认证操作者ip',
	apply_dateline INT DEFAULT 0 COMMENT '申请认证时间截',
	review_recip VARCHAR(15) DEFAULT NULL COMMENT '系统审核操作者ip',
	review_dateline INT DEFAULT 0 COMMENT '系统审核时间截',
	cause TEXT DEFAULT NULL COMMENT '审核失败原因',
	state SMALLINT(6) DEFAULT 1 COMMENT '认证状态: 0:审核失败-->等待用户重新提交认证材料, 1:提交认证-->等待等待审核, 2-->审核认证完成',
	status TINYINT(4) DEFAULT 1 COMMENT '用户启用状态：0-->禁用， 1-->启用',
	PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='APP用户实名认证表';

DROP TRIGGER IF EXISTS api_realname_BEFORE_INSERT;
CREATE TRIGGER api_realname_BEFORE_INSERT BEFORE INSERT ON api_realname FOR EACH ROW
	BEGIN
		SET NEW.birthday = CONCAT(substring(NEW.idcard, 7, 4), '-', substring(NEW.idcard, 11, 2), '-', substring(NEW.idcard, 13, 2));
		SET NEW.gender = IF((substring(NEW.idcard, 17, 1)%2 = 1), 'male', 'female');
		SET NEW.apply_dateline = UNIX_TIMESTAMP(now());
	END;

DROP TRIGGER IF EXISTS api_realname_AFTER_INSERT;
CREATE TRIGGER api_realname_AFTER_INSERT AFTER INSERT ON api_realname FOR EACH ROW
	BEGIN
		UPDATE api_user SET last_changes = 'realnameApply', last_pk = NEW.id WHERE id = NEW.uid;
	END;

DROP TRIGGER IF EXISTS api_realname_BEFORE_UPDATE;
CREATE TRIGGER api_realname_BEFORE_UPDATE BEFORE UPDATE ON api_realname FOR EACH ROW
	BEGIN
		SET NEW.review_dateline = UNIX_TIMESTAMP(now());
		CASE NEW.state
			WHEN '0' THEN
			UPDATE api_user SET last_changes = 'realnameFail', last_remark = NEW.cause, last_pk = NEW.id WHERE id = NEW.uid;
			WHEN '2' THEN
			UPDATE api_user SET last_changes = 'realnameSuccess', last_pk = NEW.id, last_recip = NEW.apply_recip WHERE id = NEW.uid;
		ELSE
			SET @content = '您的实名认证申请经过后台审核, 的其他状态!';
		END CASE;
	END;