<?php

namespace Lake\Admin\Module;

use Lake\Admin\Model\AuthRule as AuthRuleModel;
use Lake\Admin\Model\AuthRuleAccess as AuthRuleAccessModel;

/**
 * 菜单
 *
 * @create 2020-9-1
 * @author deatil
 */
class Menu
{
    /**
     * 模块安装时进行菜单注册
     * @param array $data 菜单数据
     * @param array $config 模块配置
     * @param type $parentid 父菜单ID
     * @return boolean
     */
    public static function install($data, $config, $parentid = 0)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }
        
        if (empty($config) || !is_array($config)) {
            return false;
        }
        
        // 父级ID
        $menuParentid = AuthRuleModel::where([
            'name' => 'admin/modules/index', 
        ])->value('id');
        
        // 安装模块名称
        $moduleName = $config['module'];
        foreach ($data as $rs) {
            if (empty($rs['route'])) {
                return false;
            }
            
            $checkMenuRoute = static::checkMenuRoute($rs['route']);
            if ($checkMenuRoute === false) {
                return false;
            }
           
            $pid = $parentid ? $parentid : $menuParentid;
            $newData = [
                'module' => $moduleName,
                'parentid' => $pid,
                'title' => isset($rs['title']) ? $rs['title'] : '',
                'name' => isset($rs['route']) ? $rs['route'] : '',
                'icon' => isset($rs['icon']) ? $rs['icon'] : '',
                'tip' => isset($rs['tip']) ? $rs['tip'] : '',
                'parameter' => isset($rs['parameter']) ? $rs['parameter'] : '',
                'fields' => isset($rs['fields']) ? $rs['fields'] : '',
                'condition' => isset($rs['condition']) ? $rs['condition'] : '',
                'method' => isset($rs['method']) ? $rs['method'] : '',
                'type' => (isset($rs['type']) && $rs['type'] == 2) ? $rs['type'] : 1,
                'listorder' => isset($rs['listorder']) ? $rs['listorder'] : 100,
                'is_menu' => isset($rs['is_menu']) ? $rs['is_menu'] : 0,
                'status' => 1,
            ];

            $result = AuthRuleModel::create($newData);
            if (!$result) {
                return false;
            }
            
            //是否有子菜单
            if (!empty($rs['child'])) {
                if (static::install($rs['child'], $config, $result['id']) !== true) {
                    return false;
                }
            }
        }
        
        // 清除缓存
        cache('lake_admin_menus', null);
        
        return true;
    }
    
    /**
     * 删除对应模块菜单和权限
     * @param type $moduleName 模块名称
     * @return boolean
     */
    public static function uninstall($moduleName)
    {
        if (empty($moduleName)) {
            return false;
        }    
        
        // 删除对应菜单
        AuthRuleModel::where([
            'module' => $moduleName,
        ])->delete();
        
        return true;
    }
    
    /**
     * 启用
     */
    public static function enable($name = '')
    {
        if (empty($name)) {
            return false;
        }
        
        $status = AuthRuleModel::where([
            'module' => $name,
        ])->update([
            'status' => 1,
        ]);
        if ($status === false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 禁用
     */
    public static function disable($name = '')
    {
        if (empty($name)) {
            return false;
        }
        
        $status = AuthRuleModel::where([
            'module' => $name,
        ])->update([
            'status' => 0,
        ]);
        if ($status === false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 检测route是否正确
     * @param type $route route内容
     * @return array
     */
    protected static function checkMenuRoute($route)
    {
        $route = explode('/', $route);
        if (count($route) < 3) {
            return false;
        }

        return true;
    }
    
}