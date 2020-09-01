<?php

namespace Lake\Admin\Validate;

use \think\Validate;

/**
 * 用户组验证器
 *
 * @create 2019-7-9
 * @author deatil
 */
class AuthGroup extends Validate
{
    //定义验证规则
    protected $rule = [
        'title|用户组' => 'require|chsAlphaNum',
    ];

    //定义验证提示
    protected $message = [
        'title.require' => '用户组名称不得为空',

    ];

    //定义验证场景
    protected $scene = [

    ];
}
