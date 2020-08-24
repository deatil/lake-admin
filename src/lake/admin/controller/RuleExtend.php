<?php

namespace lake\admin\controller;

use lake\admin\facade\Module as ModuleFacade;
use lake\admin\model\AuthGroup as AuthGroupModel;
use lake\admin\model\AuthRuleExtend as AuthRuleExtendModel;

/**
 * 扩展权限
 *
 * @create 2019-7-12
 * @author deatil
 */
class RuleExtend extends Base
{
    /**
     * 列表
     *
     * @create 2019-7-10
     * @author deatil
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $limit = $this->request->param('limit/d', 10);
            $page = $this->request->param('page/d', 10);
            
            $searchField = $this->request->param('search_field/s', '', 'trim');
            $keyword = $this->request->param('keyword/s', '', 'trim');
            
            $map = [];
            if (!empty($searchField) && !empty($keyword)) {
                if ($searchField == 'group') {
                    $searchField = 'ag.title';
                } else {
                    $searchField = 'are.'.$searchField;
                }
                $map[] = [$searchField, 'like', "%$keyword%"];
            }
            
            $agTable = (new AuthGroupModel)->getName();
            $data = AuthRuleExtendModel::alias('are')
                ->leftJoin($agTable . ' ag ', 'are.group_id = ag.id')
                ->field('are.*, ag.title as group_title ')
                ->where($map)
                ->page($page, $limit)
                ->order('are.module ASC')
                ->select()
                ->toArray();
            
            $agTable = (new AuthGroupModel)->getName();
            $total = AuthRuleExtendModel::alias('are')
                ->leftJoin($agTable . ' ag ', 'are.group_id = ag.id')
                ->where($map)
                ->count();
        
            $result = [
                "code" => 0, 
                "count" => $total, 
                "data" => $data
            ];
            
            return $this->json($result);
        }
        return $this->fetch();
    }
    
    /**
     * 添加
     *
     * @create 2019-7-10
     * @author deatil
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post('');
            $result = $this->validate($data, 'lake\\admin\\validate\\RuleExtend.insert');
            if (true !== $result) {
                return $this->error($result);
            }
            
            $data['id'] = md5(mt_rand(100000, 999999).microtime().mt_rand(100000, 999999));
            $rs = AuthRuleExtendModel::insert($data);
       
            if ($rs === false) {
                return $this->error("添加失败！");
            }
            
            return $this->success("添加成功！");
            
        } else {
            $this->assign("roles", AuthGroupModel::getGroups());
            
            // 模块列表
            $modules = ModuleFacade::getAll();
            $this->assign("modules", $modules);
            
            return $this->fetch();
        }
    }
    
    /**
     * 编辑
     *
     * @create 2019-7-10
     * @author deatil
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post('');
            $result = $this->validate($data, 'lake\\admin\\validate\\RuleExtend.update');
            if (true !== $result) {
                return $this->error($result);
            }
            
            $id = $data['id'];
            unset($data['id']);
            $rs = AuthRuleExtendModel::where([
                "id" => $id,
            ])->update($data);
            
            if ($rs === false) {
                $this->error("修改失败！");
            }
            
            $this->success("修改成功！");
        } else {
            $id = $this->request->param('id');
            $data = AuthRuleExtendModel::where([
                "id" => $id,
            ])->find();
            if (empty($data)) {
                $this->error('信息不存在！');
            }
            
            $this->assign("data", $data);
            $this->assign("roles", AuthGroupModel::getGroups());
            
            // 模块列表
            $modules = ModuleFacade::getAll();
            $this->assign("modules", $modules);
            
            return $this->fetch();
        }
    }
    
    /**
     * 删除
     *
     * @create 2019-7-10
     * @author deatil
     */
    public function del()
    {
        if (!$this->request->isPost()) {
            $this->error('请求错误！');
        }
        
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('参数不能为空！');
        }
        
        $data = AuthRuleExtendModel::where([
            "id" => $id,
        ])->find();
        if (empty($data)) {
            $this->error('信息不存在！');
        }
        
        $rs = AuthRuleExtendModel::where([
                'id' => $id, 
            ])
            ->delete();
        
        if ($rs === false) {
            $this->error("删除失败！");
        }
        
        $this->success("删除成功！");
    }
    
    /**
     * 数据
     *
     * @create 2019-7-13
     * @author deatil
     */
    public function data()
    {
        if (!$this->request->isGet()) {
            $this->error('请求错误！');
        }
        
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('参数不能为空！');
        }
        
        $data = AuthRuleExtendModel::where([
            "id" => $id,
        ])->find();
        if (empty($data)) {
            $this->error('信息不存在！');
        }
        
        $this->assign("data", $data);
        return $this->fetch();
    }
    
}
