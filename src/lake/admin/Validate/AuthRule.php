<?php

namespace Lake\Admin\Validate;

use think\Validate;

/**
 * 规则验证器
 *
 * @create 2019-7-9
 * @author deatil
 */
class AuthRule extends Validate
{
    //定义验证规则
    protected $rule = [
        'parentid|上级菜单' => 'require|alphaNum',
        'title|名称' => 'require|chsAlphaNum',
    ];

    //定义验证提示
    protected $message = [
    ];

    //定义验证场景
    protected $scene = [
        'insert' => ['parentid', 'title'],
        'update' => ['parentid', 'title'],
    ];
}
