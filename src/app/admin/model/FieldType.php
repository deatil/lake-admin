<?php

namespace app\admin\model;

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
