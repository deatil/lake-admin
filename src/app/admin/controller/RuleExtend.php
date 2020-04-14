<?php

namespace app\admin\controller;

use think\facade\Db;
use think\facade\View;

use app\admin\module\Module as ModuleModule;
use app\admin\model\AuthGroup as AuthGroupModel;

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
            
            $data = Db::name('auth_rule_extend')
                ->alias('are')
                ->leftJoin('auth_group ag ', 'are.group_id = ag.id')
                ->field('are.*, ag.title as group_title ')
                ->where($map)
                ->page($page, $limit)
                ->order('are.module ASC')
                ->select()
                ->toArray();
            
            $total = Db::name('auth_rule_extend')
                ->alias('are')
                ->leftJoin('auth_group ag ', 'are.group_id = ag.id')
                ->where($map)
                ->count();
        
            $result = [
                "code" => 0, 
                "count" => $total, 
                "data" => $data
            ];
            
            return json($result);
        }
        return View::fetch();
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
            $result = $this->validate($data, 'RuleExtend.insert');
            if (true !== $result) {
                return $this->error($result);
            }
            
            $data['id'] = md5(mt_rand(100000, 999999).microtime().mt_rand(100000, 999999));
            $rs = Db::name('auth_rule_extend')->data($data)->insert();
       
            if ($rs === false) {
                return $this->error("添加失败！");
            }
            
            return $this->success("添加成功！");

        } else {
            View::assign("roles", (new AuthGroupModel)->getGroups());
            
            // 模块列表
            $modules = (new ModuleModule())->getAll();
            View::assign("modules", $modules);
            
            return View::fetch();
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
            $result = $this->validate($data, 'RuleExtend.update');
            if (true !== $result) {
                return $this->error($result);
            }
            
            $rs = Db::name('auth_rule_extend')
                ->update($data);
            
            if ($rs === false) {
                $this->error("修改失败！");
            }
            
            $this->success("修改成功！");
        } else {
            $id = $this->request->param('id');
            $data = Db::name('auth_rule_extend')->where([
                "id" => $id,
            ])->find();
            if (empty($data)) {
                $this->error('信息不存在！');
            }
            
            View::assign("data", $data);
            View::assign("roles", (new AuthGroupModel)->getGroups());
            
            // 模块列表
            $modules = (new ModuleModule())->getAll();
            View::assign("modules", $modules);
            
            return View::fetch();
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
        
        $data = Db::name('auth_rule_extend')->where([
            "id" => $id,
        ])->find();
        if (empty($data)) {
            $this->error('信息不存在！');
        }
        
        $rs = Db::name('auth_rule_extend')
            ->where([
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
        
        $data = Db::name('auth_rule_extend')->where([
            "id" => $id,
        ])->find();
        if (empty($data)) {
            $this->error('信息不存在！');
        }
        
        View::assign("data", $data);
        return View::fetch();
    }

}
