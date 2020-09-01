<?php

namespace Lake\Admin\Service;

use think\facade\Env;
use think\facade\Config;

use Lake\Admin\Model\Config as ConfigModel;

/**
 * 初始化配置信息
 * 将系统配置信息合并到本地配置
 *
 * @create 2019-7-6
 * @author deatil
 */
class ConfigInit
{

    /**
     * 执行入口
     * @access public
     * @param mixed $params  行为参数
     * @return void
     *
     * @create 2019-7-6
     * @author deatil
     */
    public function handle()
    {
        // 定义系统配置信息
        $this->setAppConfig();
        
        // 定义系统相关信息
        $this->setAppEnv();
    }
    
    /**
     * 定义系统配置信息
     *
     * @create 2019-7-6
     * @author deatil
     */
    private function setAppConfig()
    {
        // 读取系统配置
        $systemConfig = (new ConfigModel)->getConfigList();
        
        // 设置配置信息
        if (!empty($systemConfig)) {
            foreach ($systemConfig as $key => $value) {
                Config::set([
                    $key => $value,
                ], 'app');
            }
        }
    }
    
    /**
     * 定义系统相关信息
     *
     * @create 2019-7-6
     * @author deatil
     */
    private function setAppEnv()
    {
        $lakeModulePath = config('app.module_path');
        $rootUrl = rtrim(dirname($_SERVER["SCRIPT_NAME"]), '\\/') . '/';
    
        Env::set([
            'lake_module_path' => $lakeModulePath,
            'root_url' => $rootUrl,
        ]);
    }

}
