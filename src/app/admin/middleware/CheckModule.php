<?php

namespace app\admin\middleware;

use Closure;
use think\App;

use app\admin\module\Module as ModuleModule;

/**
 * 检测模块
 *
 * @create 2019-7-14
 * @author deatil
 */
class CheckModule
{

    /** @var App */
    protected $app;

    public function __construct(App $app)
    {
        $this->app  = $app;
    }
    
    /**
     * 行为扩展执行入口
     *
     * @create 2019-7-6
     * @author deatil
     */
    public function handle($request, Closure $next)
    {
        $module = $this->app->http->getName();
        
        $ModuleModule = new ModuleModule();
        $check = $ModuleModule->checkModule($module);
        
        if ($check === false) {
            $error = $ModuleModule->getError();
            abort(404, $error);
        }
        
        return $next($request);
    }

}
