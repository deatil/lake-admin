<?php

namespace lake\admin\service;

use think\facade\Env;

use lake\admin\Model\Admin as AdminModel;
use lake\admin\model\AuthGroup as AuthGroupModel;
use lake\admin\model\AuthGroupAccess as AuthGroupAccessModel;
use lake\admin\Model\AuthRule as AuthRuleModel;
use lake\admin\model\AuthRuleAccess as AuthRuleAccessModel;
use lake\admin\model\AuthRuleExtend as AuthRuleExtendModel;
use lake\admin\auth\Permission as PermissionAuth;

/**
 * 管理员验证
 *
 * @create 2020-7-25
 * @author deatil
 */
class AdminAuth
{
    
    /**
     * 权限检测
     *
     * @create 2020-7-25
     * @author deatil
     */
    public static function instance()
    {
        static $auth = null;
        if (isset($auth)) {
            return $auth;
        }
        
        $auth = new PermissionAuth();
        return $auth;
    }
    
    /**
     * 权限检测
     * @param string  $rule    检测的规则
     * @param string  $mode    check模式
     * @return boolean
     *
     * @create 2020-7-25
     * @author deatil
     */
    public static function checkRule($rule, $type = [1, 2], $mode = 'url', $relation = 'or')
    {
        $config = config('app.auth');
        $Auth = static::instance($config);
        
        if (!$Auth->check($rule, Env::get('admin_id'), $relation, $type, $mode)) {
            return false;
        }
        return true;
    }

}
