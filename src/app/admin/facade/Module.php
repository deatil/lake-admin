<?php

declare (strict_types = 1);

namespace app\admin\facade;

use think\Facade;

use app\admin\module\Module as ModuleModule;

/**
 * 模块管理
 *
 * @create 2020-7-19
 * @author deatil
 */
class Module extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return ModuleModule::class;
    }
}
