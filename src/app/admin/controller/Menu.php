<?php

namespace app\admin\controller;

use think\facade\Db;
use think\facade\View;

use lake\Tree;

use app\admin\model\AuthRule as AuthRuleModel;
use app\admin\module\Module as ModuleModule;

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
            $tree = new Tree();
            $tree->icon = ['', '', ''];
            $tree->nbsp = '';
            $result = AuthRuleModel::order([
                    'listorder' => 'ASC', 
                    'id' => 'ASC',
                ])
                ->select()
                ->toArray();

            $tree->init($result);
            $list = $tree->getTreeList($tree->getTreeArray(0), 'title');
            $total = count($list);
            
            $result = [
                "code" => 0, 
                "count" => $total, 
                "data" => $list
            ];
            return json($result);
        }
        
        return View::fetch();

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
            return json($result);
        } else {
            return View::fetch();
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
            
            if (!empty($data['name'])) {
                $rule = AuthRuleModel::where([
                    "name" => $data['name']
                ])->find();
                if (!empty($rule)) {
                    $this->error('规则已经存在，请重新填写！');
                }
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

            $result = $this->validate($data, 'AuthRule.add');
            if (true !== $result) {
                return $this->error($result);
            }
            
            if (!empty($data['name'])) {
                $names = explode('/', $data['name']);
                if (count($names) < 3) {
                    $this->error('后台菜单格式错误！');
                }
            }
            
            $data['id'] = md5(time().md5($data['module']).md5($data['title']).md5($data['module']).lake_get_random_string(12));
            $res = AuthRuleModel::create($data);
            
            if ($res === false) {
                $this->error('添加失败！');
            }
            
            $this->success("添加成功！");
        } else {
            $tree = new Tree();
            $parentid = $this->request->param('parentid/s', '');
            $result = AuthRuleModel::order([
                'listorder', 
                'id' => 'DESC',
            ])->select()->toArray();
            $array = array();
            foreach ($result as $r) {
                $r['selected'] = ($r['id'] == $parentid) ? 'selected' : '';
                $array[] = $r;
            }
            $str = "<option value='\$id' \$selected>\$spacer \$title</option>";
            $tree->init($array);
            $selectCategorys = $tree->getTree(0, $str);
            View::assign("select_categorys", $selectCategorys);
            
            // 模块列表
            $modules = (new ModuleModule())->getAll();
            View::assign("modules", $modules);
            
            return View::fetch();
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
                $this->error('权限菜单不存在！');
            }
            
            if (!empty($data['name'])) {
                $rule = AuthRuleModel::where([
                    "name" => $data['name']
                ])->find();
                if (!empty($rule) && $rule['id'] == $data['id']) {
                    $this->error('规则已经存在，请重新填写！');
                }
            }
            
            if ($rs['is_system'] == 1) {
                $this->error('系统权限菜单不能进行编辑！');
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
            
            $result = $this->validate($data, 'AuthRule.edit');
            if (true !== $result) {
                return $this->error($result);
            }
            
            if (!empty($data['name'])) {
                $names = explode('/', $data['name']);
                if (count($names) < 3) {
                    $this->error('后台菜单格式错误！');
                }
            }
            
            $res = AuthRuleModel::update($data);
            
            if ($res === false) {
                $this->error('编辑失败！');
            }
            
            $this->success("编辑成功！");
        } else {
            $tree = new Tree();
            $id = $this->request->param('id/s', '');
            $rs = AuthRuleModel::where(["id" => $id])->find();
            
            if ($rs['is_system'] == 1) {
                $this->error('系统权限菜单不能进行编辑！');
            }
            
            $result = AuthRuleModel::order([
                'listorder' => 'ASC', 
                'id' => 'DESC',
            ])->select()->toArray();
            
            $childsId = $tree->getChildsId($result, $rs['id']);
            $childsId[] = $rs['id'];
            
            $array = [];
            foreach ($result as $r) {
                if (in_array($r['id'], $childsId)) {
                    continue;
                }
                
                $r['selected'] = ($r['id'] == $rs['parentid']) ? 'selected' : '';
                $array[] = $r;
            }
            
            $str = "<option value='\$id' \$selected>\$spacer \$title</option>";
            $tree->init($array);
            $selectCategorys = $tree->getTree(0, $str);
            View::assign("data", $rs);
            View::assign("select_categorys", $selectCategorys);
            
            // 模块列表
            $modules = (new ModuleModule())->getAll();
            View::assign("modules", $modules);
            
            return View::fetch();
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
            $this->error('请求错误！');
        }
        
        $id = $this->request->param('id/s');
        if (empty($id)) {
            $this->error('ID错误');
        }
        
        $rs = AuthRuleModel::where(["id" => $id])->find();
        if (empty($rs)) {
            $this->error('权限菜单不存在！');
        }
        
        if ($rs['is_system'] == 1) {
            $this->error('系统权限菜单不能删除！');
        }
        
        $result = AuthRuleModel::where(["parentid" => $id])->find();
        if (!empty($result)) {
            $this->error("含有子菜单，无法删除！");
        }
        
        $res = AuthRuleModel::destroy($id);
        
        if ($res === false) {
            $this->error("删除失败！");
        }
        
        $this->success("删除成功！");
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
            $this->error('请求错误！');
        }
        
        $id = $this->request->param('id/s', 0);
        if (empty($id)) {
            $this->error('参数不能为空！');
        }
        
        $listorder = $this->request->param('value/d', 100);
        
        $rs = AuthRuleModel::update([
            'listorder' => $listorder,
        ], [
            'id' => $id,
        ]);
        if ($rs === false) {
            $this->error("菜单排序失败！");
        }
        
        $this->success("菜单排序成功！");
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
            $this->error('请求错误！');
        }
        
        $id = $this->request->param('id/s');
        if (empty($id)) {
            $this->error('参数不能为空！');
        }
        
        $status = $this->request->param('status/d', 0);
        
        $rs = AuthRuleModel::update([
            'is_need_auth' => $status,
        ], [
            'id' => $id,
        ]);
        if ($rs === false) {
            $this->error('操作失败！');
        }
        
        $this->success('操作成功！');
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
            $this->error('请求错误！');
        }
        
        $id = $this->request->param('id/s');
        if (empty($id)) {
            $this->error('参数不能为空！');
        }
        
        $status = $this->request->param('status/d', 0);
        
        $rs = AuthRuleModel::update([
            'is_menu' => $status,
        ], [
            'id' => $id,
        ]);
        if ($rs === false) {
            $this->error('操作失败！');
        }
        
        $this->success('操作成功！');
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
            $this->error('请求错误！');
        }
        
        $id = $this->request->param('id/s');
        if (empty($id)) {
            $this->error('参数不能为空！');
        }
        
        $status = $this->request->param('status/d', 0);
        
        $rs = AuthRuleModel::update([
            'status' => $status,
        ], [
            'id' => $id,
        ]);
        if ($rs === false) {
            $this->error('操作失败！');
        }
        
        $this->success('操作成功！');
    }

}
