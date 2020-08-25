<?php

namespace lake\admin\boot;

use think\Service as BaseService;
use think\console\Input;

use lake\admin\model\Event as EventModel;
use lake\admin\middleware\LakeAdminAppMap;
use lake\admin\middleware\LoadModule;
use lake\admin\service\ModuleInit as ModuleInitService;
use lake\admin\service\ConfigInit as ConfigInitService;

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
        }
        
        $this->app->event->listen('HttpRun', function () {
            $this->app->middleware->add(LakeAdminAppMap::class);
        }, true);
        
        if ($this->isLakeAdminInstallCli() !== true) {
            // 全部模块
            $this->app->event->listen('HttpRun', function ($params) {
                // 导入模块
                $this->app->middleware->add(LoadModule::class);
            });
        }
        
        // 注册系统默认指令
        $this->commands([
            \lake\admin\command\LakeAdminInstall::class,
            \lake\admin\command\LakeAdminRepair::class,
            \lake\admin\command\LakeAdminServiceDiscover::class,
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
     * 配置全局配置信息
     *
     * @create 2020-8-6
     * @author deatil
     */
    protected function setGlobalConfig() 
    {
        $path = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
        
        // 设置admin目录
        $this->app->env->set([
            'lake_admin_app_path' => $path,
        ]);
        
        $lake_admin_layout = $path . 'admin' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'layout.html';
        $lake_admin_input_item = $path . 'admin' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'inputItem.html';
        
        // 设置环境变量
        $this->app->env->set([
            'lake_admin_layout' => $lake_admin_layout,
            'lake_admin_input_item' => $lake_admin_input_item,
        ]);
        
        // 设置公用参数
        $this->app->view->assign([
            'lake_admin_layout' => $lake_admin_layout,
            'lake_admin_input_item' => $lake_admin_input_item,
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
        $path = $this->app->env->get('lake_admin_app_path') . 'lake' . DIRECTORY_SEPARATOR;
        
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
     * 配置全局事件
     *
     * @create 2020-4-7
     * @author deatil
     */
    protected function setSystemEvents() 
    {
        $events = $this->app->cache->get("lake_admin_events");
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
        
        if (!empty($events)) {
            foreach ($events as $event) {
                $this->app->event->listen($event['name'], $event['class']);
            }
        }
    }
    
}
