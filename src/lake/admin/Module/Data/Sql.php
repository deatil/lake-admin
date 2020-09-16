<?php

namespace Lake\Admin\Module\Data;

use Lake\Admin\Model\Module as ModuleModel;
use Lake\Admin\Service\Module as ModuleService;

/**
 * 数据库
 *
 * @create 2020-9-15
 * @author deatil
 */
class Sql
{
    /**
     * 已经安装模块列表
     */
    public function moduleList()
    {
        // 读取数据库已经安装模块表
        $list = (new ModuleService)->getList();
        
        return $list;
    }
    
    /**
     * 检查模块是否已经安装
     * @param type $moduleName 模块名称
     * @return boolean
     */
    public function isInstall($name)
    {
        return (new ModuleService)->isInstall($name);
    }
    
}