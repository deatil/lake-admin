<?php

namespace Lake\Admin;

use think\Service as BaseService;
use think\console\Input;

use Lake\Admin\Auth\Permission;
use Lake\Admin\Model\Event as EventModel;
use Lake\Admin\Middleware\LakeAdminAppMap;
use Lake\Admin\Middleware\LoadModule;
use Lake\Admin\Service\ModuleInit as ModuleInitService;
use Lake\Admin\Service\ConfigInit as ConfigInitService;

// 引用文件夹
use Lake\Admin\Command;

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
        // 全局配置
        $this->setGlobalConfig();
        
        // 系统配置
        $this->setSystemConfig();
        
        // 系统别名
        $this->setSystemAlias();
        
        // 绑定
        $this->setSystemBind();
        
        // 注册系统默认指令
        $this->commands([
            Command\Install::class,
            Command\Repair::class,
            Command\ServiceDiscover::class,
            Command\ResetPassword::class,
        ]);
    }
    
    public function boot()
    {
        if ($this->isLakeAdminInstallCli() !== true) {
            // 初始化配置信息
            (new ConfigInitService)->handle();
            
            //  全局自定义事件
            $this->setSystemEvents();
            
            // 初始化模块
            (new ModuleInitService)->handle();
            
            $this->app->event->listen('HttpRun', function () {
                $this->app->middleware->add(LakeAdminAppMap::class);
            }, true);
            
            // 导入模块
            $this->app->event->listen('HttpRun', function ($params) {
                $this->app->middleware->add(LoadModule::class);
            });
        }
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
        
        $installLockFile = root_path() . 'install.lock';
        if (!file_exists($installLockFile)) {
            $isInstallCli = true;
        }
        
        return $isInstallCli;
    }
    
    /**
     * 配置全局配置信息
     *
     * @create 2020-8-6
     * @author deatil
     */
    protected function setGlobalConfig() 
    {
        $appPath = dirname(__DIR__);
        $viewPath = $appPath . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'view';
        
        $lakeAdminLayout = $viewPath . DIRECTORY_SEPARATOR . 'layout.html';
        $lakeAdminInputItem = $viewPath . DIRECTORY_SEPARATOR . 'inputItem.html';
        
        // 设置环境变量
        $this->app->env->set([
            // 设置admin目录
            'lake_admin_app_path' => $appPath,
            
            // 页面变量
            'lake_admin_layout' => $lakeAdminLayout,
            'lake_admin_input_item' => $lakeAdminInputItem,
        ]);
        
        // 设置公用参数
        $this->app->view->assign([
            'lake_admin_layout' => $lakeAdminLayout,
            'lake_admin_input_item' => $lakeAdminInputItem,
        ]);
    }
    
    /**
     * 设置 lake-admin 系统配置
     *
     * @create 2020-4-7
     * @author deatil
     */
    protected function setSystemConfig()
    {
        $path = $this->app->env->get('lake_admin_app_path') . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
        
        // 自动读取配置文件
        if (is_dir($path . 'config')) {
            $dir = $path . 'config' . DIRECTORY_SEPARATOR;
        }

        $files = isset($dir) ? scandir($dir) : [];

        foreach ($files as $file) {
            if ('.' . pathinfo($file, PATHINFO_EXTENSION) === $this->app->env->get('config_ext', '.php')) {
                $this->app->config->load($dir . $file, pathinfo($file, PATHINFO_FILENAME));
            }
        }
    }
    
    /**
     * 配置系统控制器
     *
     * @create 2020-8-23
     * @author deatil
     */
    protected function setSystemAlias() 
    {
        $controllers = $this->app->config->get('lakealias');
        foreach ($controllers as $alias => $controller) {
            class_alias($controller, $alias);
        }
    }
    
    /**
     * 系统绑定
     *
     * @create 2020-8-30
     * @author deatil
     */
    protected function setSystemBind()
    {
        // 绑定权限检测
        $this->app->bind('auth', function() {
            $Permission = new Permission;
            
            $ruleType = config('auth.rule_type', 1);
            $Permission->withRuleType($ruleType);
            
            return $Permission;
        });
    }
    
    /**
     * 配置全局事件
     *
     * @create 2020-4-7
     * @author deatil
     */
    protected function setSystemEvents() 
    {
        $events = $this->app->cache->get("lake_admin_events", []);
        if (empty($events)) {
            $events = EventModel::field('name,class')
                ->where([
                    ['status', '=', 1],
                ])
                ->order('listorder ASC, id ASC')
                ->select()
                ->toArray();
            $this->app->cache->set("lake_admin_events", $events);
        }
        
        foreach ($events as $event) {
            if (class_exists($event['class'])) {
                $this->app->event->listen($event['name'], $event['class']);
            }
        }
    }
    
}
