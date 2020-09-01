<?php

namespace Lake\Admin\Middleware;

use Closure;
use think\App;
use think\Event;

use Lake\Admin\Model\Module as ModuleModel;
use Lake\Admin\Facade\Module as ModuleFacade;
use Lake\Admin\Service\ModuleLoad as ModuleLoadService;
use Lake\Admin\Service\ModuleEvent as ModuleEventService;

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
     *
     * @create 2020-4-8
     * @author deatil
     */
    protected function loadModule()
    {
        $appName = $this->app->http->getName();
        if ($appName == 'admin') {
            // 后台特殊处理
            $adminPath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
            (new ModuleLoadService($this->app))->loadApp($adminPath);
        }
        
        $systemModuleList = config('app.system_module_list');
        if (in_array($appName, $systemModuleList)) {
            return false;
        }
        
        // 不是安装的模块不进行下面的步骤
        $check = ModuleFacade::checkModule($appName);
        if ($check === false) {
            return false;
        }
        
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
