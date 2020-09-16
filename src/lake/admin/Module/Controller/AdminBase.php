<?php

namespace Lake\Admin\Module\Controller;

use think\facade\Env;

use Lake\Admin\Controller\Base;
use Lake\Admin\Facade\Module as ModuleFacade;

use Lake\Admin\Model\Module as ModuleModel;

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
    protected $viewPath = '';
    
    protected function initialize()
    {
        parent::initialize();
        
        // 设置模版路径
        $this->viewPath();
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
    
    /**
     * 设置模块名称
     *
     * @create 2020-9-13
     * @author deatil
     */
    protected function withModule($module)
    {
        $this->module = $module;
        return $this;
    }
    
    /**
     * 设置模块模版
     *
     * @create 2020-9-13
     * @author deatil
     */
    protected function withViewPath($viewPath)
    {
        $this->viewPath = $viewPath;
        return $this;
    }
    
    /**
     * 重写获取模版方法
     *
     * @create 2020-4-10
     * @author deatil
     */
    protected function viewPath()
    {
        if (!empty($this->viewPath)) {
            $viewPath = $this->viewPath;
        } else {
            $appPath = config('app.module_path');
            
            $module = $this->module;
            
            $modulePath = rtrim($appPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $module;
            
            // 模块信息
            if (!empty($module)) {
                $moduleInfo = ModuleModel::where([
                    'module' => $module,
                    'status' => 1,
                ])->find();
                if (!empty($moduleInfo) && !empty($moduleInfo['path'])) {
                    $modulePath = ModuleFacade::getModuleRealPath($moduleInfo['path']);
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

}
