<?php

namespace app\admin\behavior;

use think\Db;
use think\Loader;
use think\facade\Cache;
use think\facade\Hook;
use think\facade\Env;

/**
 * 初始化钩子信息
 *
 * @create 2019-7-6
 * @author deatil
 */
class InitHook
{

    /**
     * 行为扩展执行入口
     *
     * @create 2019-7-6
     * @author deatil
     */
    public function run($params)
    {
        // 后台命名空间
        $this->addModuleNamespace();
        
        // 嵌入点
        $this->addModuleHooks();
    }
    
    /**
     * 添加插件后台命名空间
     *
     * @create 2019-7-6
     * @author deatil
     */
    private function addModuleNamespace()
    {
        $app_namespace = app()->getNamespace();
        $module_path = config('module_path');
        
        $modules = Cache::get('modules');
        if (empty($modules)) {
            $modules = Db::name('module')
                ->field('module, path')
                ->where([
                    'status' => 1,
                ])
                ->select();
            
            Cache::set('modules', $modules);
        }
        
        if (!empty($modules)) {
            foreach ($modules as $module) {
                if (!empty($module['path'])) {
                    $namespace_module_path = $module['path'];
                } else {
                    $namespace_module_path = $module_path . $module['module'];
                }
                
                $namespace_module_path = rtrim($namespace_module_path, DIRECTORY_SEPARATOR);
                
                $module_namespace = [
                    $app_namespace . '\\' . $module['module'] => $namespace_module_path . DIRECTORY_SEPARATOR,
                    $app_namespace . '\\api' => $namespace_module_path . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR,
                    $app_namespace . '\\admin' => $namespace_module_path . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR,
                ];
                
                Loader::addNamespace($module_namespace);
                
                // 引入公共文件
                $global = $namespace_module_path . DIRECTORY_SEPARATOR . 'global' . DIRECTORY_SEPARATOR;
                $this->loadModuleConfigAndFile($global);
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
        // 自动加载公用文件
        if (is_dir($path)) {
            $path_dir = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }

        $path_files = isset($path_dir) ? scandir($path_dir) : [];

        foreach ($path_files as $path_file) {
            if ('.' . pathinfo($path_file, PATHINFO_EXTENSION) === '.php' 
                && file_exists($path_dir . $path_file)
                && is_file($path_dir . $path_file)
            ) {
                include_once $path_dir . $path_file;
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
        
        // 加载语言包
        $lang_file = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . request()->langset() . '.php';
        app()->lang->load($lang_file);
    }

    /**
     * 添加嵌入点
     *
     * @create 2019-7-18
     * @author deatil
     */
    private function addModuleHooks()
    {
        $hooks = Cache::get('lake_admin_hooks');
        if (empty($hooks)) {
            // 所有模块钩子
            $hooks = Db::name('Hook')
                ->field('name, class')
                ->order('listorder ASC')
                ->select();
            
            Cache::set('lake_admin_hooks', $hooks);
        }

        if (!empty($hooks)) {
            foreach ($hooks as $key => $value) {
                Hook::add($value['name'], $value['class']);
            }
        }
    }

}