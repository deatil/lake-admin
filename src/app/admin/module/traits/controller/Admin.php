<?php

namespace app\admin\module\traits\controller;

use think\Db;

/**
 * 插件后台
 *
 * @create 2019-7-15
 * @author deatil
 */
trait Admin
{
	// 模块ID
	protected $module = 'admin';
	
	// 设置模板路径
	protected $moduleViewPath = '';

	/**
	 * 重写获取模版方法
	 *
	 * @create 2019-7-15
	 * @author deatil
	 */
    protected function fetch($template = '', $vars = [], $config = [])
    {
		if (!empty($this->moduleViewPath)) {
			$view_path = $this->moduleViewPath;
		} else {
			$app_path = config('module_path');
			
			if (strpos($template, '@') !== FALSE) {
				$templates = explode('@', $template);
				$module = $templates[0];
				$template = $templates[1];
			} else {
				$module = $this->module;
			}
			
			$module_path = $app_path . $module;
			
			// 模块信息
			if (!empty($module)) {
				$module_info = Db::name('module')->where([
					'module' => $module,
					'status' => 1,
				])->find();
				if (!empty($module_info) && !empty($module_info['path'])) {
					$module_path = $module_info['path'];
				}
			}
		
			$view_path = $module_path . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR;
		}
		
        $this->view->config('view_path', $view_path);
        return $this->view->fetch($template, $vars, $config);
    }

}
