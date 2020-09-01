<?php

namespace Lake\Admin\Model;

use think\model\Pivot;

/**
 * 用户组授权
 *
 * @create 2020-7-25
 * @author deatil
 */
class AuthGroupAccess extends Pivot
{
    // 设置当前模型对应的数据表名称
    protected $name = 'lakeadmin_auth_group_access';
    
    // 时间字段取出后的默认时间格式
    protected $dateFormat = false;

}
