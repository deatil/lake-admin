<?php

/**
 * [ 后台入口文件 ]
 * 
 * 使用此文件可以达到隐藏admin模块的效果
 * 建议将admin.php改成其它任意的文件名，
 * 同时修改config/app.php中的'deny_module_list',
 * 把admin模块也添加进去
 *
 * @create 2020-4-12
 * @author deatil
 */

namespace think;

if (version_compare(PHP_VERSION, '7.1.0', '<')) {
    header("Content-type: text/html; charset=utf-8");
    die('PHP 5.6.0 及以上版本系统才可运行~ ');
}

// [ 应用入口文件 ]
namespace think;

require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->name('admin')->run();

$response->send();

$http->end($response);
