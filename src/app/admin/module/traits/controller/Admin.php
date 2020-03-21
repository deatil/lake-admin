<?php

namespace app\admin\module\traits\controller;

use think\Db;
use think\facade\Env;

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
            $viewPath = $this->moduleViewPath;
        } else {
            $appPath = config('module_path');
            
            if (strpos($template, '@') !== FALSE) {
                $templates = explode('@', $template);
                $module = $templates[0];
                $template = $templates[1];
            } else {
                $module = $this->module;
            }
            
            $modulePath = $appPath . $module;
            
            // 模块信息
            if (!empty($module)) {
                $module_info = Db::name('module')->where([
                    'module' => $module,
                    'status' => 1,
                ])->find();
                if (!empty($module_info) && !empty($module_info['path'])) {
                    $modulePath = $module_info['path'];
                }
            }
            
            // 保存模块名称
            Env::set([
                'module_name' => $module,
            ]);
        
            $viewPath = $modulePath . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR;
        }
        
        $this->view->config('view_path', $viewPath);
        return $this->view->fetch($template, $vars, $config);
    }

}
