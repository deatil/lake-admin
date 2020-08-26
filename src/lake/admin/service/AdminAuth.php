<?php

namespace lake\admin\service;

use think\facade\Env;

use lake\Str;

use lake\admin\Model\Admin as AdminModel;
use lake\admin\model\AuthGroup as AuthGroupModel;
use lake\admin\model\AuthGroupAccess as AuthGroupAccessModel;
use lake\admin\Model\AuthRule as AuthRuleModel;
use lake\admin\model\AuthRuleAccess as AuthRuleAccessModel;
use lake\admin\model\AuthRuleExtend as AuthRuleExtendModel;
use lake\admin\auth\Auth as AuthService;

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
    public static function instance($userConfig = [])
    {
        static $authList = [];
        
        $config = [
            'AUTH_ON' => true, // 认证开关
            'AUTH_TYPE' => 1, // 认证方式，1为实时认证；2为登录认证。
            'AUTH_ACCESS_FIELD' => 'admin_id', // admin_id
            'AUTH_GROUP' => (new AuthGroupModel)->getName(), // 用户组数据表名
            'AUTH_GROUP_ACCESS' => (new AuthGroupAccessModel)->getName(),
            'AUTH_RULE' => (new AuthRuleModel)->getName(), // 权限规则表
            'AUTH_RULE_ACCESS' => (new AuthRuleAccessModel)->getName(), // 权限规则关系表
            'AUTH_RULE_EXTEND' => (new AuthRuleExtendModel)->getName(), // 扩展表
        ];
        if (!empty($userConfig) && is_array($userConfig)) {
            $config = array_merge($config, $userConfig);
        }
        
        $authId = Str::toGuidString($config);
        if (isset($authList[$authId])) {
            return $authList[$authId];
        }
        
        $authList[$authId] = new AuthService($config);
        return $authList[$authId];
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
