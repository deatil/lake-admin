<?php

namespace Lake\Admin\Validate;

use think\Validate;

/**
 * 扩展权限验证器
 *
 * @create 2019-7-10
 * @author deatil
 */
class RuleExtend extends Validate
{
    //定义验证规则
    protected $rule = [
        'module|模块' => 'require|chsAlphaNum',
        'group_id|角色' => 'require|alphaDash',
        'rule|扩展规则' => 'require',
    ];

    //定义验证提示
    protected $message = [
    ];

    //定义验证场景
    protected $scene = [
        'insert' => ['module', 'group_id', 'rule'],
        'update' => ['rule'],
    ];
}
