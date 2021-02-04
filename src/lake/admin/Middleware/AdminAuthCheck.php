<?php

namespace Lake\Admin\Middleware;

use Closure;
use think\App;

use Lake\Admin\Http\Traits\Jump as JumpTrait;
use Lake\Admin\Model\AuthRule as AuthRuleModel;
use Lake\Admin\Service\AdminAuth as AdminAuthService;
use Lake\Admin\Facade\Admin as AdminFacade;

/**
 * 登陆检测
 *
 * @create 2019-7-15
 * @author deatil
 */
class AdminAuthCheck
{
    use JumpTrait;
    
    /** @var App */
    protected $app;
    
    protected $loginUrl = '';
    
    public function __construct(App $app)
    {
        $this->app  = $app;
        $this->loginUrl = url('passport/login');
    }
    
    /**
     * 行为扩展的执行入口必须是run
     *
     * @create 2019-7-15
     * @author deatil
     */
    public function handle($request, Closure $next)
    {
        // 登陆检测
        $this->checkAdminLogin();
        
        $response = $next($request);
        
        // 地址检测
        $this->checkAdminRuleAuth();
        
        return $response;
    }
    
    /**
     * 检测登陆权限
     *
     * @create 2019-7-15
     * @author deatil
     */
    protected function checkAdminLogin()
    {
        // 重复检测跳过
        if ($this->app->env->get('admin_id')) {
            return;
        }
        
        $adminAllowIp = config('app.admin_allow_ip');
        if (!empty($adminAllowIp)) {
            // 检查IP地址访问
            $arr = explode(',', $adminAllowIp);
            foreach ($arr as $val) {
                // 是否是IP段
                if (strpos($val, '*')) {
                    if (strpos($this->app->request->ip(), str_replace('.*', '', $val)) !== false) {
                        $this->error('你的地址被禁止访问！');
                    }
                } else {
                    // 不是IP段,用绝对匹配
                    if ($this->app->request->ip() == $val) {
                        $this->error('你的地址被禁止访问！');
                    }
                }
            }
        }
        
        // 检测登陆
        $this->competence();
    }
    
    /**
     * 检测权限
     *
     * @create 2019-7-15
     * @author deatil
     */
    protected function checkAdminRuleAuth()
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
        $rule = str_replace([
            '.',
        ], [
            '/',
        ], $rule);
        
        if (!in_array($rule, $allowUrl)) {
            $adminId = $this->app->env->get('admin_id');
            if (empty($adminId)) {
                // 跳转到登录界面
                $this->error('请先登陆', $this->loginUrl);
            }
            
            // 是否是超级管理员
            $adminIsRoot = $this->app->env->get('admin_is_root');

            // 超级管理员跳过
            if ($adminIsRoot) {
                return;
            }
            
            $param = request()->param();
            
            $passList = [];
            $noNeedAuthRules = AuthRuleModel::getNoNeedAuthRuleList();
            if (!empty($noNeedAuthRules)) {
                foreach ($noNeedAuthRules as $noNeedAuthRule) {
                    $noNeedAuthRuleString = strtolower($noNeedAuthRule['method'].':'.$noNeedAuthRule['name']);
                    $noNeedAuthRuleQuery = preg_replace('/^.+\?/U', '', $noNeedAuthRuleString);
                    parse_str($noNeedAuthRuleQuery, $noNeedAuthRuleParam);
                    $intersect = array_intersect_assoc($param, $noNeedAuthRuleParam);
                    $noNeedAuth = preg_replace('/\?.*$/U', '', $noNeedAuthRuleString);
                    if ($noNeedAuth == $rule 
                        && serialize($intersect) == serialize($noNeedAuthRuleParam)
                    ) {
                        $passList[] = $noNeedAuth;
                    }
                }
            }
            
            if (empty($passList)) {
                // 检测访问权限
                if (!empty($param)) {
                    $rule = $rule . '?' . http_build_query($param);
                }
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
    final private function checkRule($rule, $type = [1, 2], $mode = 'url', $relation = 'or')
    {
        if (!AdminAuthService::checkRule($rule, $type, $mode, $relation)) {
            return false;
        }
        return true;
    }
}
