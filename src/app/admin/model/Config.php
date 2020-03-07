<?php

namespace app\admin\model;

use think\Model;

/**
 * 后台配置模型
 *
 * @create 2019-7-9
 * @author deatil
 */
class Config extends Model
{
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

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
                $value['options'] = parse_attr($value['options']);
            }
            switch ($value['type']) {
                case 'array':
                    $newConfigs[$key] = parse_attr($value['value']);
                    break;
                case 'radio':
                    $newConfigs[$key] = isset($value['options'][$value['value']]) ? ['key' => $value['value'], 'value' => $value['options'][$value['value']]] : ['key' => $value['value'], 'value' => $value['value']];
                    break;
                case 'select':
                    $newConfigs[$key] = isset($value['options'][$value['value']]) ? ['key' => $value['value'], 'value' => $value['options'][$value['value']]] : ['key' => $value['value'], 'value' => $value['value']];
                    break;
                case 'checkbox':
                    if (empty($value['value'])) {
                        $newConfigs[$key] = [];
                    } else {
                        $valueArr = explode(',', $value['value']);
                        foreach ($valueArr as $v) {
                            if (isset($value['options'][$v])) {
                                $newConfigs[$key][$v] = $value['options'][$v];
                            } elseif ($v) {
                                $newConfigs[$key][$v] = $v;
                            }
                        }
                    }
                    break;
                case 'image':
                    $newConfigs[$key] = empty($value['value']) ? '' : get_file_path($value['value']);
                    break;
                case 'images':
                    if (!empty($value['value'])) {
                        $images_values = explode(',', $value['value']);
                        foreach ($value['value'] as $val) {
                            $newConfigs[$key][] = get_file_path($val);
                        }
                    } else {
                        $newConfigs[$key] = [];
                    }
                    break;
                case 'Ueditor':
                    $newConfigs[$key] = htmlspecialchars_decode($value['value']);
                    break;
                default:
                    $newConfigs[$key] = $value['value'];
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
