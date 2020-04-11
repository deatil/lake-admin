<?php

namespace app\admin\boot;

use think\Service as BaseService;
use think\facade\View;
use think\facade\Db;

use app\admin\middleware\LakeAdminAppMap;
use app\admin\middleware\InitHook;
use app\admin\middleware\LoadModule;
use app\admin\middleware\CheckModule;

/**
 * lake-admin 服务
 *
 * @create 2020-4-7
 * @author deatil
 */
class Service extends BaseService
{
    public function register()
    {
        // 设置错误跳转页面目录
        $this->app->env->set([
            'lake_admin_app_path' => rtrim(dirname(dirname(__DIR__)), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR,
        ]);
        
        // 设置模块基础别名
        class_alias('app\\admin\\module\\Module', 'lake\\Module');
        class_alias('app\\admin\\module\\controller\\AdminBase', 'lake\\module\\controller\\AdminBase');
        class_alias('app\\admin\\module\\controller\\Homebase', 'lake\\module\\controller\\Homebase');
        
        // 系统配置
        $this->setSystemConfig();
    }
    
    public function boot()
    {
        $this->app->event->listen('HttpRun', function () {
            // 注册钩子
            $this->app->middleware->add(InitHook::class);
            $this->app->middleware->add(LakeAdminAppMap::class);
        }, true);
        
        // 注册配置行为
        $this->app->event->listen('HttpRun', "app\\admin\\behavior\\InitConfig", true);
        
        // 注册系统默认指令
        $this->commands([
            'LakeAdminInstall' => \app\admin\command\LakeAdminInstall::class,
        ]);
        
        // app初始化，全部模块
        $this->app->event->listen('HttpRun', function ($params) {    
            $path = env('lake_admin_app_path');
            
            $lake_admin_layout = $path . 'admin' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'layout.html';
            $lake_admin_input_item = $path . 'admin' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'inputItem.html';
            
            // 设置环境变量
            $this->app->env->set([
                'lake_admin_layout' => $lake_admin_layout,
                'lake_admin_input_item' => $lake_admin_input_item,
            ]);
            
            // 设置公用参数
            View::assign([
                'lake_admin_layout' => $lake_admin_layout,
                'lake_admin_input_item' => $lake_admin_input_item,
            ]);
            
            $this->HttpRunHooks();
            
            $this->app->middleware->add(LoadModule::class);
            
            $this->setSystemHooks();
            
            // 模块检测
            $this->app->middleware->add(CheckModule::class);
            
        });
    }
    
    /**
     * 执行 HttpRun 全局hook信息
     *
     * @create 2020-4-7
     * @author deatil
     */
    protected function HttpRunHooks() 
    {
        $hooks = Db::name('hook')
            ->where([
                'name' => 'HttpRun',
                'status' => 1,
            ])
            ->order('listorder ASC, id ASC')
            ->select();
            
        if (!empty($hooks)) {
            foreach ($hooks as $hook) {
                $this->triggerEvent('HttpRun', $hook['class']);
            }
        }
    }
    
    /**
     * 配置全局hook信息
     *
     * @create 2020-4-7
     * @author deatil
     */
    protected function setSystemHooks() {
        $hooks = Db::name('hook')
            ->where([
                ['name', 'not in', ['app_init']],
                ['status', '=', 1],
            ])
            ->order('listorder ASC, id ASC')
            ->select();
            
        if (!empty($hooks)) {
            foreach ($hooks as $hook) {
                $this->app->event->listen($hook['name'], $hook['class']);
            }
        }
    }
    
    /**
     * 设置 lake-admin 系统配置
     *
     * @create 2020-4-7
     * @author deatil
     */
    protected function setSystemConfig()
    {
        $path = env('lake_admin_app_path') . 'lake' . DIRECTORY_SEPARATOR;
        
        // 自动读取配置文件
        if (is_dir($path . 'config')) {
            $dir = $path . 'config' . DIRECTORY_SEPARATOR;
        }

        $files = isset($dir) ? scandir($dir) : [];

        foreach ($files as $file) {
            if ('.' . pathinfo($file, PATHINFO_EXTENSION) === env('config_ext', '.php')) {
                $this->app->config->load($dir . $file, pathinfo($file, PATHINFO_FILENAME));
            }
        }    
    }
    
}
