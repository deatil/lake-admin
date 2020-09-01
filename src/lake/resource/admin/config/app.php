<?php
return [

    // +----------------------------------------------------------------------
    // | 系统设置
    // +----------------------------------------------------------------------    

    // 异常页面的模板文件
    'exception_tmpl'   => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR . 'exception.tpl',
    
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR . 'dispatch_jump.tpl',
    'dispatch_error_tmpl' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR . 'dispatch_jump.tpl',
    
    // 管理员盐
    'admin_salt' => env('lake_admin.admin_salt', 'd,d7ja0db1a974;38cE84976abbac2cd'),
];