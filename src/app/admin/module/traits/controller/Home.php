<?php

namespace app\admin\module\traits\controller;

/**
 * 前台
 *
 * @create 2019-7-15
 * @author deatil
 */
trait Home
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
			
			$view_path = $app_path . $module . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR;
        }
		
		$this->view->config('view_path', $view_path);
        return $this->view->fetch($template, $vars, $config);
    }

}
