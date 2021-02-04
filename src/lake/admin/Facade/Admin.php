<?php

declare (strict_types = 1);

namespace Lake\Admin\Facade;

use think\Facade;

use Lake\Admin\Service\Admin as AdminService;

/**
 * 管理员
 *
 * @create 2020-7-28
 * @author deatil
 */
class Admin extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return AdminService::class;
    }
}
