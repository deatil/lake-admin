<?php

namespace Lake\Admin\Service;

use Lake\Admin\Model\Config as ConfigModel;

/**
 * 配置
 *
 * @create 2020-7-24
 * @author deatil
 */
class Config
{
    /**
     * 更新配置
     *
     * @create 2020-7-24
     * @author deatil
     */
    public function updateValue($name, $value)
    {
        if (empty($name)) {
            return false;
        }
        
        return ConfigModel::where([
            'name' => $name,
        ])->data([
            'value' => $value,
        ])->update();
    }
}
