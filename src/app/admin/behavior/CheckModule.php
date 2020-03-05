<?php

namespace app\admin\behavior;

use app\admin\module\Module as ModuleService;

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
		
		$moduleService = new ModuleService();
		$check = $moduleService->checkModule($module);
		
		if ($check === false) {
			$error = $moduleService->getError();
			abort(404, $error);
		}
    }

}
