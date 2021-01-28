DROP TABLE IF EXISTS `pre__lakeadmin_admin`;
CREATE TABLE `pre__lakeadmin_admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` varchar(20) NOT NULL DEFAULT '' COMMENT '管理账号',
  `password` varchar(32) DEFAULT NULL COMMENT '管理密码',
  `encrypt` varchar(6) DEFAULT NULL COMMENT '加密因子',
  `nickname` varchar(50) NOT NULL COMMENT '昵称',
  `email` varchar(40) DEFAULT NULL,
  `avatar` varchar(32) DEFAULT NULL COMMENT '头像',
  `is_system` tinyint(1) DEFAULT '0' COMMENT '1-系统',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `last_login_time` int(10) DEFAULT '0' COMMENT '最后登录时间',
  `last_login_ip` varchar(50) DEFAULT '0' COMMENT '最后登录IP',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `add_ip` varchar(50) DEFAULT '' COMMENT '添加IP',
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员表';

DROP TABLE IF EXISTS `pre__lakeadmin_admin_log`;
CREATE TABLE `pre__lakeadmin_admin_log` (
  `id` char(32) NOT NULL DEFAULT '' COMMENT '日志ID',
  `admin_id` char(32) NOT NULL DEFAULT '0' COMMENT '管理账号ID',
  `admin_username` varchar(250) DEFAULT '' COMMENT '管理账号',
  `method` varchar(250) NOT NULL DEFAULT '' COMMENT '请求类型',
  `url` text NOT NULL,
  `info` text COMMENT '内容信息',
  `useragent` text COMMENT 'User-Agent',
  `ip` varchar(50) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` int(10) NOT NULL DEFAULT '0',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `add_ip` varchar(50) DEFAULT '' COMMENT '添加IP',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='操作日志';

DROP TABLE IF EXISTS `pre__lakeadmin_attachment`;
CREATE TABLE `pre__lakeadmin_attachment` (
  `id` char(32) NOT NULL DEFAULT '',
  `module` varchar(250) NOT NULL DEFAULT '' COMMENT '模块名，由哪个模块上传的',
  `type` varchar(50) DEFAULT '' COMMENT '附件关联类型',
  `type_id` char(32) DEFAULT '0' COMMENT '关联类型ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '文件名',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '文件路径',
  `thumb` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图路径',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '文件链接',
  `mime` varchar(100) NOT NULL DEFAULT '' COMMENT '文件mime类型',
  `ext` varchar(10) NOT NULL DEFAULT '' COMMENT '文件类型',
  `size` varchar(100) NOT NULL DEFAULT '0' COMMENT '文件大小',
  `md5` char(32) NOT NULL DEFAULT '' COMMENT '文件md5',
  `sha1` char(40) NOT NULL DEFAULT '' COMMENT 'sha1 散列值',
  `driver` varchar(16) NOT NULL DEFAULT 'public' COMMENT '上传驱动',
  `listorder` int(10) NOT NULL DEFAULT '100' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '上传时间',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `add_ip` varchar(50) DEFAULT '' COMMENT '添加IP',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='附件表';

DROP TABLE IF EXISTS `pre__lakeadmin_auth_group`;
CREATE TABLE `pre__lakeadmin_auth_group` (
  `id` char(32) NOT NULL DEFAULT '' COMMENT '用户组id',
  `parentid` char(32) NOT NULL DEFAULT '0' COMMENT '父组别',
  `module` varchar(250) NOT NULL DEFAULT '' COMMENT '用户组所属模块',
  `type` tinyint(3) NOT NULL COMMENT '组类型',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '用户组中文名称',
  `description` varchar(80) NOT NULL DEFAULT '' COMMENT '描述信息',
  `listorder` int(10) NOT NULL DEFAULT '100' COMMENT '排序ID',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-系统默认角色',
  `is_root` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-超级管理组',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `add_ip` varchar(50) DEFAULT '' COMMENT '添加IP',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='权限组表';

DROP TABLE IF EXISTS `pre__lakeadmin_auth_group_access`;
CREATE TABLE `pre__lakeadmin_auth_group_access` (
  `admin_id` char(32) NOT NULL DEFAULT '0',
  `group_id` char(32) NOT NULL DEFAULT '0',
  UNIQUE KEY `admin_id` (`admin_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员与用户组关联表';

DROP TABLE IF EXISTS `pre__lakeadmin_auth_rule`;
CREATE TABLE `pre__lakeadmin_auth_rule` (
  `id` char(32) NOT NULL DEFAULT '' COMMENT '规则id',
  `module` varchar(250) NOT NULL DEFAULT '' COMMENT '规则所属module',
  `parentid` char(32) DEFAULT NULL COMMENT '上级分类ID',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '规则中文描述',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '规则唯一英文标识',
  `parameter` text COMMENT '附加参数',
  `fields` mediumtext COMMENT '扩展权限字段',
  `condition` varchar(300) DEFAULT '' COMMENT '规则附加条件',
  `method` varchar(10) NOT NULL DEFAULT '' COMMENT '请求类型',
  `icon` varchar(64) DEFAULT '' COMMENT '图标',
  `tip` varchar(255) DEFAULT '' COMMENT '提示',
  `type` tinyint(1) DEFAULT '1' COMMENT '1-url;2-主菜单',
  `listorder` int(10) NOT NULL DEFAULT '100' COMMENT '排序ID',
  `is_menu` tinyint(1) DEFAULT '1' COMMENT '菜单显示',
  `is_need_auth` tinyint(1) DEFAULT '1' COMMENT '是否验证权限',
  `is_system` tinyint(1) DEFAULT '0' COMMENT '1-系统权限',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `add_ip` varchar(50) DEFAULT '' COMMENT '添加IP',
  PRIMARY KEY (`id`),
  KEY `module` (`module`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='规则表';

DROP TABLE IF EXISTS `pre__lakeadmin_auth_rule_access`;
CREATE TABLE `pre__lakeadmin_auth_rule_access` (
  `group_id` char(32) NOT NULL DEFAULT '0',
  `rule_id` char(32) NOT NULL DEFAULT '0',
  UNIQUE KEY `rule_id` (`rule_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='用户组与权限关联表';

DROP TABLE IF EXISTS `pre__lakeadmin_auth_rule_extend`;
CREATE TABLE `pre__lakeadmin_auth_rule_extend` (
  `id` char(32) NOT NULL DEFAULT '' COMMENT '扩展规则id',
  `module` varchar(250) NOT NULL DEFAULT '' COMMENT '规则所属module',
  `type` varchar(15) DEFAULT 'other' COMMENT '扩展规则标识',
  `group_id` char(32) NOT NULL DEFAULT '0',
  `rule` text NOT NULL COMMENT '扩展规则',
  `method` varchar(10) NOT NULL DEFAULT '' COMMENT '请求类型',
  `condition` varchar(250) DEFAULT '' COMMENT '规则附加条件',
  `rule_data` longtext COMMENT '规则数据，主要用来编辑数据保持',
  `fields` text COMMENT '扩展权限字段',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `add_ip` varchar(50) DEFAULT '' COMMENT '添加IP',
  PRIMARY KEY (`id`),
  KEY `module` (`module`),
  KEY `type` (`type`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='规则扩展表';

DROP TABLE IF EXISTS `pre__lakeadmin_config`;
CREATE TABLE `pre__lakeadmin_config` (
  `id` char(32) NOT NULL DEFAULT '' COMMENT '配置ID',
  `module` varchar(250) NOT NULL DEFAULT 'admin' COMMENT '模块',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '配置名称',
  `type` varchar(32) NOT NULL DEFAULT '' COMMENT '配置类型',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '配置标题',
  `group` varchar(32) NOT NULL DEFAULT '' COMMENT '配置分组',
  `options` text NOT NULL COMMENT '配置项',
  `remark` varchar(100) NOT NULL DEFAULT '' COMMENT '配置说明',
  `value` text COMMENT '配置值',
  `listorder` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `is_show` tinyint(1) DEFAULT '1' COMMENT '1-显示',
  `is_system` tinyint(1) DEFAULT '0' COMMENT '1-系统默认角色',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `add_ip` varchar(50) DEFAULT '' COMMENT '添加IP',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `type` (`type`),
  KEY `group` (`group`),
  KEY `module` (`module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='网站配置';

DROP TABLE IF EXISTS `pre__lakeadmin_field_type`;
CREATE TABLE `pre__lakeadmin_field_type` (
  `id` char(32) NOT NULL DEFAULT '',
  `name` varchar(32) NOT NULL COMMENT '字段类型',
  `title` varchar(64) NOT NULL DEFAULT '' COMMENT '中文类型名',
  `default_define` varchar(128) NOT NULL DEFAULT '' COMMENT '默认定义',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT '数据类型',
  `ifoption` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否需要设置选项',
  `ifstring` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否自由字符',
  `vrule` varchar(256) NOT NULL DEFAULT '' COMMENT '验证规则',
  `pattern` varchar(255) NOT NULL DEFAULT '' COMMENT '数据校验正则',
  `listorder` int(10) NOT NULL DEFAULT '100' COMMENT '排序',
  `is_system` tinyint(1) DEFAULT '0' COMMENT '1-系统',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `add_ip` varchar(50) DEFAULT '' COMMENT '添加IP',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='字段类型表';

DROP TABLE IF EXISTS `pre__lakeadmin_event`;
CREATE TABLE `pre__lakeadmin_event` (
  `id` char(32) NOT NULL DEFAULT '' COMMENT 'ID',
  `module` varchar(250) NOT NULL DEFAULT '' COMMENT '模块',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '事件点名称，为英文',
  `class` text NOT NULL COMMENT '事件类',
  `description` mediumtext COMMENT '事件点描述',
  `listorder` int(10) DEFAULT '100' COMMENT '排序',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `add_ip` varchar(50) DEFAULT '' COMMENT '添加IP',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='事件';

DROP TABLE IF EXISTS `pre__lakeadmin_module`;
CREATE TABLE `pre__lakeadmin_module` (
  `module` varchar(250) NOT NULL DEFAULT '' COMMENT '模块id',
  `name` varchar(250) NOT NULL DEFAULT '' COMMENT '模块名称',
  `introduce` mediumtext COMMENT '模块简介',
  `author` varchar(100) DEFAULT '' COMMENT '模块作者',
  `authorsite` varchar(255) DEFAULT '' COMMENT '作者地址',
  `authoremail` varchar(100) DEFAULT '' COMMENT '作者邮箱',
  `adaptation` varchar(50) DEFAULT '' COMMENT '适配最低版本',
  `version` varchar(50) NOT NULL DEFAULT '' COMMENT '版本',
  `path` text COMMENT '模块路径',
  `need_module` text COMMENT '依赖模块',
  `setting` mediumtext COMMENT '设置信息',
  `setting_data` longtext COMMENT '设置存储信息',
  `listorder` int(10) DEFAULT '100' COMMENT '排序',
  `installtime` int(10) DEFAULT '0' COMMENT '安装时间',
  `updatetime` int(10) DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态',
  `update_time` int(10) DEFAULT '0' COMMENT '更新时间',
  `update_ip` varchar(50) DEFAULT NULL COMMENT '更新IP',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `add_ip` varchar(50) DEFAULT '' COMMENT '添加IP',
  PRIMARY KEY (`module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='已安装模块列表';

INSERT INTO `pre__lakeadmin_admin` (`id`,`username`,`password`,`encrypt`,`nickname`,`email`,`avatar`,`is_system`,`status`,`last_login_time`,`last_login_ip`,`add_time`,`add_ip`) VALUES (1,'admin','c4069ce41921c07c6ac090550f9e34b0','txMUJF','管理员','lake-admin@qq.com','6faa1b979521721a1927ca75703141d2',1,1,1611722235,'127.0.0.1',1564667925,'2130706433'),(2,'lake','dbe97f21a69f67fb361b0be64988ee59','T3qf8f','Lake','lake@qq.com','b7daae912526add9c6929568ec929313',0,1,1577596275,'2130706433',1564415458,'2130706433');
INSERT INTO `pre__lakeadmin_attachment` VALUES ('6faa1b979521721a1927ca75703141d2','admin','admin',1,'Koala.jpg','images/20200226/f955a044e579fc624a9d97fd5ee0cdc3.jpg','','','image/jpeg','jpg',780831,'2b04df3ecc1d94afddff082d139c6f15','9c3dcb1f9185a314ea25d51aed3b5881b32f420c','public',100,1,1582706063,1582705793,0,''),('b7daae912526add9c6929568ec929313','admin','admin',1,'Penguins.jpg','images/20200226/9865b6472adb90e60b405a7818f61fd0.jpg','','','image/jpeg','jpg',777835,'9d377b10ce778c4938b3c7e2c63a229a','df7be9dc4f467187783aca68c7ce98e4df2172d0','public',100,1,1582706529,1582705839,0,'');
INSERT INTO `pre__lakeadmin_auth_group` VALUES ('26d9697f66e341d56af023423d8718b3','538a712299e0ba6011aaf63f2a1317f4','admin',1,'编辑','网站编辑，包括对文章的添加编辑等',100,'0','0',1,0,''),('538a712299e0ba6011aaf63f2a1317f4','0','admin',1,'超级管理员','拥有所有权限',95,'1','1',1,0,'');
INSERT INTO `pre__lakeadmin_auth_group_access` VALUES ('1','538a712299e0ba6011aaf63f2a1317f4'),('2','26d9697f66e341d56af023423d8718b3');
INSERT INTO `pre__lakeadmin_auth_rule` VALUES ('011fb80f96970904d07725d7587d0047','admin','ef07d2b56a46a1b656689093060ca242','菜单列表','admin/menu/index','',NULL,'','GET','','',1,5,0,1,1,1,0,''),('0bac582c5c40bef1411fda6fc7ff0f4c','admin','a75ff96e687046fb9f0a713a1b0dc279','事件','admin/event/index','',NULL,'','GET','icon-plugins-','',1,20,1,1,1,1,0,''),('0c744d1fbfa4155b89124a789bee65a3','admin','157155dd3aa0a5dd5d218b21cea203ea','控制面板','admin/index/main','',NULL,'','GET','','',1,15,0,1,1,1,0,''),('0f95a02350a57a0d7601c17830be880f','admin','e0bd1dabdf99bd59a4b0d044902a123b','更改密码','admin/profile/password','',NULL,'','POST','','',1,31,0,1,1,1,0,''),('11a692228748caa4d895db45d6c81580','admin','78019e8cac101349b627072816e112bf','系统设置','admin/config/setting','',NULL,'','GET','','',1,5,0,0,0,1,0,''),('11db4925d60d0744426847b66071e593','admin','637324c3794352e2ce554aa9869f365e','附件删除','admin/attachments/delete','',NULL,'','POST','','',1,10,0,1,1,1,0,''),('1312e1dcbd1860723805e9a332a4b884','admin','ef07d2b56a46a1b656689093060ca242','菜单状态','admin/menu/setstate','',NULL,'','POST','','',1,45,0,1,1,1,0,''),('1329fe0776cdfa8cc43b8fdf3c79ce26','admin','ef07d2b56a46a1b656689093060ca242','新增菜单','admin/menu/add','',NULL,'','POST','','',1,11,0,1,1,1,0,''),('13d503862b23386e01bb576800f7245f','admin','a7e2a306ff2204effe9f76620f452f39','角色','admin/Role/index','',NULL,'','GET','icon-group','',1,15,1,1,1,1,0,''),('1435662e0caace3a61967155b890eed5','admin','79a1c5e8d0109f00257b84472e13efd1','模块更新','admin/module/upgrade','',NULL,'','GET','','',1,20,0,1,1,1,0,''),('157155dd3aa0a5dd5d218b21cea203ea','admin','0','后台','','',NULL,'','','icon-homepage','',1,5,0,0,1,1,0,''),('175029224681e70ffa83d1a2b3afc47f','admin','157155dd3aa0a5dd5d218b21cea203ea','附件上传','admin/File/upload','',NULL,'','POST','','',1,55,0,1,1,1,0,''),('183844a1ba05e08872679737123c82e3','admin','79a1c5e8d0109f00257b84472e13efd1','模块详情','admin/module/view','',NULL,'','GET','','',1,35,0,1,1,1,0,''),('188318dc2d4ff26461c37a9008a20fc5','admin','e96fae7059ea5adf53a41a877254e3e4','配置管理','admin/config/index','',NULL,'','POST','','',1,6,0,1,0,1,0,''),('19209bcbb331c87b10d7134a72bf4be3','admin','0bac582c5c40bef1411fda6fc7ff0f4c','模块列表','admin/event/module','',NULL,'','GET','','',1,25,0,1,1,1,0,''),('19a934ad8b992ab2ec06b35f00a393a3','admin','5917d99fa745622a223a4a8b097d1484','新增权限','admin/RuleExtend/add','',NULL,'','GET','','',1,10,0,1,1,1,0,''),('1cba6872ffde00462f8a530074a55ee2','admin','5917d99fa745622a223a4a8b097d1484','新增权限','admin/RuleExtend/add','',NULL,'','POST','','',1,11,0,1,1,1,0,''),('1d13ee3588af24eed101b2ccccc05afd','admin','78019e8cac101349b627072816e112bf','系统配置','admin/config/setting','',NULL,'','POST','','',1,25,0,1,1,1,0,''),('22267f1859bb6d54f104075342166fde','admin','157155dd3aa0a5dd5d218b21cea203ea','缓存更新','admin/index/clear','',NULL,'','POST','','',1,20,0,1,1,1,0,''),('24d3c7e1f6b81c2ac19550ff83318ba8','admin','637324c3794352e2ce554aa9869f365e','附件列表','admin/attachments/index','',NULL,'','GET','','',1,5,0,1,0,1,0,''),('24ee1bcb5c59d23ef80dee1318ca038e','admin','ebff38dd2f5cdd54761f855ebdc9074a','更改密码','admin/manager/password','',NULL,'','POST','','',1,21,0,1,1,1,0,''),('2585f778538bfc82540effb40264d2d0','admin','ebff38dd2f5cdd54761f855ebdc9074a','管理员列表','admin/manager/index','',NULL,'','POST','','',1,6,0,1,0,1,0,''),('263bf887ecd49b9249b5fca89fe02050','admin','89b99469e42c4a0402a5ae7580cd4d2a','字典列表','admin/FieldType/index','',NULL,'','POST','','',1,6,0,0,0,1,0,''),('2d43155efc5d9d7f507802c18cfff96e','admin','5917d99fa745622a223a4a8b097d1484','编辑权限','admin/RuleExtend/edit','',NULL,'','POST','','',1,16,0,1,1,1,0,''),('30e18040ab03310a2d5de28ff1fe7969','admin','79a1c5e8d0109f00257b84472e13efd1','模块禁用','admin/module/disable','',NULL,'','POST','','',1,45,0,1,1,1,0,''),('318e94311dc98be170fb12c9c85b6ad4','admin','0','设置','','',NULL,'','','icon-setup','',1,10,1,1,1,1,0,''),('3761fc2344c50bc9174e40125a0c3976','admin','79a1c5e8d0109f00257b84472e13efd1','全部模块','admin/module/all','',NULL,'','GET','','',1,7,0,1,0,1,0,''),('37aa3b209596f6773651441276e2e92d','admin','ebff38dd2f5cdd54761f855ebdc9074a','添加管理员','admin/manager/add','',NULL,'','POST','','',1,8,0,1,1,1,0,''),('3a2f0b779ec8f29ea69672174e2b852a','admin','0bac582c5c40bef1411fda6fc7ff0f4c','排序','admin/event/listorder','',NULL,'','POST','','',1,20,0,1,1,1,0,''),('3b948a7a11431fb2de9089e957f62bba','admin','5917d99fa745622a223a4a8b097d1484','权限列表','admin/RuleExtend/index','',NULL,'','POST','','',1,6,0,1,0,1,0,''),('3e275652992372c676f74a25614c1f22','admin','79a1c5e8d0109f00257b84472e13efd1','模块卸载','admin/module/uninstall','',NULL,'','POST','','',1,16,0,1,1,1,0,''),('3f01faa0821768442702b280992a106d','admin','79a1c5e8d0109f00257b84472e13efd1','全部模块','admin/module/all','',NULL,'','POST','','',1,8,0,1,0,1,0,''),('407b20d09bc520e8db63309643bf2ac6','admin','637324c3794352e2ce554aa9869f365e','附件列表','admin/attachments/index','',NULL,'','POST','','',1,6,0,0,0,1,0,''),('414f55a603a8374e85fb19cc2b7b2735','admin','637324c3794352e2ce554aa9869f365e','附件详情','admin/attachments/view','',NULL,'','GET','','',1,8,0,1,1,1,0,''),('43793e6a879cbf2dc895c231ae53e6fe','admin','e96fae7059ea5adf53a41a877254e3e4','编辑配置','admin/config/edit','',NULL,'','POST','','',1,16,0,1,1,1,0,''),('47b837912a59370eced195809212cb47','admin','ef07d2b56a46a1b656689093060ca242','菜单排序','admin/menu/listorder','',NULL,'','POST','','',1,25,0,1,1,1,0,''),('4c63be13c57e8ce286b94a5bc20a172e','admin','0bac582c5c40bef1411fda6fc7ff0f4c','编辑','admin/event/edit','',NULL,'','POST','','',1,11,0,1,1,1,0,''),('4f2b9afaeadbee6b48ccc1b1a67a1b04','admin','feee47e7c0797ebd52f21e943f3c153c','日志详情','admin/adminLog/view','',NULL,'','GET','','',1,100,0,1,1,1,0,''),('50ed938a3f32d701e1144c3a5f59ca29','admin','ebff38dd2f5cdd54761f855ebdc9074a','管理员列表','admin/manager/index','',NULL,'','GET','','',1,5,0,1,0,1,0,''),('5383dec842c26100cd022a41a25ee0f7','admin','89b99469e42c4a0402a5ae7580cd4d2a','字段编辑','admin/FieldType/edit','',NULL,'','POST','','',1,11,0,1,1,1,0,''),('58b79db312db5ee3e93101b3acaa601c','admin','ef07d2b56a46a1b656689093060ca242','菜单列表','admin/menu/index','',NULL,'','POST','','',1,6,0,1,0,1,0,''),('5917d99fa745622a223a4a8b097d1484','admin','a7e2a306ff2204effe9f76620f452f39','扩展权限','admin/RuleExtend/index','',NULL,'','GET','icon-neirongguanli','',1,36,1,1,1,1,0,''),('5c6f47c49f18ce61e83206c3a1a53a64','admin','0bac582c5c40bef1411fda6fc7ff0f4c','删除','admin/event/del','',NULL,'','POST','','',1,15,0,1,1,1,0,''),('637324c3794352e2ce554aa9869f365e','admin','f99ce69498e9bb3e12d7f18f2b8d603a','附件管理','admin/attachments/index','',NULL,'','GET','icon-accessory','',1,100,1,1,1,1,0,''),('65855b776c8d1a1f3802624243f58db2','admin','79a1c5e8d0109f00257b84472e13efd1','模块安装','admin/module/install','',NULL,'','GET','','',1,10,0,1,1,1,0,''),('67f4bbc4b7611782d03016c9700298d6','admin','e96fae7059ea5adf53a41a877254e3e4','删除配置','admin/config/del','',NULL,'','POST','','',1,20,0,1,1,1,0,''),('68335126b4d2f63e7202c9774b2c15ad','admin','13d503862b23386e01bb576800f7245f','编辑角色','admin/Role/edit','',NULL,'','GET','','',1,20,0,1,1,1,0,''),('685acbb54a5d1c351f4194fab77438c0','admin','89b99469e42c4a0402a5ae7580cd4d2a','字段删除','admin/FieldType/del','',NULL,'','POST','','',1,15,0,1,1,1,0,''),('68f003a9f280ffe8bea9a2100a363723','admin','157155dd3aa0a5dd5d218b21cea203ea','账号信息','admin/profile/index','',NULL,'','GET','','',1,25,0,1,1,1,0,''),('6927499efce07e4939ec634cce0fa480','admin','7beb74362d2c7363d01c1f0134115585','解锁屏幕','admin/passport/unlockscreen','',NULL,'','POST','','',1,15,0,1,1,1,0,''),('71b1e75f181c57e61a06f8e81f7bdd00','admin','feee47e7c0797ebd52f21e943f3c153c','日志列表','admin/adminLog/index','',NULL,'','GET','','',1,5,0,1,0,1,0,''),('7367647003a277e22d589ec576c4163e','admin','89b99469e42c4a0402a5ae7580cd4d2a','字段添加','admin/FieldType/add','',NULL,'','POST','','',1,7,0,1,1,1,0,''),('78019e8cac101349b627072816e112bf','admin','f99ce69498e9bb3e12d7f18f2b8d603a','系统设置','admin/config/setting','',NULL,'','GET','icon-setup','',1,25,1,1,1,1,0,''),('79a1c5e8d0109f00257b84472e13efd1','admin','a75ff96e687046fb9f0a713a1b0dc279','模块管理','admin/module/index','',NULL,'','GET','icon-mokuaishezhi','',1,15,1,1,1,1,0,''),('7a360a4393162d6ffb44c3ee452acc8c','admin','13d503862b23386e01bb576800f7245f','角色列表','admin/Role/index','',NULL,'','GET','','',1,5,0,1,0,1,0,''),('7b6b858dd3db4bcb233e3f6ddc6f7254','admin','89b99469e42c4a0402a5ae7580cd4d2a','字段编辑','admin/FieldType/edit','',NULL,'','GET','','',1,10,0,1,1,1,0,''),('7beb74362d2c7363d01c1f0134115585','admin','157155dd3aa0a5dd5d218b21cea203ea','锁定屏幕','admin/passport/lockscreen','',NULL,'','POST','','',1,35,0,1,1,1,0,''),('7f127d8b8b5e06cf5a9c9fbad67a1721','admin','0bac582c5c40bef1411fda6fc7ff0f4c','嵌入点列表','admin/event/name','',NULL,'','GET','','',1,30,0,1,1,1,0,''),('8435eb4babd2dbc12d0299d8d8b9c5de','admin','5917d99fa745622a223a4a8b097d1484','规则数据','admin/RuleExtend/data','',NULL,'','GET','','',1,25,0,1,1,1,0,''),('87abee44293a676dc0ad98419c0a54f6','admin','79a1c5e8d0109f00257b84472e13efd1','模块启用','admin/module/enable','',NULL,'','POST','','',1,40,0,1,1,1,0,''),('8883788dbc3ef8323ac3e4923fccb188','admin','5917d99fa745622a223a4a8b097d1484','权限列表','admin/RuleExtend/index','',NULL,'','GET','','',1,5,0,1,1,1,0,''),('89a6df3c8f24c2e85bfb096bcb416ec4','admin','0','模块','admin/modules/index','',NULL,'','GET','icon-supply','',1,30,1,1,1,1,0,''),('89b99469e42c4a0402a5ae7580cd4d2a','admin','f99ce69498e9bb3e12d7f18f2b8d603a','字段类型','admin/FieldType/index','',NULL,'','GET','icon-bangzhushouce','',1,75,1,1,1,1,0,''),('89bdfb77700ac902108a2139ee610cd0','admin','79a1c5e8d0109f00257b84472e13efd1','模块更新','admin/module/upgrade','',NULL,'','POST','','',1,21,0,1,1,1,0,''),('8a3df2a1289fac9ecc656ef75e872e2e','admin','0bac582c5c40bef1411fda6fc7ff0f4c','事件列表','admin/event/index','',NULL,'','GET','','',1,5,0,1,0,1,0,''),('8b9844f730c66fa81c3b3f85fc28449c','admin','68f003a9f280ffe8bea9a2100a363723','账号信息','admin/profile/index','',NULL,'','GET','','',1,5,0,1,0,1,0,''),('8cbf1b62c961693f41ac6b15b3f05990','admin','89b99469e42c4a0402a5ae7580cd4d2a','排序','admin/FieldType/listorder','',NULL,'','POST','','',1,20,0,1,1,1,0,''),('9044b0f8048230a7c28d459fb791ad11','admin','89b99469e42c4a0402a5ae7580cd4d2a','字段添加','admin/FieldType/add','',NULL,'','GET','','',1,6,0,1,1,1,0,''),('92343380bc2b964c95b66ee7b69bf98f','admin','157155dd3aa0a5dd5d218b21cea203ea','图片本地化','admin/File/getUrlFile','',NULL,'','GET','','',1,60,0,1,1,1,0,''),('9364ab35a553feb99bfdde72fbdc229e','admin','13d503862b23386e01bb576800f7245f','删除角色','admin/Role/delete','',NULL,'','POST','','',1,30,0,1,1,1,0,''),('94e3956ff0b969d12bdb28cc239bf183','admin','0bac582c5c40bef1411fda6fc7ff0f4c','添加','admin/event/add','',NULL,'','POST','','',1,9,0,1,1,1,0,''),('997bfac935e680bba80121dbf1f6a8e0','admin','ebff38dd2f5cdd54761f855ebdc9074a','添加管理员','admin/manager/add','',NULL,'','GET','','',1,7,0,1,1,1,0,''),('9c91cd3d7d31311ae4e2c5aaa7fadb8a','admin','e96fae7059ea5adf53a41a877254e3e4','新增配置','admin/config/add','',NULL,'','GET','','',1,11,0,1,1,1,0,''),('9caf84fe6f59d57924c7f4a89cf6fa19','admin','ebff38dd2f5cdd54761f855ebdc9074a','管理员详情','admin/manager/view','',NULL,'','GET','','',1,15,0,1,1,1,0,''),('9fc4a10e76d57cb13ba74efde63e0b29','admin','79a1c5e8d0109f00257b84472e13efd1','模块设置','admin/module/config','',NULL,'','GET','','',1,25,0,1,1,1,0,''),('a75ff96e687046fb9f0a713a1b0dc279','admin','318e94311dc98be170fb12c9c85b6ad4','本地模块','','',NULL,'','','icon-supply','',1,30,1,1,1,1,0,''),('a7e2a306ff2204effe9f76620f452f39','admin','318e94311dc98be170fb12c9c85b6ad4','权限管理','','',NULL,'','','icon-guanliyuan','',1,20,1,1,1,1,0,''),('aa6a83c1cc29356c46f8a56dd9fa29df','admin','e96fae7059ea5adf53a41a877254e3e4','全部配置','admin/config/all','',NULL,'','POST','','',1,10,0,1,0,1,0,''),('b232de38130130dcaffb9aa8006c18e1','admin','ef07d2b56a46a1b656689093060ca242','编辑菜单','admin/menu/edit','',NULL,'','GET','','',1,15,0,1,1,1,0,''),('b32ea3e69e09b4069a0a1fac6b3a5a7c','admin','79a1c5e8d0109f00257b84472e13efd1','本地安装','admin/module/local','',NULL,'','POST','','',1,9,0,1,1,1,0,''),('b813293548f183a26280336e8bd67a9c','admin','e96fae7059ea5adf53a41a877254e3e4','编辑配置','admin/config/edit','',NULL,'','GET','','',1,15,0,1,1,1,0,''),('c0da67303446e516dab8ba6627e64430','admin','ef07d2b56a46a1b656689093060ca242','验证状态','admin/menu/setauth','',NULL,'','POST','','',1,30,0,1,1,1,0,''),('c0e834dc0bec7e69f15d1d967c577253','admin','79a1c5e8d0109f00257b84472e13efd1','模块列表','admin/module/index','',NULL,'','GET','','',1,5,0,1,0,1,0,''),('c5db8934a7efb788655f360380725cc8','admin','7beb74362d2c7363d01c1f0134115585','锁定屏幕','admin/passport/lockscreen','',NULL,'','POST','','',1,5,0,1,0,1,0,''),('c7742f8c6567af98670ba88810e97cc1','admin','ebff38dd2f5cdd54761f855ebdc9074a','编辑管理员','admin/manager/edit','',NULL,'','POST','','',1,11,0,1,1,1,0,''),('c943c6346550e302c72ca8c7332b05c4','admin','13d503862b23386e01bb576800f7245f','访问授权','admin/Role/access','',NULL,'','POST','','',1,8,0,1,1,1,0,''),('ca5257f40a038ef379a6ca4578e3559a','admin','feee47e7c0797ebd52f21e943f3c153c','删除日志','admin/adminLog/clear','',NULL,'','POST','','',1,50,0,1,1,1,0,''),('ca77deba4bdac3c3146ab38f2e3b5ffb','admin','79a1c5e8d0109f00257b84472e13efd1','模块设置','admin/module/config','',NULL,'','POST','','',1,26,0,1,1,1,0,''),('ce37c3ca21be4695556e360083852416','admin','157155dd3aa0a5dd5d218b21cea203ea','管理首页','admin/index/index','',NULL,'','GET','','',1,10,0,1,1,1,0,''),('d1dd4f64e1c34ec7f6daabb9a5763232','admin','ebff38dd2f5cdd54761f855ebdc9074a','更改密码','admin/manager/password','',NULL,'','GET','','',1,20,0,1,1,1,0,''),('d1f58d220f2dbb9daebf7a165de71c2c','admin','13d503862b23386e01bb576800f7245f','添加角色','admin/Role/create','',NULL,'','GET','','',1,10,0,1,1,1,0,''),('d1fc5dda9550ae957c22fc19ce1eaabe','admin','ef07d2b56a46a1b656689093060ca242','删除菜单','admin/menu/delete','',NULL,'','POST','','',1,20,0,1,1,1,0,''),('d209c1fcb180d277724a72e46a3f9062','admin','79a1c5e8d0109f00257b84472e13efd1','模块卸载','admin/module/uninstall','',NULL,'','GET','','',1,15,0,1,1,1,0,''),('d3bc0861baf269ee33ce7ddcf2d15828','admin','feee47e7c0797ebd52f21e943f3c153c','日志列表','admin/adminLog/index','',NULL,'','POST','','',1,6,0,1,0,1,0,''),('d86dfd1f85e0c3c692c32cdbb554b702','admin','13d503862b23386e01bb576800f7245f','角色列表','admin/Role/index','',NULL,'','POST','','',1,6,0,1,0,1,0,''),('d8ba22591e97ea52912e03167b94099d','admin','e96fae7059ea5adf53a41a877254e3e4','新增配置','admin/config/add','',NULL,'','POST','','',1,12,0,1,1,1,0,''),('d9ccd01e24c930351fc990e388308cb5','admin','89b99469e42c4a0402a5ae7580cd4d2a','字段列表','admin/FieldType/index','',NULL,'','GET','','',1,5,0,1,0,1,0,''),('db5e5f9e06c2ec94b9aafa9792c38243','admin','e96fae7059ea5adf53a41a877254e3e4','配置排序','admin/config/listorder','',NULL,'','POST','','',1,30,0,1,1,1,0,''),('dc3d98d8e1fa9ccb4ec692a80448aadd','admin','ef07d2b56a46a1b656689093060ca242','菜单显示','admin/menu/setmenu','',NULL,'','POST','','',1,35,0,1,1,1,0,''),('dcd4ec05b3667f3790c94612872675c0','admin','5917d99fa745622a223a4a8b097d1484','编辑权限','admin/RuleExtend/edit','',NULL,'','GET','','',1,15,0,1,1,1,0,''),('ddf35a1cd69cdd08e8420a2554299c49','admin','79a1c5e8d0109f00257b84472e13efd1','模块安装','admin/module/install','',NULL,'','POST','','',1,11,0,1,1,1,0,''),('e0a8efd45364f74c3424d6edd11394cb','admin','ebff38dd2f5cdd54761f855ebdc9074a','编辑管理员','admin/manager/edit','',NULL,'','GET','','',1,10,0,1,1,1,0,''),('e0bd1dabdf99bd59a4b0d044902a123b','admin','157155dd3aa0a5dd5d218b21cea203ea','更改密码','admin/profile/password','',NULL,'','GET','','',1,30,0,1,1,1,0,''),('e193c4a58ea4213da111fccc8ffba82b','admin','79a1c5e8d0109f00257b84472e13efd1','模块列表','admin/module/index','',NULL,'','POST','','',1,6,0,1,0,1,0,''),('e1fcff3c31ebe88a2e01957a9e1036f6','admin','0bac582c5c40bef1411fda6fc7ff0f4c','编辑','admin/event/edit','',NULL,'','GET','','',1,10,0,1,1,1,0,''),('e2e36cfb1d9ea660985b507c6efd6280','admin','ebff38dd2f5cdd54761f855ebdc9074a','删除管理员','admin/manager/del','',NULL,'','POST','','',1,25,0,1,1,1,0,''),('e3b770459528c8d303367c4a5a6b9dc5','admin','68f003a9f280ffe8bea9a2100a363723','账号信息','admin/profile/index','',NULL,'','POST','','',1,26,0,1,0,1,0,''),('e47583c1e884ee99996ef59a640781e5','admin','0bac582c5c40bef1411fda6fc7ff0f4c','事件列表','admin/event/index','',NULL,'','POST','','',1,6,0,1,0,1,0,''),('e956545f93a2fdb2ff43198923e9b4c8','admin','e0bd1dabdf99bd59a4b0d044902a123b','更改密码','admin/profile/password','',NULL,'','GET','','',1,5,0,1,0,1,0,''),('e96fae7059ea5adf53a41a877254e3e4','admin','f99ce69498e9bb3e12d7f18f2b8d603a','配置管理','admin/config/index','',NULL,'','GET','icon-apartment','',1,35,1,1,1,1,0,''),('ebff38dd2f5cdd54761f855ebdc9074a','admin','a7e2a306ff2204effe9f76620f452f39','管理员','admin/manager/index','',NULL,'','GET','icon-guanliyuan','',1,10,1,1,1,1,0,''),('ed4464f5690005a0379021a98d97ce4b','admin','0bac582c5c40bef1411fda6fc7ff0f4c','添加','admin/event/add','',NULL,'','GET','','',1,8,0,1,1,1,0,''),('edc679fef48d9a648d6fcc0fbc3d3d38','admin','13d503862b23386e01bb576800f7245f','访问授权','admin/Role/access','',NULL,'','GET','','',1,7,0,1,1,1,0,''),('eefa3e0c7e62a413b53467d41181f32d','admin','e96fae7059ea5adf53a41a877254e3e4','设置状态','admin/config/setstate','',NULL,'','POST','','',1,25,0,1,1,1,0,''),('ef07d2b56a46a1b656689093060ca242','admin','a7e2a306ff2204effe9f76620f452f39','权限菜单','admin/menu/index','',NULL,'','GET','icon-other','',1,35,1,1,1,1,0,''),('f14f17c382957cafaad1860640fa1430','admin','13d503862b23386e01bb576800f7245f','角色排序','admin/Role/listorder','',NULL,'','POST','','',1,35,0,1,1,1,0,''),('f2f5ac0096654088069d93379275b60b','admin','13d503862b23386e01bb576800f7245f','角色更新','admin/Role/update','',NULL,'','POST','','',1,25,0,1,1,1,0,''),('f5209acbd126ab13cdd5d3d9c670a708','admin','ef07d2b56a46a1b656689093060ca242','编辑菜单','admin/menu/edit','',NULL,'','POST','','',1,16,0,1,1,1,0,''),('f57659c04b2dfb3330dd019b3ceebd64','admin','ef07d2b56a46a1b656689093060ca242','新增菜单','admin/menu/add','',NULL,'','GET','','',1,10,0,1,1,1,0,''),('f7aad832deb37a453f4819ff3ec35800','admin','e96fae7059ea5adf53a41a877254e3e4','配置管理','admin/config/index','',NULL,'','GET','','',1,5,0,1,0,1,0,''),('f99ce69498e9bb3e12d7f18f2b8d603a','admin','318e94311dc98be170fb12c9c85b6ad4','系统配置','','',NULL,'','','icon-zidongxiufu','',1,10,1,1,1,1,0,''),('fb324b2f7eea95c6e7cfc42244c89eb4','admin','13d503862b23386e01bb576800f7245f','角色写入','admin/Role/write','',NULL,'','POST','','',1,15,0,1,1,1,0,''),('fc54bbd2255bba95db218f7517f9d030','admin','e96fae7059ea5adf53a41a877254e3e4','全部配置','admin/config/all','',NULL,'','GET','','',1,9,0,1,1,1,0,''),('fc7817f5226ab2fad3f0fc0ceb717fc9','admin','5917d99fa745622a223a4a8b097d1484','删除权限','admin/RuleExtend/del','',NULL,'','POST','','',1,20,0,1,1,1,0,''),('feee47e7c0797ebd52f21e943f3c153c','admin','a7e2a306ff2204effe9f76620f452f39','管理日志','admin/adminLog/index','',NULL,'','GET','icon-rizhi','',1,55,1,1,1,1,0,'');
INSERT INTO `pre__lakeadmin_auth_rule_access` VALUES ('26d9697f66e341d56af023423d8718b3','0c744d1fbfa4155b89124a789bee65a3'),('26d9697f66e341d56af023423d8718b3','0f95a02350a57a0d7601c17830be880f'),('26d9697f66e341d56af023423d8718b3','157155dd3aa0a5dd5d218b21cea203ea'),('26d9697f66e341d56af023423d8718b3','175029224681e70ffa83d1a2b3afc47f'),('26d9697f66e341d56af023423d8718b3','22267f1859bb6d54f104075342166fde'),('26d9697f66e341d56af023423d8718b3','68f003a9f280ffe8bea9a2100a363723'),('26d9697f66e341d56af023423d8718b3','6927499efce07e4939ec634cce0fa480'),('26d9697f66e341d56af023423d8718b3','7beb74362d2c7363d01c1f0134115585'),('26d9697f66e341d56af023423d8718b3','8b9844f730c66fa81c3b3f85fc28449c'),('26d9697f66e341d56af023423d8718b3','92343380bc2b964c95b66ee7b69bf98f'),('26d9697f66e341d56af023423d8718b3','c5db8934a7efb788655f360380725cc8'),('26d9697f66e341d56af023423d8718b3','ce37c3ca21be4695556e360083852416'),('26d9697f66e341d56af023423d8718b3','e0bd1dabdf99bd59a4b0d044902a123b'),('26d9697f66e341d56af023423d8718b3','e3b770459528c8d303367c4a5a6b9dc5'),('26d9697f66e341d56af023423d8718b3','e956545f93a2fdb2ff43198923e9b4c8');
INSERT INTO `pre__lakeadmin_config` VALUES ('1d7d470b81c6d7965ce6c2eab8b3c2de','admin','upload_thumb_water_alpha','text','水印透明度','upload','','请输入0~100之间的数字，数字越小，透明度越高','50',8,1,'1',1,1552436083,1552435299,0,''),('22cf857d6d5fb38a940ab8e3c1b77746','admin','admin_main','text','后台首页','system','','后台首页链接，默认lake-admin后台首页','',10,1,'1',1,1573398116,1571319251,0,''),('40e0305dbfb74d8eb75a048b2d2cde26','admin','upload_file_ext','text','允许上传的文件后缀','upload','','多个后缀用逗号隔开，不填写则不限制类型','doc,docx,xls,xlsx,ppt,pptx,pdf,wps,txt,rar,zip,gz,bz2,7z',4,1,'1',1,1552436080,1540457659,0,''),('5212d1baa0c8d105bfcde8dd74c93c17','admin','upload_thumb_water_pic','image','水印图片','upload','','只有开启水印功能才生效','7fa0976005ce96f4feb3e0a9511c04cc',6,1,'1',1,1552436081,1552435183,0,''),('87445ce1690defabff5426ab133927f6','admin','upload_driver','radio','上传驱动','upload','{\"public\":\"本地\"}','图片或文件上传驱动','public',9,1,'1',1,1552436085,1541752781,0,''),('90fa5b07881d16206c5c39a1b87e0d09','admin','upload_image_size','text','图片上传大小限制','upload','','0为不限制大小，单位：kb','0',2,1,'1',1,1552436075,1540457656,0,''),('9fe6fa5cbfdb51a866f9150b18bfd0aa','admin','upload_thumb_water_position','select','水印位置','upload','{\"1\":\"左上角\",\"2\":\"上居中\",\"3\":\"右上角\",\"4\":\"左居中\",\"5\":\"居中\",\"6\":\"右居中\",\"7\":\"左下角\",\"8\":\"下居中\",\"9\":\"右下角\"}','只有开启水印功能才生效','9',7,1,'1',1,1552436082,1552435257,0,''),('aa033250e51c3dc21eeb22f506c2d859','admin','admin_allow_ip','textarea','后台允许访问IP','system','','匹配IP段用\"*\"占位，如192.168.*.*，多个IP地址请用英文逗号\",\"分割','',15,1,'1',1,1571319287,1551244957,0,''),('c637bbc27673a8c253807cc19984550f','admin','config_group','array','配置分组','system','{}','字段请不要使用数字','{\"system\":\"系统\",\"upload\":\"上传\"}',5,0,'1',1,1577020289,1494408414,0,''),('da237e7fe5b27b745f00ece22ed2a002','admin','upload_image_ext','text','允许上传的图片后缀','upload','','多个后缀用逗号隔开，不填写则不限制类型','gif,jpg,jpeg,bmp,png',1,1,'1',1,1552436074,1540457657,0,''),('e4de02664583b150c657f33cd484bfb1','admin','upload_thumb_water','switch','添加水印','upload','','','0',5,1,'1',1,1552436080,1552435063,0,''),('e74a14f7239321408b2821de42e4a4cd','admin','upload_file_size','text','文件上传大小限制','upload','','0为不限制大小，单位：kb','0',3,1,'1',1,1552436078,1540457658,0,'');
INSERT INTO `pre__lakeadmin_field_type` (`id`,`name`,`title`,`default_define`,`type`,`ifoption`,`ifstring`,`vrule`,`pattern`,`listorder`,`is_system`,`add_time`,`add_ip`) VALUES ('01bc01a46f357802ee07c5cde2435417','color','颜色值','varchar(7) NOT NULL DEFAULT \'\'','varchar',0,0,'','',11,1,0,''),('1974c40d6f0efa6527caa60e52616459','array','数组','varchar(512) NOT NULL DEFAULT \'\'','varchar',0,0,'','',8,1,0,''),('3ec65ea1388488285a1ae8d14bfa1f13','textarea','多行文本','varchar(255) NOT NULL DEFAULT \'\'','varchar',0,1,'','',3,1,0,''),('41181ffb6e74ee819beee38f4e71485b','images','多张图','text NOT NULL DEFAULT \'\'','text',0,0,'','',10,1,0,''),('5ac82e38b16f6e7e069ab1fc533aa17d','files','多文件','text NOT NULL DEFAULT \'\'','text',0,0,'','',16,1,0,''),('654eb35a35f1f59c643014445a58ce5b','date','日期','int(10) UNSIGNED NOT NULL DEFAULT \'0\'','int',0,0,'','',12,1,0,''),('654eb35a35f1f59c643014445a58ceab','datetime','日期和时间','int(10) UNSIGNED NOT NULL DEFAULT \'0\'','int',0,0,'','',13,1,0,''),('65dd0149c4ce8e3bf34827887105e55c','number','数字','int(10) UNSIGNED NOT NULL DEFAULT \'0\'','int',0,0,'','',1,1,0,''),('6b621a89607db17a6b9de06adb84d268','file','单文件','varchar(32) NOT NULL DEFAULT \'\'','varchar',0,0,'','',15,1,0,''),('73adee58ccf43bb0e282788020f20fd3','checkbox','复选按钮','varchar(32) NOT NULL DEFAULT \'\'','varchar',1,0,'','',5,1,0,''),('77e063b76c390acb2d0e37ce01cf03b5','tags','标签','varchar(255) NOT NULL DEFAULT \'\'','varchar',0,1,'','',25,1,0,''),('90e42565f4e0160f4781c66fc71e82b6','select','下拉框','varchar(10) NOT NULL DEFAULT \'\'','varchar',1,0,'','',7,1,0,''),('9b7019fb76cf156fbac866adbbebedf5','hidden','隐藏值','varchar(255) NOT NULL DEFAULT \'\'','varchar',0,1,'','',17,1,0,''),('a22f3b5775bae45ea7de4ad8625b4c60','switch','开关','tinyint(2) UNSIGNED NOT NULL DEFAULT \'0\'','tinyint',0,0,'isBool','',6,1,0,''),('a6bbd4b9ebc6ac91bc401375afa24fbb','radio','单选按钮','varchar(10) NOT NULL DEFAULT \'\'','varchar',1,0,'','',4,1,0,''),('c4a5fa2f40ce76d55d95d5fdb33e584b','Ueditor','百度编辑器','text NOT NULL','text',0,1,'','',14,1,0,''),('ce83c73575d00d9fdee9b6b3f812c2a5','image','单张图','varchar(32) NOT NULL DEFAULT \'\'','varchar',0,0,'','',9,1,0,''),('f437d6c1e474c0f078aff4bee1c3f6fe','password','密码','varchar(255) NOT NULL DEFAULT \'\'','varchar',0,1,'','',20,1,0,''),('f437d6c1e474c0f078aff4bee1c3fbfe','text','输入框','varchar(255) NOT NULL DEFAULT \'\'','varchar',0,1,'','',2,1,0,'');
