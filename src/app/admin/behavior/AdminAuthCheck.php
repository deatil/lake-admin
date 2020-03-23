<?php

namespace app\admin\behavior;

use think\facade\Env;
use traits\controller\Jump;

use app\admin\Model\AuthRule as AuthRuleModel;
use app\admin\service\Auth as AuthService;
use app\admin\service\Admin as AdminService;

/**
 * 登陆检测
 *
 * @create 2019-7-15
 * @author deatil
 */
class AdminAuthCheck
{
    use Jump;
    
    protected $loginUrl = '';
    
    /**
     * 行为扩展的执行入口必须是run
     *
     * @create 2019-7-15
     * @author deatil
     */
    public function run($params)
    {
        $this->loginUrl = url('passport/login');
        
        $this->checkAdminLogin();
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
            'admin/passport/captcha',
            'admin/passport/login',
            'admin/passport/logout',
        ];
        
        $rule = strtolower(request()->module() . '/' . request()->controller() . '/' . request()->action());
        
        if (!in_array($rule, $allowUrl)) {
            
            if (Env::get('admin_id')) {
                return;
            }
            
            $adminId = AdminService::instance()->isLogin();
            
            // 是否是超级管理员
            $adminIsRoot = AdminService::instance()->isAdministrator();
        
            Env::set([
                'admin_id' => $adminId,
                'admin_is_root' => $adminIsRoot,
            ]);
        
            if (!$adminIsRoot && config('admin_allow_ip')) {
                // 检查IP地址访问
                $arr = explode(',', config('admin_allow_ip'));
                foreach ($arr as $val) {
                    // 是否是IP段
                    if (strpos($val, '*')) {
                        if (strpos(request()->ip(), str_replace('.*', '', $val)) !== false) {
                            $this->error('403:你在IP禁止段内,禁止访问！');
                        }
                    } else {
                        // 不是IP段,用绝对匹配
                        if (request()->ip() == $val) {
                            $this->error('403:IP地址绝对匹配,禁止访问！');
                        }

                    }
                }
            }
            
            if (false == $this->competence()) {
                // 跳转到登录界面
                $this->error('请先登陆', $this->loginUrl);
            } else {
                // 是否超级管理员
                if (!$adminIsRoot) {
                    $noNeedAuthRules = (new AuthRuleModel())->getNoNeedAuthRuleList();
                    if (!in_array($rule, $noNeedAuthRules)) {
                        // 检测访问权限
                        if (!$this->checkRule($rule, [1, 2])) {
                            $this->error('未授权访问!');
                        }
                    }
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
        $adminId = AdminService::instance()->isLogin();
        if (empty($adminId)) {
            return false;
        }
        
        // 获取当前登录用户信息
        $adminInfo = AdminService::instance()->getInfo();
        if (empty($adminInfo)) {
            AdminService::instance()->logout();
            return false;
        }
        
        // 是否锁定
        if (!$adminInfo['status']) {
            AdminService::instance()->logout();
            $this->error('您的帐号已经被锁定！', $this->loginUrl);
            return false;
        }
        
        Env::set('admin_info', $adminInfo);
        
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
        static $Auth = null;
        if (!$Auth) {
            $Auth = new AuthService();
        }
        if (!$Auth->check($rule, Env::get('admin_id'), $type, $mode, $relation)) {
            return false;
        }
        return true;
    }
}
