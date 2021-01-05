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
* 强大的模块插件扩展功能，安装卸载升级插件随便组合
* 自带附件管理及操作日志系统，方便系统维护管理
* 通用的用户管理和API模块插件，轻易扩展你的系统
* 强大的模块插件Admin开发和API开发
* 系统CMS模块插件，为了节约CMS开发难题
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

![LakeAdmin-Index](https://user-images.githubusercontent.com/24578855/103670051-116d1080-4fb4-11eb-8dd9-b8767b35c459.png)

![LakeAdmin-Attachment](https://user-images.githubusercontent.com/24578855/103670074-1631c480-4fb4-11eb-88d0-754e6e085f60.png)

![LakeAdmin-Module](https://user-images.githubusercontent.com/24578855/103670080-17fb8800-4fb4-11eb-84f2-1021a2293a18.png)

查看更多截图 ![LakeAdmin](https://github.com/deatil/lake-admin/issues)


### 扩展推荐

| 名称 | 描述 |
| --- | --- |
| [CMS内容管理](https://github.com/deatil/lake-cms) | 强大的分类管理，完整的模版开发标签系统，配套的友情链接模块和自定义表单模块，让你的cms简单但高效 |
| [用户管理](https://github.com/deatil/lake-admin-addon-luser) | 通用的用户管理模块，实现了用户登陆api的token及jwt双认证 |
| [API接口](https://github.com/deatil/lake-admin-addon-lapi) | 强大的API接口管理系统，支持多种签名算法验证，支持签名字段多个位置存放 |
| [路由美化](https://github.com/deatil/lake-admin-addon-lroute) | 支持thinkphp自带的多种路由美化设置，自定义你的系统url |
| [菜单结构](https://github.com/deatil/lake-admin-addon-lmenu) | 提取后台菜单分级结构格式，为你的模块开发保驾护航 |
| [数据库管理](https://github.com/deatil/lake-admin-addon-database) | 数据库备份、优化、修复及还原，你的系统维护帮手 |

注：模块目录默认为 `/addon` 目录


## 问题反馈

在使用中有任何问题，请使用以下联系方式联系我们

Github: https://github.com/deatil/lake-admin


### 开源协议

*  `lake-admin` 遵循 `Apache2` 开源协议发布，在保留本系统版权的情况下提供个人及商业免费使用。  


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

