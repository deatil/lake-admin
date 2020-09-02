<?php

namespace Lake\Admin\Model;

/**
 * 后台配置模型
 *
 * @create 2019-7-9
 * @author deatil
 */
class Config extends ModelBase
{
    // 设置当前模型对应的数据表名称
    protected $name = 'lakeadmin_config';
    
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
     * 字段类型
     *
     * @create 2020-8-19
     * @author deatil
     */
    public function fieldType()
    {
        return $this->hasOne(FieldType::class, 'name', 'type');
    }

    /**
     * 获取配置信息
     * @return mixed
     *
     * @create 2019-8-4
     * @author deatil
     */
    public static function getConfig(
        $where = "status='1'", 
        $fields = 'name,value,type,options', 
        $order = 'listorder,id desc'
    ) {
        $configs = self::where($where)->order($order)->column($fields);
        $newConfigs = [];
        foreach ($configs as $key => $value) {
            if ($value['options'] != '') {
                $value['options'] = json_decode($value['options'], true);
            }
            switch ($value['type']) {
                case 'array':
                    $newConfigs[$value['name']] = json_decode($value['value'], true);
                    break;
                case 'radio':
                    $newConfigs[$value['name']] = isset($value['options'][$value['value']]) ? ['key' => $value['value'], 'value' => $value['options'][$value['value']]] : ['key' => $value['value'], 'value' => $value['value']];
                    break;
                case 'select':
                    $newConfigs[$value['name']] = isset($value['options'][$value['value']]) ? ['key' => $value['value'], 'value' => $value['options'][$value['value']]] : ['key' => $value['value'], 'value' => $value['value']];
                    break;
                case 'checkbox':
                    if (empty($value['value'])) {
                        $newConfigs[$value['name']] = [];
                    } else {
                        $valueArr = explode(',', $value['value']);
                        foreach ($valueArr as $v) {
                            if (isset($value['options'][$v])) {
                                $newConfigs[$value['name']][$v] = $value['options'][$v];
                            } elseif ($v) {
                                $newConfigs[$value['name']][$v] = $v;
                            }
                        }
                    }
                    break;
                case 'image':
                    $newConfigs[$value['name']] = empty($value['value']) ? '' : lake_get_file_path($value['value']);
                    break;
                case 'images':
                    if (!empty($value['value'])) {
                        $images_values = explode(',', $value['value']);
                        foreach ($value['value'] as $val) {
                            $newConfigs[$value['name']][] = lake_get_file_path($val);
                        }
                    } else {
                        $newConfigs[$value['name']] = [];
                    }
                    break;
                case 'Ueditor':
                    $newConfigs[$value['name']] = htmlspecialchars_decode($value['value']);
                    break;
                default:
                    $newConfigs[$value['name']] = $value['value'];
                    break;
            }
        }
        return $newConfigs;
    }
    
    /**
     * 获取配置列表
     * @return array
     *
     * @create 2019-8-4
     * @author deatil
     */
    public function getConfigList()
    {
        $data = cache('lake_admin_config');
        if (!$data) {
            $data = $this->getConfig();
            cache("lake_admin_config", $data);
        }
        
        return $data;
    }

}
