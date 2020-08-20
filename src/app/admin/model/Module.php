<?php

namespace app\admin\model;

/**
 * 模块模型
 *
 * @create 2019-7-9
 * @author deatil
 */
class Module extends ModelBase
{
    // 设置当前模型对应的数据表名称
    protected $name = 'lakeadmin_module';
    
    // 设置主键名
    protected $pk = 'module';
    
    // 时间字段取出后的默认时间格式
    protected $dateFormat = false;
    
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

}
