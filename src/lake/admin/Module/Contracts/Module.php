<?php

namespace Lake\Admin\Module\Contracts;

/**
 * 模型接口
 *
 * @create 2020-9-15
 * @author deatil
 */
interface Module
{
    /**
     * 安装
     */
    public function install($name = '');
    
    /**
     * 卸载
     */
    public function uninstall($name = '');
    
    /**
     * 更新
     */
    public function upgrade($name = '');
    
    /**
     * 启用
     */
    public function enable($name = '');
    
    /**
     * 禁用
     */
    public function disable($name = '');
}