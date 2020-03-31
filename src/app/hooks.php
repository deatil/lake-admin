<?php

/**
 * lake-admin 后台hooks配置文件
 *
 * @create 2019-10-5
 * @author deatil
 */

use think\Db;
use think\Hook;
use think\Loader;
use think\Console;
use think\facade\Hook as FacadeHook;
use think\facade\View as FacadeView;
use think\facade\Env as FacadeEnv;

// 监听后台开始
FacadeHook::listen('lake_admin_hook_begin');

// 注册配置行为
FacadeHook::add('app_init', "app\\admin\\behavior\\InitConfig");    
// 注册钩子
FacadeHook::add('app_init', "app\\admin\\behavior\\InitHook");    
// 模块检测
FacadeHook::add('app_begin', "app\\admin\\behavior\\CheckModule");    

// 设置错误跳转页面目录    
FacadeEnv::set([
    'lake_admin_app_path' => rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR,
]);

// 设置模块基础别名
Loader::addClassAlias([
    'lake\\Module' => 'app\\admin\\module\\Module',
    'lake\\module\\controller\\AdminBase' => 'app\\admin\\module\\controller\\AdminBase' ,
    'lake\\module\\controller\\Homebase' => 'app\\admin\\module\\controller\\Homebase' ,
    'lake\\module\\traits\\controller\\Admin' => 'app\\admin\\module\\traits\\controller\\Admin' ,
    'lake\\module\\traits\\controller\\Home' => 'app\\admin\\module\\traits\\controller\\Home' ,
]);

// 执行hook
function lake_admin_run_hook($name, $tags, $params = []) {
    if (empty($name) || empty($tags)) {
        return false;
    }
    
    $Hook = new Hook(app());
    $Hook->add($name, $tags);
    $Hook->listen($name, $params);
}

// 执行app_init全局hook信息
function lake_admin_app_init_hooks() {
    $hooks = Db::name('hook')
        ->where([
            'name' => 'app_init',
            'status' => 1,
        ])
        ->order('listorder ASC, id ASC')
        ->select();
        
    if (!empty($hooks)) {
        foreach ($hooks as $hook) {
            lake_admin_run_hook('app_init', $hook['class']);
        }
    }
}

// 配置全局hook信息
function lake_admin_hooks() {
    $hooks = Db::name('hook')
        ->where([
            ['name', 'not in', ['app_init']],
            ['status', '=', 1],
        ])
        ->order('listorder ASC, id ASC')
        ->select();
        
    if (!empty($hooks)) {
        foreach ($hooks as $hook) {
            FacadeHook::add($hook['name'], $hook['class']);    
        }
    }
}
    
// 设置 lake-admin 系统配置
function lake_admin_config()
{
    $path = env('lake_admin_app_path') . 'lake' . DIRECTORY_SEPARATOR;
    
    // 自动读取配置文件
    if (is_dir($path . 'config')) {
        $dir = $path . 'config' . DIRECTORY_SEPARATOR;
    }

    $files = isset($dir) ? scandir($dir) : [];

    foreach ($files as $file) {
        if ('.' . pathinfo($file, PATHINFO_EXTENSION) === env('config_ext', '.php')) {
            app()->config->load($dir . $file, pathinfo($file, PATHINFO_FILENAME));
        }
    }    
    
    // 注册系统默认指令
    Console::addDefaultCommands([    
        'app\admin\command\LakeAdminInstall',
    ]);
}
lake_admin_config();

// app初始化，全部模块
FacadeHook::add('app_init', function ($params) {    
    $path = env('lake_admin_app_path');
    
    $lake_admin_layout = $path . 'admin' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'layout.html';
    $lake_admin_input_item = $path . 'admin' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'inputItem.html';
    
    // 设置环境变量
    FacadeEnv::set([
        'lake_admin_layout' => $lake_admin_layout,
        'lake_admin_input_item' => $lake_admin_input_item,
    ]);
    
    // 设置公用参数
    FacadeView::share([
        'lake_admin_layout' => $lake_admin_layout,
        'lake_admin_input_item' => $lake_admin_input_item,
    ]);
    
    lake_admin_app_init_hooks();
    lake_admin_hooks();
    
});

// 更新模块配置信息
function lake_admin_container_config_update($module)
{
    $config = app()->config->get();

    // 注册异常处理类
    if ($config['app']['exception_handle']) {
        Error::setExceptionHandler($config['app']['exception_handle']);
    }

    Db::init($config['database']);
    app()->middleware->setConfig($config['middleware']);
    app()->route->setConfig($config['app']);
    app()->request->init($config['app']);
    app()->cookie->init($config['cookie']);
    app()->view->init($config['template']);
    app()->log->init($config['log']);
    app()->session->setConfig($config['session']);
    app()->debug->setConfig($config['trace']);
    app()->cache->init($config['cache'], true);

    // 加载当前模块语言包
    if ($module == 'admin') {
        $module_path = env('lake_admin_app_path');
    } else {
        $module_path = rtrim(config('module_path'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
    
    app()->lang->load($module_path . $module . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . app()->request->langset() . '.php');
    
    // 模块请求缓存检查
    app()->checkRequestCache(
        $config['app']['request_cache'],
        $config['app']['request_cache_expire'],
        $config['app']['request_cache_except']
    );
}

// 添加系统信息
FacadeHook::add('app_begin', function ($params) {
    $module = request()->module();
    
    if ($module == 'admin') {
        $module_path = env('lake_admin_app_path');

        // 模块路径
        $path = $module_path . $module . DIRECTORY_SEPARATOR;
    } else {
        $module_info = Db::name('module')
            ->field('module, path')
            ->where([
                'module' => $module,
                'status' => 1,
            ])
            ->find();
            
        if (!empty($module_info['path'])) {
            $path = rtrim($module_info['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        } else {
            $module_path = rtrim(config('module_path'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            
            // 模块路径
            $path = $module_path . $module . DIRECTORY_SEPARATOR;
        }
    }

    FacadeEnv::set([
        'lake_admin_module_path' => $path,
    ]);

    if (is_file($path . 'init.php')) {
        include $path . 'init.php';
    } else {
        // 加载行为扩展文件
        if (is_file($path . 'tags.php')) {
            $tags = include $path . 'tags.php';
            if (is_array($tags)) {
                app()->hook->import($tags);
            }
        }
        
        // 加载公共文件
        $common = $path . 'common.php';
        if (is_file($common)) {
            include_once $common;
        }

        // 加载中间件
        if (is_file($path . 'middleware.php')) {
            $middleware = include $path . 'middleware.php';
            if (is_array($middleware)) {
                app()->middleware->import($middleware);
            }
        }

        // 注册服务的容器对象实例
        if (is_file($path . 'provider.php')) {
            $provider = include $path . 'provider.php';
            if (is_array($provider)) {
                app()->bindTo($provider);
            }
        }
        
        // 自动读取配置文件
        if (is_dir($path . 'config')) {
            $dir = $path . 'config' . DIRECTORY_SEPARATOR;
        }

        $files = isset($dir) ? scandir($dir) : [];

        foreach ($files as $file) {
            if ('.' . pathinfo($file, PATHINFO_EXTENSION) === env('config_ext', '.php')) {
                app()->config->load($dir . $file, pathinfo($file, PATHINFO_FILENAME));
            }
        }
    }
    
    // 重设模块配置信息
    lake_admin_container_config_update($module);
    
    // 行为扩展 app_begin 兼容性处理
    if (isset($tags) && is_array($tags)) {
        if (isset($tags['app_begin'])) {
            lake_admin_run_hook('app_begin', $tags['app_begin'], $params);
        }
    }
    
    // 监听 lake_app_begin, 兼容框架 app_begin
    FacadeHook::listen('lake_admin_app_begin');
    
    // 加载语言包
    app()->lang->load($path . 'lang' . DIRECTORY_SEPARATOR . request()->langset() . '.php');
    
});

// 监听后台结束
FacadeHook::listen('lake_admin_hook_end');
