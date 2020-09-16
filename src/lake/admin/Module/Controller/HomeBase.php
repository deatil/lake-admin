<?php

namespace Lake\Admin\Module\Controller;

use think\facade\Env;

use Lake\Admin\Http\Traits\Jump as JumpTrait;
use Lake\Admin\Http\Traits\View as ViewTrait;
use Lake\Admin\Http\BaseController;

use Lake\Admin\Model\Module as ModuleModel;

/**
 * 插件前台基础类
 *
 * @create 2020-4-10
 * @author deatil
 */
class HomeBase extends BaseController
{    
    use JumpTrait;
    use ViewTrait;
    
    // 设置模板路径
    protected $viewPath = '';
    
    // 初始化
    protected function initialize()
    {
        parent::initialize();
        
        // 设置模版路径
        $this->viewPath();
    }
    
    // 空操作
    public function _empty()
    {
        $this->error('该页面不存在！');
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
            
            $module = $this->app->http->getName();

            $modulePath = rtrim($appPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $module;
            
            // 模块信息
            if (!empty($module)) {
                $moduleInfo = ModuleModel::where([
                    'module' => $module,
                    'status' => 1,
                ])->find();
                if (!empty($moduleInfo) && !empty($moduleInfo['path'])) {
                    $modulePath = $moduleInfo['path'];
                }
            }
            
            // 保存模块名称
            Env::set([
                'module_name' => $module,
            ]);
        
            $viewPath = $modulePath . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR;
        }
        
        app('config')->set([
            'view_path' => $viewPath,
        ], 'view');
    }
}
