<?php

// 应用行为扩展定义文件
return [
    // 应用开始
    'app_begin' => [
		// 权限检测
		'\\app\\admin\\behavior\\AdminAuthCheck',
	],
    // 模块初始化
    'module_init' => [],
    // 操作开始执行
    'action_begin' => [],
    // 视图内容过滤
    'view_filter' => [],
    // 日志写入
    'log_write' => [],
    // 应用结束
    'app_end' => [
		// 操作记录
		'\\app\\admin\\behavior\\AdminLog',
	],
	
    // 自定义后台首页
    'lake_admin_main_url' => [
		// 自定义后台首页
		'\\app\\admin\\behavior\\AdminMainUrl',
	],
];
