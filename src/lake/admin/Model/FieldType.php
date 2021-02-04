<?php

namespace Lake\Admin\Model;

/**
 * 字段类型模型
 *
 * @create 2019-8-5
 * @author deatil
 */
class FieldType extends ModelBase
{
    // 设置当前模型对应的数据表名称
    protected $name = 'lakeadmin_field_type';
    
    // 设置主键名
    protected $pk = 'id';
    
    // 时间字段取出后的默认时间格式
    protected $dateFormat = false;

    public static function onBeforeInsert($model)
    {
        $id = md5(mt_rand(10000, 99999) . time() . mt_rand(10000, 99999) . microtime());
        $model->setAttr('id', $id);
        
        $model->setAttr('add_time', time());
        $model->setAttr('add_ip', request()->ip());
    }
    
    /**
     * 字段类型的配置列表
     *
     * @create 2020-8-19
     * @author deatil
     */
    public function configs()
    {
        return $this->hasMany(Config::class, 'type', 'name');
    }

    /**
     * 获取字段类型列表
     *
     * @create 2019-11-7
     * @author deatil
     */
    public function getFieldTypeList()
    {
        $types = cache('lake_admin_field_type');
        if (!$types) {
            $types = [];
            
            $data = $this->select()->toArray();
            if (!empty($data)) {
                foreach ($data as $rs) {
                    $types[$rs['name']] = $rs['type'];
                }
            }
            
            cache('lake_admin_field_type', $types);
        }
        
        return $types;
    }

}
