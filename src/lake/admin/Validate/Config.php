<?php

namespace Lake\Admin\Validate;

use think\Validate;

/**
 * 配置验证器
 *
 * @create 2019-7-9
 * @author deatil
 */
class Config extends Validate
{
    // 定义验证规则
    protected $rule = [
        'module|所属模块' => 'require|regex:^[a-zA-Z_]\w{0,39}$',
        'group|配置分组' => 'require',
        'type|配置类型' => 'require|alpha',
        'title|配置标题' => 'require|chsAlphaNum',
        'name|配置名称' => 'require|regex:^[a-zA-Z]\w{0,39}$|unique:lakeadmin_config',
        'listorder|排序' => 'number',
    ];
}
