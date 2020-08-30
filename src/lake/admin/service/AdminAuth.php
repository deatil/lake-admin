<?php

namespace lake\admin\service;

use think\facade\Env;

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
        $Auth = app()->auth;
        $Auth->withRuleType($ruleType);
        
        $checkStatus = $Auth->check($rule, Env::get('admin_id'), $relation, $type, $mode);
        if (false === $checkStatus) {
            return false;
        }
        
        return true;
    }

}
