<?php

namespace Lake\Admin\Module\Data;

/**
 * 本地
 *
 * @create 2020-9-15
 * @author deatil
 */
class Local
{
    // 模块所处目录路径
    protected $modulePath;
    
    /**
     * 设置路径
     */
    public function withModulePath($modulePath)
    {
        $this->modulePath = $modulePath;
        return $this;
    }
    
    /**
     * 列表
     */
    public function getList()
    {
        $dirs = array_map('basename', glob($this->modulePath . '*', GLOB_ONLYDIR));
        if ($dirs === false || !file_exists($this->modulePath)) {
            return false;
        }
        
        return $dirs;
    }
}