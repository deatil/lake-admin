<?php

namespace app\admin\listener;

use think\facade\Env;
use think\facade\Config;

use app\admin\model\Config as ConfigModel;

/**
 * 初始化配置信息
 * 将系统配置信息合并到本地配置
 *
 * @create 2019-7-6
 * @author deatil
 */
class InitConfig
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
        $system_config = (new ConfigModel)->getConfigList();
        
        // 设置配置信息
        if (!empty($system_config)) {
            foreach ($system_config as $key => $value) {
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
        $lake_module_path = config('app.module_path');
        $root_url = rtrim(dirname($_SERVER["SCRIPT_NAME"]), '\\/') . '/';
    
        Env::set([
            'lake_module_path' => $lake_module_path,
            'root_url' => $root_url,
        ]);
    }

}
