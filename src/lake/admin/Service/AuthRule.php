<?php

namespace Lake\Admin\Service;

use Lake\Arr;

use Lake\Admin\Model\Admin as AdminModel;
use Lake\Admin\Model\AuthRule as AuthRuleModel;
use Lake\Admin\Model\AuthRuleAccess as AuthRuleAccessModel;

use Lake\Admin\Facade\Admin as AdminFacade;

/**
 * 权限规则
 *
 * @create 2020-8-28
 * @author deatil
 */
class AuthRule
{
    protected $error = '错误';
    
    /**
     * 获取菜单
     * @return type
     */
    final public function getMenuList()
    {
        $data = $this->getTree(0);
        return $data;
    }

    /**
     * 取得树形结构的菜单
     * @param type $mid
     * @param type $parent
     * @param type $level
     * @return type
     */
    final public function getTree($mid, $parent = "", $level = 1)
    {
        $data = $this->adminMenu($mid);
        $level++;
        if (is_array($data)) {
            $ret = null;
            foreach ($data as $a) {
                $id = $a['id'];
                $module = $a['module'];
                $name = $a['name'];
                
                if (strpos($name, '://') || 0 === strpos($name, '/')) {
                    $url = $name;
                } else {
                    // 附带参数
                    $exta = "";
                    if ($a['parameter']) {
                        $exta = "?" . $a['parameter'];
                    }
                    
                    $url = (string) url("{$name}{$exta}");
                }
                
                $array = [
                    "menuid" => $id,
                    "id" => $id . $module,
                    "title" => $a['title'],
                    "icon" => $a['icon'],
                    "parent" => $parent,
                    "url" => $url,
                ];
                $ret[$id . $module] = $array;
                $child = $this->getTree($a['id'], $id, $level);
                // 只考虑5层结构
                if ($child && $level <= 5) {
                    $ret[$id . $module]['items'] = $child;
                }
            }
        }
        return $ret;
    }

    /**
     * 按父ID查找菜单子项
     * @param integer $parentid   父菜单ID
     * @param integer $withSelf  是否包括他自己
     */
    final public function adminMenu($parentid, $withSelf = false)
    {
        $result = AuthRuleModel::where([
                'parentid' => $parentid, 
                'is_menu' => 1,
                'status' => 1,
            ])
            ->order('listorder ASC, module ASC')
            ->select()
            ->toArray();
        if (empty($result)) {
            $result = [];
        }
        if ($withSelf) {
            $parentInfo = AuthRuleModel::where(['id' => $parentid])->find();
            $result2[] = $parentInfo ? $parentInfo : array();
            $result = array_merge($result2, $result);
        }
        
        // 是否超级管理员
        if (AdminFacade::isAdministrator()) {
            return $result;
        }
        
        $authIdList = $this->getAuthIdList();
        
        $array = [];
        if (!empty($result)) {
            foreach ($result as $v) {
                if (in_array($v['id'], $authIdList)) {
                    $array[] = $v;
                }
            }
        }
        
        return $array;
    }

    /**
     * 获取权限ID列表
     *
     * @create 2019-7-30
     * @author deatil
     */    
    protected function getAuthIdList()
    {
        static $authIdList = [];
        if (!empty($authIdList)) {
            return $authIdList;
        }
        
        $admins = AdminModel::with(['groups'])
            ->where([
                'id' => env('admin_id'),
            ])
            ->select()
            ->toArray();
            
        $groupIds = [];
        foreach ($admins as $admin) {
            if (!empty($admin['groups'])) {
                foreach ($admin['groups'] as $group) {
                    $groupIds[] = $group['id'];
                }
            }
        }
        
        $authIdList = AuthRuleAccessModel::where([
            ['group_id', 'in', $groupIds],
        ])
        ->column('rule_id');
        
        return $authIdList;
    }

    /**
     * 返回后台节点数据
     * @param boolean $tree 是否返回多维数组结构(生成菜单时用到),为false返回一维数组(生成权限节点时用到)
     * @retrun array
     *
     * 注意,返回的主菜单节点数组中有'controller'元素,以供区分子节点和主节点
     *
     */
    final public function returnNodes($tree = true)
    {
        static $tree_nodes = [];
        if ($tree && !empty($tree_nodes[(int) $tree])) {
            return $tree_nodes[$tree];
        }
        if ((int) $tree) {
            $list = AuthRuleModel::order('listorder ASC,id ASC')->select()->toArray();
            foreach ($list as $key => $value) {
                $list[$key]['url'] = $value['name'];
            }
            $nodes = Arr::listToTree($list, $pk = 'id', $pid = 'parentid', $child = 'operator', $root = 0);
            foreach ($nodes as $key => $value) {
                if (!empty($value['operator'])) {
                    $nodes[$key]['child'] = $value['operator'];
                    unset($nodes[$key]['operator']);
                }
            }
        } else {
            $nodes = AuthRuleModel::order('listorder ASC,id ASC')->select()->toArray();
            foreach ($nodes as $key => $value) {
                $nodes[$key]['url'] = $value['name'];
            }
        }
        $tree_nodes[(int) $tree] = $nodes;
        return $nodes;
    }

    /**
     * 获取菜单列表
     * @param type $data
     * @return type
     */
    public function getMenusList()
    {
        $menus = cache('lake_admin_menus');
        if (!$menus) {
            $menus = [];
            
            $data = AuthRuleModel::select()->toArray();
            if (!empty($data)) {
                foreach ($data as $rs) {
                    $menus[$rs['id']] = $rs;
                }
            }
            
            cache('lake_admin_menus', $menus);
        }
        
        return $menus;
    }
    
    /**
     * 获取错误信息
     * @return string
     *
     * @create 2020-7-26
     * @author deatil
     */
    public function getError()
    {
        return $this->error;
    }
    
}
