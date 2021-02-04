<?php

namespace Lake\Admin\Middleware;

use Closure;
use think\App;

use Lake\Admin\Http\Traits\Jump as JumpTrait;
use Lake\Admin\Service\Screen as ScreenService;

/**
 * 锁屏检测
 *
 * @create 2020-7-21
 * @author deatil
 */
class AdminScreenLockCheck
{
    use JumpTrait;
    
    /** @var App */
    protected $app;
    
    public function __construct(App $app)
    {
        $this->app  = $app;
    }
    
    /**
     * 入口
     *
     * @create 2020-7-21
     * @author deatil
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        $this->checkScreenLock();
        
        return $response;
    }
    
    
    /**
     * 检测锁屏
     *
     * @create 2020-8-5
     * @author deatil
     */
    protected function checkScreenLock()
    {
        // 过滤的行为
        $allowUrl = [
            'get:admin/passport/captcha',
            'get:admin/passport/login',
            'post:admin/passport/login',
            'get:admin/passport/logout',
            'post:admin/passport/lockscreen',
            'post:admin/passport/unlockscreen',
            'get:admin/index/index',
            'get:admin/index/main',
        ];
        
        $rule = strtolower(
            $this->app->request->method() . 
            ':' . $this->app->http->getName() . 
            '/' . $this->app->request->controller() . 
            '/' . $this->app->request->action()
        );
        
        if (!in_array($rule, $allowUrl)) {
            $check = (new ScreenService())->check();
            if ($check !== false) {
                $url = url('index/index');
                $this->error('后台已锁定，请先解锁', $url);
            }
        }
    }
    
}
