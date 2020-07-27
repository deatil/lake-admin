<?php

namespace app\admin\service;

use Composer\Autoload\ClassLoader;

use think\App;
use think\Console;

use app\admin\model\Hook as HookModel;
use app\admin\model\Module as ModuleModel;
use app\admin\facade\Module as ModuleFacade;
use app\admin\service\ModuleLoad as ModuleLoadService;

/**
 * 初始化模块
 *
 * @create 2020-4-15
 * @author deatil
 */
class InitModule
{
    /** @var App */
    protected $app;

    /**
     * 行为扩展执行入口
     *
     * @create 2020-4-15
     * @author deatil
     */
    public function handle()
    {
        $this->app  = app();
        
        // 后台命名空间
        $this->addModuleNamespace();
        
        // 嵌入点
        $this->addModuleHooks();
    }
    
    /**
     * 添加插件后台命名空间
     *
     * @create 2020-4-15
     * @author deatil
     */
    private function addModuleNamespace()
    {
        $appNamespace = $this->app->config->get('app.module_namespace');
        $modulePath = $this->app->config->get('app.module_path');
        
        $modules = $this->app->cache->get('lake_admin_modules');
        if (empty($modules)) {
            $modules = ModuleModel::where([
                    'status' => 1,
                ])
                ->field('module, path')
                ->select()
                ->toArray();
            
            $this->app->cache->set('lake_admin_modules', $modules);
        }
        
        if (!empty($modules)) {
            foreach ($modules as $module) {
                if (!empty($module['path'])) {
                    $namespaceModulePath = ModuleFacade::getModuleRealPath($module['path']);
                } else {
                    $namespaceModulePath = $modulePath . $module['module'];
                }
                
                $namespaceModulePath = rtrim($namespaceModulePath, DIRECTORY_SEPARATOR);
                
                $moduleNamespaces = [
                    $appNamespace . '\\' . $module['module'] . '\\' => $namespaceModulePath . DIRECTORY_SEPARATOR,
                    $appNamespace . '\\api\\' => $namespaceModulePath . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR,
                    $appNamespace . '\\admin\\' => $namespaceModulePath . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR,
                ];
                
                $loader = new ClassLoader();
                foreach ($moduleNamespaces as $namespace => $path) {
                    $loader->addPsr4($namespace, $path);
                }
                $loader->register();
                unset($loader);
                
                // 设置模块地址
                $appMaps = $this->app->config->get('app.app_map');
                $appMaps = array_merge($appMaps, [
                    $module['module'] => function($app) use($namespaceModulePath) {
                        $app->http->path($namespaceModulePath);
                    },
                ]);
                $this->app->config->set([
                    'app_map' => $appMaps,
                ], 'app');
                
                // 引入模块公共文件
                $moduleGlobal = $namespaceModulePath . DIRECTORY_SEPARATOR . 'global' . DIRECTORY_SEPARATOR;
                $this->loadModuleConfigAndFile($moduleGlobal);
                
                // 注册模块指令
                $commands = $this->app->config->get('app.command');
                if (!empty($commands) && is_array($commands)) {
                    Console::starting(function (Console $console) use ($commands) {
                        $console->addCommands($commands);
                    });
                }
            }
        }
    }

    /**
     * 导入模型公用配置和文件
     *
     * @create 2019-10-9
     * @author deatil
     */
    private function loadModuleConfigAndFile($path)
    {
        (new ModuleLoadService($this->app))->loadApp($path);
    }
    
    /**
     * 添加嵌入点
     *
     * @create 2019-7-18
     * @author deatil
     */
    private function addModuleHooks()
    {
        $hooks = $this->app->cache->get('lake_admin_hooks');
        if (empty($hooks)) {
            // 所有模块钩子
            $hooks = HookModel::field('name, class')
                ->order('listorder ASC')
                ->select()
                ->toArray();
            
            $this->app->cache->set('lake_admin_hooks', $hooks);
        }
        
        if (!empty($hooks)) {
            foreach ($hooks as $key => $value) {
                $this->app->event->listen($value['name'], $value['class']);
            }
        }
    }

}