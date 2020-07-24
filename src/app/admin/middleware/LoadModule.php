<?php

namespace app\admin\middleware;

use Closure;
use think\App;
use think\Event;
use think\facade\Db;

use app\admin\model\Module as ModuleModel;
use app\admin\facade\Module as ModuleFacade;

/**
 * lake-admin 中间件
 *
 * @create 2020-4-8
 * @author deatil
 */
class LoadModule
{
    
    /** @var App */
    protected $app;
    
    public function __construct(App $app)
    {
        $this->app  = $app;
    }
    
    /**
     * 中间件
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return Response
     *
     * @create 2020-4-7
     * @author deatil
     */
    public function handle($request, Closure $next)
    {
        $this->loadModule();;
        
        return $next($request);
    }
    
    /**
     * 导入模块信息
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return Response
     *
     * @create 2020-4-8
     * @author deatil
     */
    protected function loadModule($params = [])
    {
        $appName = $this->app->http->getName();
        if ($appName == 'admin') {
            // 应用路径
            $appPath = dirname(__DIR__) . DIRECTORY_SEPARATOR;
        } else {
            $moduleInfo = ModuleModel::field('module, path')
                ->where([
                    'module' => $appName,
                    'status' => 1,
                ])
                ->find();
                
            if (!empty($moduleInfo['path'])) {
                $appPath = rtrim(ModuleFacade::getModuleRealPath($moduleInfo['path']), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            } else {
                $modulePath = rtrim(config('app.module_path'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                
                // 模块路径
                $appPath = $modulePath . $appName . DIRECTORY_SEPARATOR;
            }
        }
        
        $this->app->env->set([
            'lake_admin_module_path' => $appPath,
        ]);
        
        $this->loadApp($appPath, $params);
    }
    
    /**
     * 加载应用配置文件
     * @param string $appPath 应用路径
     * @return void
     *
     * @create 2020-4-7
     * @author deatil
     */
    protected function loadApp($appPath, $params = [])
    {
        if (is_file($appPath . 'common.php')) {
            include_once $appPath . 'common.php';
        }
        
        $files = [];
        
        $files = array_merge($files, glob($appPath . 'config' . DIRECTORY_SEPARATOR . '*' . $this->app->getConfigExt()));
        
        foreach ($files as $file) {
            $this->app->config->load($file, pathinfo($file, PATHINFO_FILENAME));
        }
        
        if (is_file($appPath . 'event.php')) {
            $events = include $appPath . 'event.php';
            if (is_array($events)) {
                $this->app->loadEvent($events);
            }
        }
        
        if (is_file($appPath . 'middleware.php')) {
            $this->app->middleware->import(include $appPath . 'middleware.php', 'app');
        }
        
        if (is_file($appPath . 'provider.php')) {
            $this->app->bind(include $appPath . 'provider.php');
        }
        
        // 加载应用默认语言包
        $this->app->loadLangPack($this->app->lang->defaultLangSet());
        
        // 行为扩展 HttpRun 兼容性处理
        if (isset($events) && is_array($events)) {
            if (isset($events['listen']['HttpRun'])) {
                $this->triggerEvent('HttpRun', $events['listen']['HttpRun'], $params);
            }
        }
    }
    
    /**
     * 触发事件
     *
     * @create 2020-4-7
     * @author deatil
     */
    protected function triggerEvent($event, $listeners, $params = null) 
    {
        if (empty($event) || empty($listeners)) {
            return false;
        }
        
        $EventObj = new Event($this->app);
        if (is_array($listeners)) {
            foreach ($listeners as $listener) {
                $EventObj->listen($event, $listener);
            }
        } else {
            $EventObj->listen($event, $listeners);
        }
        
        $EventObj->trigger($event, $params);
    }
    
}
