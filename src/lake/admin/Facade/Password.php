<?php

declare (strict_types = 1);

namespace Lake\Admin\Facade;

use think\Facade;

use Lake\Admin\Service\Password as PasswordService;

/**
 * 密码
 *
 * @create 2020-7-22
 * @author deatil
 */
class Password extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return PasswordService::class;
    }
}
