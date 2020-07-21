<?php

namespace app\admin\middleware;

use Closure;

use app\admin\boot\Jump as JumpTrait;
use app\admin\service\Screen as ScreenService;

/**
 * 锁屏检测
 *
 * @create 2020-7-21
 * @author deatil
 */
class AdminScreenLockCheck
{
    use JumpTrait;
    
    /**
     * 入口
     *
     * @create 2020-7-21
     * @author deatil
     */
    public function handle($request, Closure $next)
    {
        // 过滤不需要登陆的行为
        $allowUrl = [
            'admin/passport/captcha',
            'admin/passport/login',
            'admin/passport/logout',
            'admin/index/index',
            'admin/index/main',
            'admin/screen/unlock',
        ];
        
        $rule = strtolower(
            app()->http->getName() . 
            '/' . request()->controller() . 
            '/' . request()->action()
        );
        
        if (!in_array($rule, $allowUrl)) {
            $check = (new ScreenService())->check();
            if ($check !== false) {
                $url = url('index/index');
                $this->error('后台已锁定，请先解锁', $url);
            }
        }
        
        $request = app()->middleware->pipeline('app')
            ->send($request)
            ->then(function ($request) use ($next) {
                return $next($request);
            });
        
        return $request;
    }
}
