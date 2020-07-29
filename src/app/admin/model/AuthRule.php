<?php

namespace app\admin\Model;

use lake\Arr;

use app\admin\service\AdminAuth as AdminAuthService;
use app\admin\facade\Admin as AdminFacade;

/**
 * 权限规则模型
 *
 * @create 2019-7-9
 * @author deatil
 */
class AuthRule extends ModelBase
{
    // 设置当前模型对应的数据表名称
    protected $name = 'auth_rule';
    
    const RULE_MENU = 1; // 菜单
    const RULE_URL = 2;
    
    /**
     * 获取不需要鉴权的菜单
     * @return type
     */
    public function getNoNeedAuthRuleList()
    {
        $data = $this->where([
                'is_need_auth' => 0,
                'status' => 1,
            ])
            ->order('listorder ASC,module ASC')
            ->column('name');
        return $data;
    }
    
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
     * @param type $Level
     * @return type
     */
    final public function getTree($mid, $parent = "", $Level = 1)
    {
        $data = $this->adminMenu($mid);
        $Level++;
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
                    
                    $url = (string) url("{$name}{$exta}", ["menuid" => $id]);
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
                $child = $this->getTree($a['id'], $id, $Level);
                // 由于后台管理界面只支持三层，超出的层级的不显示
                if ($child && $Level <= 4) {
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
        $result = $this->where([
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
            $parentInfo = $this->where(['id' => $parentid])->find();
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
        
        $Auth = AdminAuthService::instance();
        $authIdList = $Auth->getAuthIdList(env('admin_id'), [1, 2]);
        
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
            $list = $this->order('listorder ASC,id ASC')->select()->toArray();
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
            $nodes = $this->order('listorder ASC,id ASC')->select()->toArray();
            foreach ($nodes as $key => $value) {
                $nodes[$key]['url'] = $value['name'];
            }
        }
        $tree_nodes[(int) $tree] = $nodes;
        return $nodes;
    }

    /**
     * 模块安装时进行菜单注册
     * @param array $data 菜单数据
     * @param array $config 模块配置
     * @param type $parentid 父菜单ID
     * @return boolean
     */
    public function installModuleMenu($data, $config, $parentid = 0)
    {
        if (empty($data) || !is_array($data)) {
            $this->error = '菜单没有数据！';
            return false;
        }
        
        if (empty($config) || !is_array($data)) {
            $this->error = '模块配置信息为空！';
            return false;
        }
        
        // 父级ID
        $menuParentid = $this->where([
            'name' => 'admin/modules/index', 
        ])->value('id');
        
        // 安装模块名称
        $moduleName = $config['module'];
        foreach ($data as $rs) {
            if (empty($rs['route'])) {
                $this->error = '菜单信息配置有误，route 不能为空！';
                return false;
            }
            
            $checkMenuRoute = $this->checkMenuRoute($rs['route']);
            if ($checkMenuRoute === false) {
                $this->error = '菜单信息配置有误，route 格式错误！';
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
            $newData['id'] = md5(time().md5($newData['module']).md5($newData['title']).md5($newData['module']).lake_get_random_string(12));

            $result = self::create($newData);
            if (!$result) {
                return false;
            }
            
            //是否有子菜单
            if (!empty($rs['child'])) {
                if ($this->installModuleMenu($rs['child'], $config, $result['id']) !== true) {
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
    public function delModuleMenu($moduleName)
    {
        if (empty($moduleName)) {
            return false;
        }    
        
        // 删除对应菜单
        self::where([
            'module' => $moduleName,
        ])->delete();
        
        return true;
    }
    
    /**
     * 检测route是否正确
     * @param type $route route内容
     * @return array
     */
    private function checkMenuRoute($route)
    {
        $route = explode('/', $route);
        if (count($route) < 3) {
            return false;
        }

        return true;
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
            
            $data = $this->select()->toArray();
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
