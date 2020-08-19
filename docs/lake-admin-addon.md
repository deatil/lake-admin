## `lake-admin` `模块插件` 目录说明

> `addon目录` 下模块插件必须有的目录结构

~~~
-module_name
--admin 模块后台目录[不需后台可删去]
---controller 模块后台控制器目录
---view 模块后台视图目录

--api 模块api目录[没有API可删去]
---controller 模块api控制器目录

--config 模块配置文件目录
--controller 模块控制器目录
--global 模块全局配置及文件引入目录[非必须]

--icon.png 模块logo文件
--info.php 模块说明文件
--Install.php 模块安装文件[非必须]
--Uninstall.php 模块卸载文件[非必须]
--Upgrade.php 模块更新文件[非必须]
~~~

> `自定义模块插件` 下模块插件必须有的目录结构

# 需要在composer.json引入插件的Service，并在插件Service注入模块信息到lake-admin系统
# 下面以 `lake-admin-addon-lmenu` 为例子说明。
# 更多具体的信息可以直接查看该模块插件项目

模块插件 `composer.json` 信息
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
            "app\\lmenu\\": "src/addons/lmenu"
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

模块插件 `Service` 信息
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