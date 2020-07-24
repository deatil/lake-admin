<?php

namespace app\admin\boot;

use think\Service as BaseService;
use think\facade\View;
use think\facade\Cache;
use think\console\Input;

use app\admin\middleware\LakeAdminAppMap;
use app\admin\middleware\LoadModule;
use app\admin\middleware\CheckModule;

use app\admin\model\Hook as HookModel;
use app\admin\service\InitHook as InitHookService;

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
        // 设置admin目录
        $this->app->env->set([
            'lake_admin_app_path' => rtrim(dirname(dirname(__DIR__)), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR,
        ]);
        
        // 设置模块基础别名
        class_alias('app\\admin\\module\\Module', 'lake\\Module');
        class_alias('app\\admin\\module\\controller\\AdminBase', 'lake\\module\\controller\\AdminBase');
        class_alias('app\\admin\\module\\controller\\HomeBase', 'lake\\module\\controller\\HomeBase');
        
        // 系统配置
        $this->setSystemConfig();
        
        if ($this->isLakeAdminInstallCli() !== true) {
            // 初始化钩子信息
            (new InitHookService())->handle();
        }
    }
    
    public function boot()
    {
        $this->app->event->listen('HttpRun', function () {
            $this->app->middleware->add(LakeAdminAppMap::class);
        }, true);
        
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
        });
        
        if ($this->isLakeAdminInstallCli() !== true) {
            // 注册配置行为
            $this->app->event->listen('HttpRun', "app\\admin\\listener\\InitConfig", true);
            
            // 全部模块
            $this->app->event->listen('HttpRun', function ($params) {    
                // 后台系统配置
                $this->setSystemHooks();
                
                // 导入模块
                $this->app->middleware->add(LoadModule::class);
                
                // 模块检测
                $this->app->middleware->add(CheckModule::class);
            });
        }
        
        // 注册系统默认指令
        $this->commands([
            \app\admin\command\LakeAdminInstall::class,
            \app\admin\command\LakeAdminRepair::class,
            \app\admin\command\LakeAdminServiceDiscover::class,
        ]);
    }
    
    /**
     * 是否为安装系统命令
     *
     * @create 2020-5-2
     * @author deatil
     */
    protected function isLakeAdminInstallCli()
    {
        $isInstallCli = false;
        if ($this->app->request->isCli()) {
            $commandName = (new Input())->getFirstArgument();
            if ($commandName == 'lake-admin:install') {
                $isInstallCli = true;
            }
        }
        
        return $isInstallCli;
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
    
    /**
     * 配置全局hook信息
     *
     * @create 2020-4-7
     * @author deatil
     */
    protected function setSystemHooks() 
    {
        $hooks = Cache::get("lake_admin_hooks");
        if (empty($hooks)) {
            $hooks = HookModel::field('name,class')
                ->where([
                    ['status', '=', 1],
                ])
                ->order('listorder ASC, id ASC')
                ->select()
                ->toArray();
            Cache::set("lake_admin_hooks", $hooks);
        }
            
        if (!empty($hooks)) {
            foreach ($hooks as $hook) {
                $this->app->event->listen($hook['name'], $hook['class']);
            }
        }
    }
    
}
