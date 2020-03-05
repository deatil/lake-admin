<?php

namespace app\admin\behavior;

/**
 * 后台控制台设置
 *
 * @create 2020-1-6
 * @author deatil
 */
class AdminMainUrl
{
	
    /**
	 * 行为扩展的执行入口必须是run
	 *
	 * @create 2020-1-6
	 * @author deatil
	 */
    public function run($params)
    {
		// 首页链接
		$main_url = config('admin_main');
		
		if (empty($main_url)) {
			$main_url = $params;
		} else {
			$main_url = url($main_url);
		}
		
		return $main_url;
    }
	
}
