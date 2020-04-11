<?php

namespace app\admin\model;

use think\Model;

/**
 * 模块模型
 *
 * @create 2019-7-9
 * @author deatil
 */
class Module extends Model
{
    // 自动完成
    protected $auto = [];
    
    // 添加时候
    protected $insert = [
        'installtime', 
        'updatetime', 
        'status' => 1,
    ];
    
    protected function setInstalltimeAttr($value)
    {
        return time();
    }
    
    protected function setUpdatetimeAttr($value)
    {
        return time();
    }

    /**
     * 更新缓存
     * @return type
     */
    public function getModuleList()
    {
        $module = cache('lake_admin_module_list');
        if (!$module) {
            $module = [];
            
            $data = $this->column('*', 'module');
            if (!empty($data)) {
                foreach ($data as &$v) {
                    to_time($v, 'installtime');
                    $module[$v['module']] = $v;
                }
                
                unset($v);
            }
            
            cache('lake_admin_module_list', $module);
        }
        
        return $module;
    }

}
