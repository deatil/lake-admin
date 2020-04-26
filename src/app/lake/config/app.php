<?php

// +----------------------------------------------------------------------
// | 系统设置
// +----------------------------------------------------------------------

return [

    // app命名空间
    'module_namespace' => 'app',

    // 模块地址
    'module_path' => root_path() . 'addons' . DIRECTORY_SEPARATOR,
    // 系统模块
    'system_module_list' => [
        'index', 
        'admin', 
        'api',
    ],
    
    // 公开路径
    'public_url' => '/',
    // 文件上传文件路径
    'upload_url' => '',
    // 资源文件路径
    'static_url' => '/static/',
    // 文件上传文件路径
    'upload_path' => root_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'storage',
    // 资源文件路径
    'static_path' => root_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'static',
    // 模块资源文件路径
    'module_static_path' => root_path() . 'public' . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR,

];
