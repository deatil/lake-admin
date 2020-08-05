<?php

namespace app\admin\middleware;

use Closure;
use think\App;
use think\Event;

use app\admin\model\Module as ModuleModel;
use app\admin\facade\Module as ModuleFacade;
use app\admin\service\ModuleLoad as ModuleLoadService;
use app\admin\service\ModuleEvent as ModuleEventService;

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
        $this->loadModule();
        
        return $this->app->middleware->pipeline('app')
            ->send($request)
            ->then(function ($request) use ($next) {
                return $next($request);
            });
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
    protected function loadModule()
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
        
        (new ModuleLoadService($this->app))->loadApp($appPath);
        
        // 事件兼容性处理
        $this->triggerEvent($appPath);
    }

    /**
     * HttpRun 事件兼容性处理
     * @param string $langset 语言
     * @return void
     */
    protected function triggerEvent($appPath)
    {
        if (is_file($appPath . 'event.php')) {
            $events = include $appPath . 'event.php';
            if (is_array($events)) {
                (new ModuleEventService($this->app))->load($events)->trigger('HttpRun');
            }
        }
    }
    
}
