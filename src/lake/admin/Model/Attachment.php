<?php

namespace Lake\Admin\Model;

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
    
    // 时间字段取出后的默认时间格式
    protected $dateFormat = false;
    
    protected $insert = [
        'status' => 1,
    ];

    public static function onBeforeInsert($model)
    {
        $id = md5(mt_rand(10000, 99999) . time() . mt_rand(10000, 99999));
        $model->setAttr('id', $id);
    }

    public function getSizeAttr($value)
    {
        return lake_format_bytes($value);
    }
}

