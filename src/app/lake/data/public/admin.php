<?php

/**
 * [ 后台入口文件 ]
 * 
 * 使用此文件可以达到隐藏admin模块的效果
 * 建议将admin.php改成其它任意的文件名，
 * 同时修改config/app.php中的'deny_module_list',
 * 把admin模块也添加进去
 *
 * @create 2019-7-31
 * @author deatil
 */

namespace think;

if (version_compare(PHP_VERSION, '5.6.0', '<')) {
    header("Content-type: text/html; charset=utf-8");
    die('PHP 5.6.0 及以上版本系统才可运行~ ');
}

// 加载基础文件
require '..' . DIRECTORY_SEPARATOR . 'thinkphp' . DIRECTORY_SEPARATOR . 'base.php';

// 执行应用并响应
Container::get('app')->bind('admin')->run()->send();
