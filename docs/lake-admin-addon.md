## `lake-admin` `模块插件` 目录说明

> `addon目录` 下模块插件必须有的目录结构

~~~
┌-- module_name
├---- admin 模块后台目录[不需后台可删去]
├------- controller 模块后台控制器目录
├------- view 模块后台视图目录
├---- api 模块api目录[没有API可删去]
├------- controller 模块api控制器目录
├---- config 模块配置文件目录
├---- controller 模块控制器目录
├---- global 模块全局配置及文件引入目录[非必须]
├---- icon.png 模块logo文件
├---- info.php 模块说明文件
├---- Install.php 模块安装文件[非必须]
├---- Uninstall.php 模块卸载文件[非必须]
├---- Upgrade.php 模块更新文件[非必须]
~~~

> `自定义模块插件` 下模块插件必须有的目录结构

*  需要在composer.json引入插件的Service，并在插件Service注入模块信息到lake-admin系统
*  下面以 `lake-admin-addon-lmenu` 为例子说明。
*  更多具体的信息可以直接查看该模块插件项目

> 模块插件 `composer.json` 信息
~~~
{
    "name": "lake/lake-admin-addon-lmenu",
    "description": "The Lake-admin-addon-lmenu is an addon package for lake-admin.",
    "license": "Apache-2.0",
    "type": "library",
    "keywords": [
        "lake",
        "admin",
        "lake-admin",
        "addon",
        "lmenu"
    ],
    "homepage": "https://github.com/deatil",
    "authors": [
        {
            "name": "deatil",
            "email": "deatil@github.com",
            "homepage": "https://github.com/deatil"
        }
    ],
    "require": {
        "php": ">=7.1.0"
    },
    "autoload": {
        "psr-4": {
            "app\\lmenu\\": "src/addon/lmenu"
        },
        "files": [
        ]
    },
    "extra": {
        "think":{
            "services":[
                "app\\lmenu\\boot\\Service" # 该行引入模块插件Service
            ]
        }
    }
}
~~~

> 模块插件 `Service` 信息
~~~
<?php

namespace app\lmenu\boot;

use think\Service as BaseService;

class Service extends BaseService
{
    public function boot()
    {
        // 该事件将模块信息注入到系统，以公系统获取信息及调用，请填写正确
        $this->app->event->listen('lake_admin_module', function () {
            $info_file = dirname(__DIR__)
                . DIRECTORY_SEPARATOR . 'info.php';
            if (file_exists($info_file)) {
                $info = include $info_file;
            } else {
                $info = [];
            }
            
            return $info;
        });
    }
    
}
~~~

> 模块插件 `info.php` 信息，以`lcms`模块为例
~~~
<?php

return [
    // 模块ID[必填]
    'module' => 'lcms',
    // 模块名称[必填]
    'name' => 'lcms内容管理',
    // 模块简介[选填]
    'introduce' => 'lcms内容管理系统，基于lake-admin开发',
    // 模块作者[必填]
    'author' => 'deatil',
    // 作者地址[选填]
    'authorsite' => 'http://github.com/deatil',
    // 作者邮箱[选填]
    'authoremail' => 'deatil@github.com',
    // 版本号，请不要带除数字外的其他字符[必填]
    'version' => '2.0.2',
    // 适配最低 lake-admin 系统版本[必填]
    'adaptation' => '2.0.2',
    // 签名[必填]
    'sign' => '6935ade1070a6ce945db58129347758b',
    
    // 模块地址，自定义模块包时填写
    'path' => '', // 自定义模块包地址通常为 __DIR__
    
    // 依赖模块
    'need_module' => [
        ['lform', '2.0.2', '>=']
    ],
    
    // 设置
    'setting' => [
        'type' => [
            'title' => '内容类型',
            'type' => 'select',
            'options' => [
                '1' => '普通',
                '4' => '一般',
                '9' => '最高',
            ],
            'value' => '9',
            'tips' => '设置文章内容类型',
        ],
    ],
    
    // 事件
    'event' => [
        'InitLcmsRoute' => [
            'name' => 'HttpRun',
            'class' => 'app\\lcms\\behavior\\InitLcmsRoute',
            'description' => 'Lcms路由设置',
            'listorder' => 100,
            'status' => 1,
        ],
    ],
    
    // 菜单，菜单数组建议单独引入
    'menus' => include __DIR__ . '/menu.php',
    
    // 数据表，不用加表前缀
    'tables' => [
        'lcms_category',
    ],
    
    // 安装演示数据
    'demo' => 1,
];
~~~

> 模块插件 `Install.php` 信息
~~~
class Install
{
    /**
     * 安装前回调
     * @return boolean
     */
    public function run()
    {
        return true;
    }

    /**
     * 安装完回调
     * @return boolean
     */
    public function end()
    {
        return true;
    }

}
~~~

> 模块插件 `Uninstall.php` 信息
~~~
class Uninstall
{
    /**
     * 卸载前回调
     * @return boolean
     */
    public function run()
    {
        return true;
    }

    /**
     * 卸载完回调
     * @return boolean
     */
    public function end()
    {
        return true;
    }

}
~~~

> 模块插件 `Upgrade.php` 信息
~~~
class Upgrade
{
    /**
     * 更新前回调
     * @return boolean
     */
    public function run()
    {
        return true;
    }

    /**
     * 更新完回调
     * @return boolean
     */
    public function end()
    {
        return true;
    }

}
~~~
