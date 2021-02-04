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