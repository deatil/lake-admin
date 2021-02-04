<?php

// +----------------------------------------------------------------------
// | 系统设置
// +----------------------------------------------------------------------

return [
    
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR . 'dispatch_jump.tpl',
    'dispatch_error_tmpl' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR . 'dispatch_jump.tpl',

    // 根目录
    'root_path' => root_path(),

    // app命名空间
    'module_namespace' => 'app',

    // 模块地址
    'module_path' => root_path() . 'addon' . DIRECTORY_SEPARATOR,
    
    // 系统模块
    'system_module_list' => [
        'admin', 
        'api',
        'index', 
    ],
    
    // 上传磁盘
    'upload_disk' => env('lake_admin.upload_disk', 'public'),
   
    // 资源文件路径
    'static_url' => '/static/',
    // 模块资源文件路径
    'module_static_path' => root_path() . 'public' . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR,
    
    // 超级管理员ID
    'admin_super_id' => env('lake_admin.admin_super_id', 1),
    
    // 如果启用了自动加载语言包，需要开启该配置
    'load_lang_pack' => env('lake_admin.load_lang_pack', 0),
];
