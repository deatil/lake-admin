<?php

namespace Lake\Admin\Module\Data;

use Lake\Admin\Model\Module as ModuleModel;

/**
 * 模块数据
 *
 * @create 2020-9-15
 * @author deatil
 */
class Module
{

    /**
     * 安装模块数据
     * @param type $name 模块名称
     * @param type $config 模块信息
     * @return boolean
     *
     * @create 2019-8-5
     * @author deatil
     */
    private function installConfig($name = '', $config = [])
    {
        if (empty($config['name']) 
            || empty($config['version']) 
            || empty($config['sign'])
        ) {
            $this->error = '模块信息错误！';
            return false;
        }
        
        $modulePath = (isset($config['path']) && !empty($config['path'])) 
            ? $config['path'] 
            : $this->modulePath . $name;
        $modulePath = $this->getModulePath($modulePath);
        
        $data = [
            'module' => $name,
            'name' => $config['name'],
            'introduce' => isset($config['introduce']) ? $config['introduce'] : '',
            'author' => isset($config['author']) ? $config['author'] : '',
            'authorsite' => isset($config['authorsite']) ? $config['authorsite'] : '',
            'authoremail' => isset($config['authoremail']) ? $config['authoremail'] : '',
            'version' => $config['version'],
            'adaptation' => isset($config['adaptation']) ? $config['adaptation'] : '',
            'path' => $modulePath,
            'sign' => $config['sign'],
            
            'need_module' => (isset($config['need_module']) && !empty($config['need_module'])) ? json_encode($config['need_module']) : '',
            'setting' => (isset($config['setting']) && !empty($config['setting'])) ?  json_encode($config['setting']) : '',
            'setting_data' => (isset($config['setting_data']) && !empty($config['setting_data'])) ?  json_encode($config['setting_data']) : '',
            'listorder' => (isset($config['listorder']) && !empty($config['listorder'])) ? intval($config['listorder']) : 100,
        
            'installtime' => time(),
            'status' => 1,
        ];
        
        // 保存在安装表
        if (!ModuleModel::create($data, [], true)) {
            return false;
        }

        return true;
    }

    /**
     * 更新模块数据
     * @param type $name 模块名称
     * @param type $config 模块信息
     * @return boolean
     *
     * @create 2019-8-5
     * @author deatil
     */
    private function upgradeConfig($name = '', $config = [])
    {
        if (empty($config['name']) 
            || empty($config['version']) 
            || empty($config['sign'])
        ) {
            $this->error = '模块信息错误！';
            return false;
        }
        
        $modulePath = (isset($config['path']) && !empty($config['path'])) 
            ? $config['path'] 
            : $this->modulePath . $name;
        $modulePath = $this->getModulePath($modulePath);
        
        $data = [
            'name' => $config['name'],
            'introduce' => isset($config['introduce']) ? $config['introduce'] : '',
            'author' => isset($config['author']) ? $config['author'] : '',
            'authorsite' => isset($config['authorsite']) ? $config['authorsite'] : '',
            'authoremail' => isset($config['authoremail']) ? $config['authoremail'] : '',
            'version' => $config['version'],
            'adaptation' => isset($config['adaptation']) ? $config['adaptation'] : '',
            'path' => $modulePath,
            'sign' => $config['sign'],
            
            'need_module' => isset($config['need_module']) ? json_encode($config['need_module']) : '',
            'setting' => isset($config['setting']) ?  json_encode($config['setting']) : '',
            'need_module' => isset($config['need_module']) ? json_encode($config['need_module']) : '',
        
            'update_time' => time(),
            'update_ip' => request()->ip(1),
        ];
        
        $status = ModuleModel::where([
            'module' => $name,
        ])->data($data)->update();
        if ($status === false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 删除
     */
    public static function delete($name = '')
    {
        if (empty($name)) {
            return false;
        }
        
        $status = ModuleModel::where([
            'module' => $name,
        ])->delete();
        if ($status === false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 启用
     */
    public static function enable($name = '')
    {
        if (empty($name)) {
            return false;
        }
        
        $status = ModuleModel::where([
            'module' => $name,
        ])->update([
            'status' => 1,
        ]);
        if ($status === false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 禁用
     */
    public static function disable($name = '')
    {
        if (empty($name)) {
            return false;
        }
        
        $status = ModuleModel::where([
            'module' => $name,
        ])->update([
            'status' => 0,
        ]);
        if ($status === false) {
            return false;
        }
        
        return true;
    }
    
}