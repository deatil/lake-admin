<?php

namespace app\admin\middleware;

use Closure;
use think\App;

use app\admin\boot\Jump;
use app\admin\model\AuthRule as AuthRuleModel;
use app\admin\service\AdminAuth as AdminAuthService;
use app\admin\facade\Admin as AdminFacade;

/**
 * 登陆检测
 *
 * @create 2019-7-15
 * @author deatil
 */
class AdminAuthCheck
{
    use Jump;
    
    /** @var App */
    protected $app;
    
    protected $loginUrl = '';
    
    public function __construct(App $app)
    {
        $this->app  = $app;
    }
    
    /**
     * 行为扩展的执行入口必须是run
     *
     * @create 2019-7-15
     * @author deatil
     */
    public function handle($request, Closure $next)
    {
        $this->loginUrl = url('passport/login');
        
        $this->checkAdminLogin();
        
        $response = $this->app->middleware->pipeline('app')
            ->send($request)
            ->then(function ($request) use ($next) {
                return $next($request);
            });
        
        return $response;
    }
    
    /**
     * 检测权限
     *
     * @create 2019-7-15
     * @author deatil
     */
    protected function checkAdminLogin()
    {
        // 过滤不需要登陆的行为
        $allowUrl = [
            'get:admin/passport/captcha',
            'get:admin/passport/login',
            'post:admin/passport/login',
            'get:admin/passport/logout',
        ];
        
        $rule = strtolower(
            $this->app->request->method() . 
            ':' . $this->app->http->getName() . 
            '/' . $this->app->request->controller() . 
            '/' . $this->app->request->action()
        );
        
        if (!in_array($rule, $allowUrl)) {
            // 重复检测跳过
            if ($this->app->env->get('admin_id')) {
                return;
            }
            
            // 检测登陆
            if (false === $this->competence()) {
                // 跳转到登录界面
                $this->error('请先登陆', $this->loginUrl);
            }
            
            // 是否是超级管理员
            $adminIsRoot = $this->app->env->get('admin_is_root');

            // 超级管理员跳过
            if ($adminIsRoot) {
                return;
            }
        
            if (config('app.admin_allow_ip')) {
                // 检查IP地址访问
                $arr = explode(',', config('app.admin_allow_ip'));
                foreach ($arr as $val) {
                    // 是否是IP段
                    if (strpos($val, '*')) {
                        if (strpos($this->app->request->ip(), str_replace('.*', '', $val)) !== false) {
                            $this->error('403:你在IP禁止段内,禁止访问！');
                        }
                    } else {
                        // 不是IP段,用绝对匹配
                        if ($this->app->request->ip() == $val) {
                            $this->error('403:IP地址绝对匹配,禁止访问！');
                        }
                    }
                }
            }
            
            $noNeedAuthRules = (new AuthRuleModel())->getNoNeedAuthRuleList();
            if (!in_array($rule, $noNeedAuthRules)) {
                // 检测访问权限
                if (!$this->checkRule($rule, [1, 2])) {
                    $this->error('未授权访问!');
                }
            }
        }
    }
    
    /**
     * 验证登录
     * @return boolean
     *
     * @create 2019-7-15
     * @author deatil
     */
    final private function competence()
    {
        // 检查是否登录
        $adminId = AdminFacade::isLogin();
        if (empty($adminId)) {
            return false;
        }
        
        // 获取当前登录用户信息
        $adminInfo = AdminFacade::getLoginUserInfo();
        if (empty($adminInfo)) {
            AdminFacade::logout();
            return false;
        }
        
        // 是否锁定
        if (!$adminInfo['status']) {
            AdminFacade::logout();
            $this->error('您的帐号已经被锁定！', $this->loginUrl);
            return false;
        }
        
        // 是否是超级管理员
        $adminIsRoot = AdminFacade::isAdministrator();
        
        $this->app->env->set([
            'admin_id' => $adminId,
            'admin_is_root' => $adminIsRoot,
            'admin_info' => $adminInfo,
        ]);
        
        return $adminInfo;
    }
    
    /**
     * 权限检测
     * @param string  $rule    检测的规则
     * @param string  $mode    check模式
     * @return boolean
     *
     * @create 2019-7-15
     * @author deatil
     */
    final private function checkRule($rule, $type = AuthRule::RULE_URL, $mode = 'url', $relation = 'or')
    {
        if (!AdminAuthService::checkRule($rule, $type, $mode, $relation)) {
            return false;
        }
        return true;
    }
}
