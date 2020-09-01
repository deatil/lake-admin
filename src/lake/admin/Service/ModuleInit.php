<?php

namespace Lake\Admin\Service;

use Composer\Autoload\ClassLoader;

use think\App;
use think\Console;

use Lake\Admin\Model\Module as ModuleModel;
use Lake\Admin\Facade\Module as ModuleFacade;
use Lake\Admin\Service\ModuleLoad as ModuleLoadService;

/**
 * 初始化模块
 *
 * @create 2020-4-15
 * @author deatil
 */
class ModuleInit
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
                
                // 模块全局加载文件夹
                $moduleGlobalPath = $namespaceModulePath . DIRECTORY_SEPARATOR . 'global' . DIRECTORY_SEPARATOR;
                
                // 引入模块公共文件
                $this->loadModule($moduleGlobalPath);
                
                // 注册模块指令
                $moduleCommand = $this->app->config->get($module['module'] . '.command');
                $this->addCommands($moduleCommand);
            }
        }
        
        // 注册全局指令
        $command = $this->app->config->get('command');
        $this->addCommands($command);
    }

    /**
     * 导入模型公用配置和文件
     *
     * @create 2019-10-9
     * @author deatil
     */
    private function loadModule($appPath)
    {
        $ModuleLoadService = new ModuleLoadService($this->app);
        
        // 加载应用配置文件
        $ModuleLoadService->loadApp($appPath);
        
        // 加载服务
        $ModuleLoadService->loadService($appPath);
    }

    /**
     * 注册指令
     *
     * @create 2020-7-31
     * @author deatil
     */
    private function addCommands($command)
    {
        if (!empty($command) && is_array($command)) {
            Console::starting(function (Console $console) use ($command) {
                $console->addCommands($command);
            });
        }
    }

}