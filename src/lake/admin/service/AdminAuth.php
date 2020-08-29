<?php

namespace lake\admin\service;

use think\facade\Env;

use lake\admin\auth\Permission;

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
     * @param string  $rule    检测的规则
     * @param string  $mode    check模式
     * @return boolean
     *
     * @create 2020-7-25
     * @author deatil
     */
    public static function checkRule(
        $rule, 
        $type = [1, 2], 
        $mode = 'url', 
        $relation = 'or'
    ) {
        $ruleType = config('auth.rule_type', 1);
        $Auth = new Permission();
        $Auth->withRuleType($ruleType);
        
        if (!$Auth->check($rule, Env::get('admin_id'), $relation, $type, $mode)) {
            return false;
        }
        
        return true;
    }

}
