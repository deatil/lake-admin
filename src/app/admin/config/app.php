<?php
return [

    // +----------------------------------------------------------------------
    // | 系统设置
    // +----------------------------------------------------------------------    
    
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl' => env('lake_admin_module_path') . 'tpl' . DIRECTORY_SEPARATOR . 'dispatch_jump.tpl',
    'dispatch_error_tmpl' => env('lake_admin_module_path') . 'tpl' . DIRECTORY_SEPARATOR . 'dispatch_jump.tpl',

    // 异常页面的模板文件
    'exception_tmpl'   => env('lake_admin_module_path') . 'tpl/exception.tpl',
    
    // 管理员盐
    'admin_salt' => env('admin_salt', 'd,d7ja0db1a974;38cE84976abbac2cd'),
    
];