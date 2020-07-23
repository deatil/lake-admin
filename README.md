## lake-admin后台管理系统


### 项目介绍

*  `lake-admin` 基于ThinkPHP6.0最新版，后台页面基于layui搭建
*  本项目开发理念为“提供更为精简完善的后台管理系统”，友好的模型开发方案，更接近Thinkphp相关APP开发，为更好更快的开发项目提供更大的帮助
*  更新ThinkPHP版本到6.0.3版本


### 开源协议

*  `lake-admin` 遵循 `Apache2` 开源协议发布，在保留本系统版权（包括版权文件及系统相关标识，相关的标识需在明显位置标示出来）的情况下提供个人及商业免费使用。  
*  使用该项目时，请在明显的位置保留该系统的标识（标识包括：lake，lake-admin及该系统所属logo），为了防止不必要的麻烦，请遵守该协议要求。


### 版权

*  该系统所属版权归deatil(deatil#github.com)所有。


### 安装步骤

*  安装 `thinkphp6.0` 框架
*  配置数据库的连接信息
*  `composer require lake/lake-admin` 导入lake-admin后台管理系统
*  cmd里执行 `php think lake-admin:install` 及 `php think lake-admin:service-discover` 初始化 `lake-admin` 管理系统
*  设置网站执行目录为：`public`
*  后台admin登陆，超级管理员账号及密码：`admin/123456`
*  模块插件目录：`addons` 文件夹 及 自定义包模块插件
*  自定义包模块插件可以查看：`lake-admin-addon-lmenu` 插件 及 `lake-admin-addon-lroute` 插件
*  如果项目搬迁移，可以执行 `php think lake-admin:repair` 修复静态文件失效问题，已安装模块请升级更新一次
