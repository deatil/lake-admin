<?php

namespace Lake\Admin\Validate;

use think\Validate;

/**
 * 字段类型表验证器
 *
 * @create 2019-7-10
 * @author deatil
 */
class FieldType extends Validate
{
    //定义验证规则
    protected $rule = [
        'name|字段类型' => 'require|chsAlphaNum',
        'title|中文类型名' => 'require|chsAlphaNum',
    ];

    //定义验证提示
    protected $message = [
    ];

    //定义验证场景
    protected $scene = [
        'insert' => ['name', 'title'],
        'update' => ['name', 'title'],
    ];
}
