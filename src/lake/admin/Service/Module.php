<?php

namespace Lake\Admin\Service;

use Lake\Admin\Model\Module as ModuleModel;

/**
 * 模块模型
 *
 * @create 2020-7-24
 * @author deatil
 */
class Module
{
    
    /**
     * 获取模块列表
     * @return type
     *
     * @create 2020-7-24
     * @author deatil
     */
    public function getList()
    {
        $module = cache('lake_admin_module_list');
        if (!$module) {
            $module = [];
            
            $data = ModuleModel::column('*', 'module');
            if (!empty($data)) {
                foreach ($data as &$v) {
                    lake_to_time($v, 'installtime');
                    $module[$v['module']] = $v;
                }
                
                unset($v);
            }
            
            cache('lake_admin_module_list', $module);
        }
        
        return $module;
    }
    
    /**
     * 检查模块是否已经安装
     * @param type $moduleName 模块名称
     * @return boolean
     *
     * @create 2020-7-24
     * @author deatil
     */
    public function isInstall($name)
    {
        if (empty($name)) {
            return false;
        }
        
        $name = ModuleModel::where([
                'module' => $name,
            ])
            ->field('name')
            ->find();
        if (empty($name)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 获取模块的配置值
     * @param string $name 模块名
     * @return array
     *
     * @create 2020-7-24
     * @author deatil
     */
    public function getConfig($name)
    {
        static $_config = [];
        
        if (empty($name)) {
            return [];
        }
        if (isset($_config[$name])) {
            return $_config[$name];
        }    

        $setting = ModuleModel::where([
                'module' => $name,
                'status' => 1,
            ])
            ->field('setting, setting_data')
            ->find();
        if (empty($setting)) {
            return [];
        }
        
        $config = [];
        if (!empty($setting['setting_data'])) {
            $config = json_decode($setting['setting_data'], true);
        } elseif (!empty($setting['setting'])) {
            $temp_arr = json_decode($setting['setting'], true);
            foreach ($temp_arr as $key => $value) {
                if ($value['type'] == 'group') {
                    foreach ($value['options'] as $gkey => $gvalue) {
                        foreach ($gvalue['options'] as $ikey => $ivalue) {
                            $config[$ikey] = $ivalue['value'];
                        }
                    }
                } else {
                    $config[$key] = $temp_arr[$key]['value'];
                }
            }
        }
        
        $_config[$name] = $config;
        
        return $config;
    }
    
    /**
     * 获取模块的路径
     * @param string $name 模块名
     * @return string
     *
     * @create 2020-7-24
     * @author deatil
     */
    public function getPath($name)
    {
        static $modules = [];
        
        if (empty($name)) {
            return '';
        }
        if (isset($modules[$name])) {
            return $modules[$name];
        }
        
        $module = ModuleModel::where([
            'module' => $name,
            'status' => 1,
        ])
        ->field('path')
        ->find();
        if (empty($module)) {
            return '';
        }
        
        $modules[$name] = $module;
        return $module;
    }

}
