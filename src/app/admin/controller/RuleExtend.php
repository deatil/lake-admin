<?php

namespace app\admin\controller;

use think\Db;

use app\admin\module\Module as ModuleService;

/**
 * 扩展权限
 *
 * @create 2019-7-12
 * @author deatil
 */
class RuleExtend extends Base
{
    
    /**
     * 框架构造函数
     *
     * @create 2019-8-5
     * @author deatil
     */
    protected function initialize()
    {
        parent::initialize();
    }

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

            $search_field = $this->request->param('search_field/s', '', 'trim');
            $keyword = $this->request->param('keyword/s', '', 'trim');
            
            $map = [];
            if (!empty($search_field) && !empty($keyword)) {
                if ($search_field == 'group') {
                    $search_field = 'ag.title';
                } else {
                    $search_field = 'are.'.$search_field;
                }
                $map[] = [$search_field, 'like', "%$keyword%"];
            }
            
            $data = Db::name('auth_rule_extend')
                ->alias('are')
                ->leftJoin('auth_group ag ', 'are.group_id = ag.id')
                ->field('are.*, ag.title as group_title ')
                ->where($map)
                ->page($page, $limit)
                ->order('are.module ASC')
                ->select();
            
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
            $result = $this->validate($data, 'RuleExtend.insert');
            if (true !== $result) {
                return $this->error($result);
            }
            
            $data['id'] = md5(mt_rand(100000, 999999).microtime().mt_rand(100000, 999999));
            $rs = Db::name('auth_rule_extend')->data($data)->insert();
       
            if ($rs === false) {
                $this->error("添加失败！");
            }
            
            $this->success("添加成功！");

        } else {
            $this->assign("roles", model('admin/AuthGroup')->getGroups());
            
            // 模块列表
            $modules = (new ModuleService())->getAll();
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
            
            $this->assign("data", $data);
            $this->assign("roles", model('admin/AuthGroup')->getGroups());
            
            // 模块列表
            $modules = (new ModuleService())->getAll();
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
        
        $this->assign("data", $data);
        return $this->fetch();
    }

}
