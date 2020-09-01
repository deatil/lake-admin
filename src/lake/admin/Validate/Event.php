<?php

namespace Lake\Admin\Validate;

use think\Validate;

/**
 * 嵌入点验证器
 *
 * @create 2019-7-20
 * @author deatil
 */
class Event extends Validate
{
    //定义验证规则
    protected $rule = [
        'module|模型' => 'require|chsAlphaNum',
        'name|检测点名称' => 'require',
        'class|事件类' => 'require',
    ];

    //定义验证提示
    protected $message = [
    ];

    //定义验证场景
    protected $scene = [
        'insert' => ['module', 'name', 'class'],
        'update' => ['module', 'name', 'class'],
    ];
}
