<?php

namespace Lake\Admin\Module;

use Lake\Symlink;

/**
 * Statics
 *
 * @create 2020-9-15
 * @author deatil
 */
class Statics
{
    // 模块所处目录路径
    protected $modulePath;
    
    // 静态资源目录
    protected $staticPath = null;
    
    /**
     * 设置模块目录
     */
    public function withModulePath($modulePath)
    {
        $this->modulePath = $modulePath;
        return $this;
    }
    
    /**
     * 设置资源目录
     */
    public function withStaticPath($staticPath)
    {
        $this->staticPath = $staticPath;
        return $this;
    }
    
    /**
     * 添加静态文件
     * @param string $name 模块名称
     * @param string $fromPath [选填]模块静态文件夹
     * @return boolean
     *
     * @create 2019-8-12
     * @author deatil
     */
    public function install($name = '', $fromPath = '')
    {
        if (empty($name)) {
            return false;
        }
        
        $name = strtolower($name);
        
        // 静态资源文件软链接
        if (empty($fromPath)) {
            $fromPath = $this->modulePath 
                . $name . DIRECTORY_SEPARATOR 
                . "static" . DIRECTORY_SEPARATOR;
        }
        
        $toPath = $this->staticPath . DIRECTORY_SEPARATOR 
            . $name . DIRECTORY_SEPARATOR;
        
        // 创建静态资源文件软链接
        $status = Symlink::make($fromPath, $toPath);
        if ($status === false) {
            return false;
        }
        
        return true;
    }

    /**
     * 删除静态文件
     *
     * @create 2019-8-12
     * @author deatil
     */    
    public function uninstall($name = '')
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        // 移除静态资源软链接
        $moduleStatic = $this->staticPath . DIRECTORY_SEPARATOR
            . strtolower($name) . DIRECTORY_SEPARATOR;
        if (file_exists($moduleStatic)) {
            Symlink::remove($moduleStatic);
        }
        
        return true;
    }
    
}