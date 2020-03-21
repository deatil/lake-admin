<?php

namespace app\admin\behavior;

use app\admin\module\Module as ModuleModule;

/**
 * 检测模块
 *
 * @create 2019-7-14
 * @author deatil
 */
class CheckModule
{
    /**
     * 行为扩展执行入口
     *
     * @create 2019-7-6
     * @author deatil
     */
    public function run($params)
    {        
        $module = request()->module();
        
        $ModuleModule = new ModuleModule();
        $check = $ModuleModule->checkModule($module);
        
        if ($check === false) {
            $error = $ModuleModule->getError();
            abort(404, $error);
        }
    }

}
