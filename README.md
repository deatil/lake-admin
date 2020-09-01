## lake-admin后台管理系统


### 项目介绍

*  `lake-admin` 基于 `ThinkPHP` 框架，后台页面基于 `layui` 搭建
*  更新 `ThinkPHP` 版本到 v6.0.3 版本
*  更新 `layui` 版本到 v2.5.6 版本
*  模块插件文档请查看 `docs/lake-admin-addon.md` 文件


### 安装步骤

*  安装 `thinkphp6.0` 框架
*  配置数据库的连接信息
*  `composer require lake/lake-admin` 导入lake-admin后台管理系统
*  cmd里执行 `php think lake-admin:install` 及 `php think lake-admin:service-discover` 初始化 `lake-admin` 管理系统
*  设置网站执行目录为：`public`
*  后台admin登陆，超级管理员账号及密码：`admin/123456`
*  模块插件目录：`addon` 文件夹 及 自定义包模块插件
*  自定义包模块插件可以查看：`lake-admin-addon-lmenu` 插件 及 `lake-admin-addon-lroute` 插件
*  如果项目迁移，可以执行 `php think lake-admin:repair` 修复静态文件失效问题，已安装模块请根据模块相关文档更新静态文件链接
*  部分自定义配置，需要将根目录 `.env.lake` 文件里内容复制到 `.env` 内


### 截图预览

![LakeAdmin1](https://user-images.githubusercontent.com/24578855/91877097-23f6d680-ecb0-11ea-99e1-c8e7b36421dd.png)
![LakeAdmin2](https://user-images.githubusercontent.com/24578855/91877220-4ee12a80-ecb0-11ea-9f0f-e3879d4c07ac.png)
![LakeAdmin3](https://user-images.githubusercontent.com/24578855/91877251-57396580-ecb0-11ea-8503-be6335c8a92d.png)
![LakeAdmin0](https://user-images.githubusercontent.com/24578855/91877310-691b0880-ecb0-11ea-9692-06d0beb3c048.png)

### 开源协议

*  `lake-admin` 遵循 `Apache2` 开源协议发布，在保留本系统版权（包括版权文件及系统相关标识，相关的标识需在明显位置标示出来）的情况下提供个人及商业免费使用。  
*  使用该项目时，请在明显的位置保留该系统的版权标识（标识包括：lake，lake-admin及该系统所属logo），并不得修改后台版权信息。


### 版权

*  该系统所属版权归 deatil(https://github.com/deatil) 所有。
