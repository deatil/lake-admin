<?php

namespace app\admin\model;

use think\Model;

/**
 * 字段类型模型
 *
 * @create 2019-8-5
 * @author deatil
 */
class FieldType extends Model
{

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
