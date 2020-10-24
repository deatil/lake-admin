<?php

namespace Lake\Admin\Controller;

use think\model\Relation;

use Lake\Admin\Facade\Module as ModuleFacade;
use Lake\Admin\Model\AuthGroup as AuthGroupModel;
use Lake\Admin\Model\AuthRuleExtend as AuthRuleExtendModel;

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
                $map[] = [$searchField, 'like', "%$keyword%"];
            }
            
            $data = AuthRuleExtendModel::withJoin(['group' => function(Relation $query) {
                    $query->withField(["id","title"]);
                }])
                ->where($map)
                ->page($page, $limit)
                ->order('module ASC')
                ->select()
                ->toArray();
            
            $total = AuthRuleExtendModel::withJoin(['group' => function(Relation $query) {
                    $query->withField(["id","title"]);
                }])
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
            $result = $this->validate($data, 'Lake\\Admin\\Validate\\RuleExtend.insert');
            if (true !== $result) {
                return $this->error($result);
            }
            
            $rs = AuthRuleExtendModel::create($data);
       
            if ($rs === false) {
                return $this->error(__("添加失败！"));
            }
            
            return $this->success(__("添加成功！"));
            
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
            $result = $this->validate($data, 'Lake\\Admin\\Validate\\RuleExtend.update');
            if (true !== $result) {
                return $this->error($result);
            }
            
            $id = $data['id'];
            unset($data['id']);
            $rs = AuthRuleExtendModel::where([
                "id" => $id,
            ])->update($data);
            
            if ($rs === false) {
                $this->error(__("修改失败！"));
            }
            
            $this->success(__("修改成功！"));
        } else {
            $id = $this->request->param('id');
            $data = AuthRuleExtendModel::where([
                "id" => $id,
            ])->find();
            if (empty($data)) {
                $this->error(__('信息不存在！'));
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
            $this->error(__('请求错误！'));
        }
        
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error(__('参数不能为空！'));
        }
        
        $data = AuthRuleExtendModel::where([
            "id" => $id,
        ])->find();
        if (empty($data)) {
            $this->error(__('信息不存在！'));
        }
        
        $rs = AuthRuleExtendModel::where([
                'id' => $id, 
            ])
            ->delete();
        
        if ($rs === false) {
            $this->error(__("删除失败！"));
        }
        
        $this->success(__("删除成功！"));
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
            $this->error(__('请求错误！'));
        }
        
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error(__('参数不能为空！'));
        }
        
        $data = AuthRuleExtendModel::where([
            "id" => $id,
        ])->find();
        if (empty($data)) {
            $this->error(__('信息不存在！'));
        }
        
        $this->assign("data", $data);
        return $this->fetch();
    }
    
}
