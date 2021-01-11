lake-admin是一款基于ThinkPHP6+Layui的后台开发框架。


### 项目介绍

*  `lake-admin` 基于 `ThinkPHP` 框架，后台页面基于 `Layui` 搭建
*  更新 `ThinkPHP` 版本到 `v6.0.5`
*  更新 `Layui` 版本到 `v2.5.7`
*  模块插件文档请查看 `docs/lake-admin-addon.md` 文件


## 主要特性

* 基于 `RABC` 验证的权限管理系统
    * 支持父级的管理员可任意增删改子级管理员及权限设置
    * 支持单管理员多角色
    * 支持管理子级数据或个人数据
* 完善的前端功能组件开发
    * 基于`Layui`开发，自适应手机、平板、PC
    * 基于`Layui`的模块加载机制进行JS模块管理，按需加载
    * 系统界面以模块扩展方式独立于`Layui`，方便升级更新`Layui`
    * 后台界面多主题选择，让你的使用不再单调
* 强大的模块插件扩展功能，安装卸载升级插件随便组合
* 自带附件管理及操作日志系统，方便系统维护管理
* 通用的用户管理和API模块插件，轻易扩展你的系统
* 强大的模块插件自定义Admin开发和API开发
* 简易的模块插件开发流程，为你的开发节省时间提高效率


### 安装使用

*  安装 `thinkphp` `v6.*` 版本框架
*  配置数据库的连接信息
*  `composer require lake/lake-admin` 导入lake-admin后台管理系统
*  执行 `php think lake-admin:install` 及 `php think lake-admin:service-discover` 初始化 `lake-admin` 系统
*  设置网站执行目录为：`public`
*  后台admin登陆，超级管理员账号及密码：`admin/123456`
*  部分自定义配置，需要将根目录 `.env.lake` 文件里内容复制到 `.env` 内
*  模块插件目录：`addon` 文件夹 及 自定义包模块插件
*  自定义包模块插件可以查看：`lake-admin-addon-lmenu` 插件 及 `lake-admin-addon-lroute` 插件
*  如果项目迁移，可以执行 `php think lake-admin:repair` 修复系统静态文件失效问题，已安装模块请根据模块相关文档更新模块静态文件链接


### 界面截图

![LakeAdmin](https://user-images.githubusercontent.com/24578855/103784065-7ab45880-5074-11eb-9f16-a4fd869223ff.png)

![LakeAdmin7](https://user-images.githubusercontent.com/24578855/103784137-8d2e9200-5074-11eb-88f7-3372c9919acf.png)

![LakeAdmin8](https://user-images.githubusercontent.com/24578855/104213975-df572500-5471-11eb-9dd7-acde3de4ba86.png)

查看更多截图 [LakeAdmin](https://github.com/deatil/lake-admin/issues)


### 模块推荐

| 名称 | 描述 |
| --- | --- |
| [cms系统](https://github.com/deatil/lake-admin-cms) | 简单高效实用的内容管理系统 |
| [用户管理](https://github.com/deatil/lake-admin-addon-luser) | 通用的用户管理模块，实现了用户登陆api的token及jwt双认证 |
| [API接口](https://github.com/deatil/lake-admin-addon-lapi) | 强大的API接口管理系统，支持多种签名算法验证，支持签名字段多个位置存放 |
| [路由美化](https://github.com/deatil/lake-admin-addon-lroute) | 支持thinkphp自带的多种路由美化设置，自定义你的系统url |
| [菜单结构](https://github.com/deatil/lake-admin-addon-lmenu) | 提取后台菜单分级结构格式，为你的模块开发保驾护航 |
| [数据库管理](https://github.com/deatil/lake-admin-addon-database) | 数据库备份、优化、修复及还原，你的系统维护帮手 |

注：模块目录默认为 `/addon` 目录


## 问题反馈

在使用中有任何问题，请使用以下联系方式联系我们

Github: https://github.com/deatil/lake-admin


## 特别鸣谢

感谢以下的项目,排名不分先后

ThinkPHP：http://www.thinkphp.cn

Layui: https://www.layui.com

jQuery：http://jquery.com


## 版权信息

lake-admin 遵循Apache2开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有 Copyright © deatil(https://github.com/deatil)

All rights reserved。

