## lake-admin 系统 root_path 目录说明

> 目录结构

~~~
www  WEB部署目录
├─addon                 lake-admin系统模块插件目录
│  ├─module_name        模块插件目录
│  │  ├─controller      控制器目录
│  │  ├─model           模型目录
│  │  ├─view            视图目录
│  │  ├─common.php      模块函数文件
│  │  └─ ...            其他
│  │
│  └─ ...               其他模块插件目录
│
├─app                   应用目录 
│  ├─module_name        模块目录
│  │  ├─controller      控制器目录
│  │  ├─model           模型目录
│  │  ├─view            视图目录
│  │  ├─common.php      模块函数文件
│  │  └─ ...            其他
│  │
│  └─ ...               其他模块目录
│
├─config                应用配置目录
│  ├─app.php            应用配置
│  ├─cache.php          缓存配置
│  ├─cookie.php         Cookie配置
│  ├─database.php       数据库配置
│  ├─log.php            日志配置
│  ├─session.php        Session配置
│  ├─view.php           模板引擎配置
│  └─trace.php          Trace配置
│
├─route                 路由定义目录
│  ├─app.php            路由定义
│  └─...                更多
│
├─public                WEB目录（对外访问目录）
│  ├─index.php          入口文件
│  ├─static             静态资源文件目录
│  ├─router.php         快速测试文件
│  └─.htaccess          用于apache的重写
│
├─extend                扩展类库目录
├─runtime               应用的运行时目录（可写，可定制）
├─vendor                第三方类库目录（Composer依赖库）
├─composer.json         composer 定义文件
├─LICENSE.txt           授权说明文件
├─README.md             README 文件
├─think                 命令行入口文件
└─ ...                  其他自定义文件和目录
~~~

> 注意事项

*  addon目录如果没有，需要手动创建，非自定义模块插件包需要放到该目录