<?php

namespace app\admin\controller;

use think\facade\Db;
use think\facade\View;

use app\admin\facade\Module as ModuleFacade;

/**
 * 嵌入点
 *
 * @create 2019-7-20
 * @author deatil
 */
class Hook extends Base
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
     * @create 2019-7-20
     * @author deatil
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $limit = $this->request->param('limit/d', 20);
            $page = $this->request->param('page/d', 1);
            $map = $this->buildparams();
            
            $data = Db::name('hook')
                ->page($page, $limit)
                ->where($map)
                ->order('listorder ASC, module ASC')
                ->select()
                ->toArray();
            $total = Db::name('hook')
                ->where($map)
                ->order('listorder ASC, module ASC')
                ->count();
            
            $result = [
                "code" => 0, 
                "count" => $total, 
                "data" => $data,
            ];
            return $this->json($result);
        } else {
            $this->buildparams();
            
            return $this->fetch();
        }
    }

    /**
     * 添加
     *
     * @create 2019-7-20
     * @author deatil
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post('');
            $result = $this->validate($data, 'Hook.insert');
            if (true !== $result) {
                return $this->error($result);
            }
            
            $data['id'] = md5(time().lake_to_guid_string(time()));
            if (isset($data['status']) 
                && $data['status'] == 1
            ) {
                $data['status'] = 1;
            } else {
                $data['status'] = 0;
            }
            
            $data['add_time'] = time();
            $data['add_ip'] = request()->ip(1);
            
            $status = Db::name('hook')->data($data)->insert();
       
            if ($status === false) {
                $this->error("添加失败！");
            }
            
            $this->success("添加成功！");
            
        } else {
            // 模块列表
            $modules = ModuleFacade::getAll();
            $this->assign("modules", $modules);
            
            return $this->fetch();
        }
    }
    
    /**
     * 编辑
     *
     * @create 2019-7-20
     * @author deatil
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $result = $this->validate($data, 'Hook.update');
            if (true !== $result) {
                return $this->error($result);
            }
            
            if (isset($data['status']) 
                && $data['status'] == 1
            ) {
                $data['status'] = 1;
            } else {
                $data['status'] = 0;
            }
            
            $rs = Db::name('hook')
                ->update($data);
            
            if ($rs === false) {
                $this->error("修改失败！");
            }
            
            $this->success("修改成功！");
        } else {
            $id = $this->request->param('id/s');
            $data = Db::name('hook')->where([
                "id" => $id,
            ])->find();
            if (empty($data)) {
                $this->error('信息不存在！');
            }
            
            $this->assign("data", $data);
            
            // 模块列表
            $modules = ModuleFacade::getAll();
            $this->assign("modules", $modules);
            
            return $this->fetch();
        }
    }
    
    /**
     * 删除
     *
     * @create 2019-7-20
     * @author deatil
     */
    public function del()
    {
        if (!$this->request->isPost()) {
            $this->error('请求错误！');
        }
        
        $id = $this->request->param('id/s');
        if (empty($id)) {
            $this->error('参数不能为空！');
        }
        
        $data = Db::name('hook')->where([
            "id" => $id,
        ])->find();
        if (empty($data)) {
            $this->error('信息不存在！');
        }
        
        $rs = Db::name('hook')
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
     * 排序
     *
     * @create 2019-7-20
     * @author deatil
     */
    public function listorder()
    {
        if (!$this->request->isPost()) {
            $this->error('请求错误！');
        }
        
        $id = $this->request->param('id/s', '');
        if (empty($id)) {
            $this->error('参数不能为空！');
        }
        
        $listorder = $this->request->param('value/d', 100);
        
        $rs = Db::name('hook')
            ->where([
                'id' => $id, 
            ])
            ->update([
                'listorder' => $listorder,
            ]);
        
        if ($rs === false) {
            $this->error("排序失败！");
        }
        
        $this->success("排序成功！");
    }
    
    /**
     * 模块列表
     *
     * @create 2019-7-28
     * @author deatil
     */
    public function module()
    {
        if ($this->request->isAjax()) {
            $limit = $this->request->param('limit/d', 20);
            $page = $this->request->param('page/d', 1);
            $map = $this->buildparams();
            
            $data = Db::name('hook')
                ->page($page, $limit)
                ->where($map)
                ->field("module, count(module) as num")
                ->group("module")
                ->order('module ASC')
                ->select()
                ->toArray();
            $total = Db::name('hook')
                ->where($map)
                ->group("module")
                ->count();
            
            $result = [
                "code" => 0, 
                "count" => $total, 
                "data" => $data,
            ];
            return $this->json($result);
        } else {
            $this->buildparams();
            
            return $this->fetch();
        }
    }
    
    /**
     * 嵌入点列表
     *
     * @create 2019-7-28
     * @author deatil
     */
    public function name()
    {
        if ($this->request->isAjax()) {
            $limit = $this->request->param('limit/d', 20);
            $page = $this->request->param('page/d', 1);
            $map = $this->buildparams();
            
            $data = Db::name('hook')
                ->page($page, $limit)
                ->where($map)
                ->field("name, count(name) as num")
                ->group("name")
                ->order('name ASC')
                ->select()
                ->toArray();
            $total = Db::name('hook')
                ->where($map)
                ->group("name")
                ->count();
            
            $result = [
                "code" => 0, 
                "count" => $total, 
                "data" => $data,
            ];
            return $this->json($result);
        } else {
            $this->buildparams();
            
            return $this->fetch();
        }
    }
    
}
