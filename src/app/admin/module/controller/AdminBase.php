<?php

namespace app\admin\module\controller;

use think\facade\Db;
use think\facade\Env;

use app\admin\controller\Base;

/**
 * 插件后台
 *
 * @create 2019-7-4
 * @author deatil
 */
class AdminBase extends Base
{
    // 模块ID
    protected $module = 'admin';
    
    // 设置模板路径
    protected $moduleViewPath = '';
    
    protected function initialize()
    {
        parent::initialize();
        
        // 设置模版路径
        $this->viewPath();
    }
    
    /**
     * 重写获取模版方法
     *
     * @create 2020-4-10
     * @author deatil
     */
    protected function viewPath()
    {
        if (!empty($this->moduleViewPath)) {
            $viewPath = $this->moduleViewPath;
        } else {
            $appPath = config('app.module_path');
            
            $module = $this->module;
            
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
        
        app('config')->set([
            'view_path' => $viewPath,
        ], 'view');
    }
    
    /**
     * 空操作
     *
     * @create 2020-4-10
     * @author deatil
     */
    public function _empty()
    {
        $this->error('该页面不存在！');
    }

}
