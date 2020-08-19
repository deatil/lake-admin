<?php

namespace app\admin\model;

use think\model\Pivot;

/**
 * 后台菜单授权
 *
 * @create 2020-7-25
 * @author deatil
 */
class AuthRuleAccess extends Pivot
{
    // 设置当前模型对应的数据表名称
    protected $name = 'lakeadmin_auth_rule_access';

}
