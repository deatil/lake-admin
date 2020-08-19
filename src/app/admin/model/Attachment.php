<?php

namespace app\admin\model;

/**
 * 附件模型
 *
 * @create 2019-8-5
 * @author deatil
 */
class Attachment extends ModelBase
{
    // 设置当前模型对应的数据表名称
    protected $name = 'lakeadmin_attachment';
    
    // 设置主键名
    protected $pk = 'id';
    
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $insert = ['status' => 1];

    public function getSizeAttr($value)
    {
        return lake_format_bytes($value);
    }
}

