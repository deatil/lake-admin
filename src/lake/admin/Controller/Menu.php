<?php

namespace Lake\Admin\Controller;

use Lake\TTree;

use Lake\Admin\Facade\Module as ModuleFacade;
use Lake\Admin\Model\AuthRule as AuthRuleModel;

/**
 * 后台菜单管理
 *
 * @create 2019-7-7
 * @author deatil
 */
class Menu extends Base
{
    
    /**
     * 后台菜单首页
     *
     * @create 2019-7-30
     * @author deatil
     */
    public function index()
    {
        if ($this->request->isAjax()) {

            $result = AuthRuleModel::order([
                    'listorder' => 'ASC', 
                    'id' => 'ASC',
                ])
                ->select()
                ->toArray();

            $TTree = new TTree();
            $menuTree = $TTree->withData($result)->buildArray(0);
            $menus = $TTree->buildFormatList($menuTree, 'title');
            $total = count($menus);
            
            $result = [
                "code" => 0, 
                "count" => $total, 
                "data" => $menus
            ];
            return $this->json($result);
        }
        
        return $this->fetch();

    }

    /**
     * 全部
     *
     * @create 2019-7-30
     * @author deatil
     */
    public function all()
    {
        if ($this->request->isAjax()) {
            $limit = $this->request->param('limit/d', 20);
            $page = $this->request->param('page/d', 1);
            
            $searchField = $this->request->param('search_field/s', '', 'trim');
            $keyword = $this->request->param('keyword/s', '', 'trim');
            
            $map = [];
            if (!empty($searchField) && !empty($keyword)) {
                $map[] = [$searchField, 'like', "%$keyword%"];
            }
            
            $data = AuthRuleModel::where($map)
                ->page($page, $limit)
                ->order('module ASC, name ASC, title ASC, id ASC')
                ->select()
                ->toArray();
            $total = AuthRuleModel::where($map)->count();
            
            $result = [
                "code" => 0, 
                "count" => $total, 
                "data" => $data,
            ];
            return $this->json($result);
        } else {
            return $this->fetch();
        }
    }

    /**
     * 添加
     *
     * @create 2019-7-30
     * @author deatil
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            
            if (!isset($data['is_menu'])) {
                $data['is_menu'] = 0;
            } else {
                $data['is_menu'] = 1;
            }
            
            if (!isset($data['is_need_auth'])) {
                $data['is_need_auth'] = 0;
            } else {
                $data['is_need_auth'] = 1;
            }
            
            if (!isset($data['status'])) {
                $data['status'] = 0;
            } else {
                $data['status'] = 1;
            }

            $result = $this->validate($data, 'Lake\\Admin\\Validate\\AuthRule.insert');
            if (true !== $result) {
                return $this->error($result);
            }
            
            if (!empty($data['name'])) {
                $names = explode('/', $data['name']);
                if (count($names) < 3) {
                    $this->error(__('后台菜单格式错误！'));
                }
            }
            
            $res = AuthRuleModel::create($data);
            
            if ($res === false) {
                $this->error(__('添加失败！'));
            }
            
            $this->success(__("添加成功！"));
        } else {
            $parentid = $this->request->param('parentid/s', '');
            
            $menus = AuthRuleModel::order([
                'listorder', 
                'id' => 'DESC',
            ])->select()->toArray();
            
            $TTree = new TTree();
            $menuTree = $TTree->withData($menus)->buildArray(0);
            $menus = $TTree->buildFormatList($menuTree, 'title');
            
            $this->assign("parentid", $parentid);
            $this->assign("menus", $menus);
            
            // 模块列表
            $modules = ModuleFacade::getAll();
            $this->assign("modules", $modules);
            
            return $this->fetch();
        }
    }

    /**
     * 编辑后台菜单
     *
     * @create 2019-7-30
     * @author deatil
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            
            $rs = AuthRuleModel::where([
                "id" => $data['id'],
            ])->find();
            if (empty($rs)) {
                $this->error(__('权限菜单不存在！'));
            }
            
            if ($rs['is_system'] == 1) {
                $this->error(__('系统权限菜单不能进行编辑！'));
            }
            
            if (!isset($data['is_menu'])) {
                $data['is_menu'] = 0;
            } else {
                $data['is_menu'] = 1;
            }
            
            if (!isset($data['is_need_auth'])) {
                $data['is_need_auth'] = 0;
            } else {
                $data['is_need_auth'] = 1;
            }
            
            if (!isset($data['status'])) {
                $data['status'] = 0;
            } else {
                $data['status'] = 1;
            }
            
            $result = $this->validate($data, 'Lake\\Admin\\Validate\\AuthRule.update');
            if (true !== $result) {
                return $this->error($result);
            }
            
            if (!empty($data['name'])) {
                $names = explode('/', $data['name']);
                if (count($names) < 3) {
                    $this->error(__('后台菜单格式错误！'));
                }
            }
            
            $res = AuthRuleModel::update($data);
            
            if ($res === false) {
                $this->error(__('编辑失败！'));
            }
            
            $this->success(__("编辑成功！"));
        } else {
            $id = $this->request->param('id/s', '');
            
            $data = AuthRuleModel::where(["id" => $id])->find();
            if (empty($data)) {
                $this->error(__('菜单不存在！'));
            }
            
            if ($data['is_system'] == 1) {
                $this->error(__('系统权限菜单不能进行编辑！'));
            }
            
            $ruleList = AuthRuleModel::order([
                'listorder' => 'ASC', 
                'id' => 'DESC',
            ])->select()->toArray();
            
            $TTree = new TTree();
            $childsId = $TTree->getListChildsId($ruleList, $data['id']);
            $childsId[] = $data['id'];
            
            $ruleParentList = [];
            foreach ($ruleList as $r) {
                if (in_array($r['id'], $childsId)) {
                    continue;
                }
                
                $ruleParentList[] = $r;
            }
            
            $this->assign("data", $data);
            
            $menuTree = $TTree->withData($ruleParentList)->buildArray(0);
            $menus = $TTree->buildFormatList($menuTree, 'title');
            
            $this->assign("parentid", $data['parentid']);
            $this->assign("menus", $menus);
            
            // 模块列表
            $modules = ModuleFacade::getAll();
            $this->assign("modules", $modules);
            
            return $this->fetch();
        }

    }

    /**
     * 菜单删除
     *
     * @create 2019-7-30
     * @author deatil
     */
    public function delete()
    {
        if (!$this->request->isPost()) {
            $this->error(__('请求错误！'));
        }
        
        $id = $this->request->param('id/s');
        if (empty($id)) {
            $this->error(__('ID错误！'));
        }
        
        $rs = AuthRuleModel::where(["id" => $id])->find();
        if (empty($rs)) {
            $this->error(__('权限菜单不存在！'));
        }
        
        if ($rs['is_system'] == 1) {
            $this->error(__('系统权限菜单不能删除！'));
        }
        
        $result = AuthRuleModel::where(["parentid" => $id])->find();
        if (!empty($result)) {
            $this->error(__("含有子菜单，无法删除！"));
        }
        
        $res = AuthRuleModel::destroy($id);
        
        if ($res === false) {
            $this->error(__("删除失败！"));
        }
        
        $this->success(__("删除成功！"));
    }

    /**
     * 菜单排序
     *
     * @create 2019-7-30
     * @author deatil
     */
    public function listorder()
    {
        if (!$this->request->isPost()) {
            $this->error(__('请求错误！'));
        }
        
        $id = $this->request->param('id/s', 0);
        if (empty($id)) {
            $this->error(__('参数不能为空！'));
        }
        
        $listorder = $this->request->param('value/d', 100);
        
        $rs = AuthRuleModel::update([
            'listorder' => $listorder,
        ], [
            'id' => $id,
        ]);
        if ($rs === false) {
            $this->error(__("菜单排序失败！"));
        }
        
        $this->success(__("菜单排序成功！"));
    }

    /**
     * 菜单权限验证状态
     *
     * @create 2019-7-30
     * @author deatil
     */
    public function setauth()
    {
        if (!$this->request->isPost()) {
            $this->error(__('请求错误！'));
        }
        
        $id = $this->request->param('id/s');
        if (empty($id)) {
            $this->error(__('参数不能为空！'));
        }
        
        $status = $this->request->param('status/d', 0);
        
        $rs = AuthRuleModel::update([
            'is_need_auth' => $status,
        ], [
            'id' => $id,
        ]);
        if ($rs === false) {
            $this->error(__('操作失败！'));
        }
        
        $this->success(__('操作成功！'));
    }

    /**
     * 菜单显示状态
     *
     * @create 2019-7-30
     * @author deatil
     */
    public function setmenu()
    {
        if (!$this->request->isPost()) {
            $this->error(__('请求错误！'));
        }
        
        $id = $this->request->param('id/s');
        if (empty($id)) {
            $this->error(__('参数不能为空！'));
        }
        
        $status = $this->request->param('status/d', 0);
        
        $rs = AuthRuleModel::update([
            'is_menu' => $status,
        ], [
            'id' => $id,
        ]);
        if ($rs === false) {
            $this->error(__('操作失败！'));
        }
        
        $this->success(__('操作成功！'));
    }

    /**
     * 菜单状态
     *
     * @create 2019-7-30
     * @author deatil
     */
    public function setstate()
    {
        if (!$this->request->isPost()) {
            $this->error(__('请求错误！'));
        }
        
        $id = $this->request->param('id/s');
        if (empty($id)) {
            $this->error(__('参数不能为空！'));
        }
        
        $status = $this->request->param('status/d', 0);
        
        $rs = AuthRuleModel::update([
            'status' => $status,
        ], [
            'id' => $id,
        ]);
        if ($rs === false) {
            $this->error(__('操作失败！'));
        }
        
        $this->success(__('操作成功！'));
    }

}
