<?php

namespace lake\admin\controller;

use lake\admin\model\Admin as AdminModel;
use lake\admin\model\AuthGroup as AuthGroupModel;
use lake\admin\model\AuthGroupAccess as AuthGroupAccessModel;

use lake\admin\service\AuthManager as AuthManagerService;

/**
 * 管理员管理
 *
 * @create 2019-8-1
 * @author deatil
 */
class Manager extends Base
{
    protected $AdminModel;
    
    protected $AuthManagerService;

    /**
     * 框架构造函数
     *
     * @create 2019-8-4
     * @author deatil
     */
    protected function initialize()
    {
        parent::initialize();
        
        $this->AdminModel = app(AdminModel::class);
        $this->AuthManagerService = app(AuthManagerService::class);
    }

    /**
     * 管理员管理列表
     *
     * @create 2019-8-1
     * @author deatil
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $limit = $this->request->param('limit/d', 10);
            $page = $this->request->param('page/d', 1);

            $map = $this->buildparams();
            
            if (!env('admin_is_root')) {
                $userChildGroupIds = $this->AuthManagerService->getUserChildGroupIds(env('admin_id'));
                $adminIds = AuthGroupAccessModel::where([
                        ['group_id', 'in', $userChildGroupIds],
                    ])
                    ->column('admin_id');
                $map[] = ['id', 'in', $adminIds];
            }
            
            $list = $this->AdminModel
                ->where($map)
                ->page($page, $limit)
                ->select()
                ->toArray();
            $total = $this->AdminModel
                ->where($map)
                ->count();
            
            if (!empty($list)) {
                $agaTable = (new AuthGroupAccessModel)->getName();
                foreach ($list as $k => $v) {
                    $groups = AuthGroupModel::alias('ag')
                        ->join($agaTable . ' aga', "aga.group_id = ag.id")
                        ->where([
                            'aga.admin_id' => $v['id'],
                        ])
                        ->column('ag.title');
                    if (!empty($groups)) {
                        $list[$k]['groups'] = implode('，', $groups);
                    } else {
                        $list[$k]['groups'] = '-';
                    }
                }
            }
            
            $result = [
                "code" => 0, 
                "count" => $total, 
                "data" => $list
            ];
            return $this->json($result);
        }
        return $this->fetch();
    }

    /**
     * 添加管理员
     *
     * @create 2019-8-1
     * @author deatil
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post('');
            
            $result = $this->validate($data, 'lake\\admin\\validate\\Admin.insert');
            if (true !== $result) {
                return $this->error($result);
            }
            
            if (isset($data['status'])) {
                $data['status'] = 1;
            } else {
                $data['status'] = 0;
            }
            
            if (isset($data['roleid']) && !empty($data['roleid'])) {
                $roleids = explode(',', $data['roleid']);
                $userChildGroupIds = $this->AuthManagerService->getUserChildGroupIds(env('admin_id'));
                $isAllow = true;
                foreach ($roleids as $roleid) {
                    if (!in_array($roleid, $roleids)) {
                        $isAllow = false;
                        break;
                    }
                }
                
                if ($isAllow === false) {
                    $this->error('选择权限组错误！');
                }
            }
            
            $status = $this->AdminModel->createManager($data);
            if ($status === false) {
                $error = $this->AdminModel->getError();
                $this->error($error ? $error : '添加失败！');
            }
           
            $this->success("添加管理员成功！");
        } else {
            if (!env('admin_is_root')) {
                $userChildGroupIds = $this->AuthManagerService->getUserChildGroupIds(env('admin_id'));
                $roles = AuthGroupModel::getGroups([
                        ['id', 'in', $userChildGroupIds],
                    ]);
            } else {
                $roles = AuthGroupModel::getGroups();
            }
            $this->assign("roles", $roles);
            
            return $this->fetch();
        }
    }

    /**
     * 管理员编辑
     *
     * @create 2019-8-1
     * @author deatil
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post('');
            
            $result = $this->validate($data, 'lake\\admin\\validate\\Admin.update');
            if (true !== $result) {
                return $this->error($result);
            }
            
            if (empty($data['id'])) {
                $this->error('参数错误！');
            }
            
            if (env('admin_is_root') != 1) {
                if ($data['id'] == env('admin_id')) {
                    $this->error('不能修改自己的账户！');
                }
            }
            
            $adminInfo = $this->AdminModel
                ->where([
                    "id" => $data['id'],
                ])
                ->find();
            if (empty($adminInfo)) {
                $this->error('信息不存在！');
            }
            
            if ($adminInfo['is_system'] == 1) {
                $this->error('系统默认账户不可操作！');
            }
            
            if (isset($data['status'])) {
                $data['status'] = 1;
            } else {
                $data['status'] = 0;
            }
            
            if (isset($data['roleid']) && !empty($data['roleid'])) {
                $roleids = explode(',', $data['roleid']);
                $userChildGroupIds = $this->AuthManagerService->getUserChildGroupIds(env('admin_id'));
                $isAllow = true;
                foreach ($roleids as $roleid) {
                    if (!in_array($roleid, $userChildGroupIds)) {
                        $isAllow = false;
                        break;
                    }
                }
                
                if ($isAllow === false) {
                    $this->error('选择权限组错误！');
                }
            }
            
            $status = $this->AdminModel->editManager($data);
            if ($status === false) {
                $error = $this->AdminModel->getError();
                $this->error($error ? $error : '修改失败！');
            }
            
            $this->success("修改成功！");
        } else {
            $id = $this->request->param('id/s');
            if (empty($id)) {
                $this->error('参数错误！');
            }
            
            $data = $this->AdminModel
                ->where([
                    "id" => $id,
                ])
                ->find();
            if (empty($data)) {
                $this->error('该信息不存在！');
            }
            
            if ($data['is_system'] == 1) {
                $this->error('系统默认账户不可操作！');
            }
            
            $data['gids'] = AuthGroupAccessModel::where([
                    'module' => 'admin',
                    'admin_id' => $id,
                ])
                ->column('group_id');
            
            $this->assign("data", $data);
            
            if (!env('admin_is_root')) {
                $userChildGroupIds = $this->AuthManagerService->getUserChildGroupIds(env('admin_id'));
                $roles = AuthGroupModel::getGroups([
                        ['id', 'in', $userChildGroupIds],
                    ]);
            } else {
                $roles = AuthGroupModel::getGroups();
            }
            $this->assign("roles", $roles);
            
            return $this->fetch();
        }
    }
    
    /**
     * 管理员删除
     *
     * @create 2019-8-1
     * @author deatil
     */
    public function del()
    {
        if (!$this->request->isPost()) {
            $this->error('访问错误！');
        }
        
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('参数错误！');
        }
        
        $adminInfo = $this->AdminModel
            ->where([
                "id" => $id,
            ])
            ->find();
        if (empty($adminInfo)) {
            $this->error('信息不存在！');
        }
        
        if ($adminInfo['is_system'] == 1) {
            $this->error('系统默认账户不可操作！');
        }
        
        $rs = $this->AdminModel->deleteManager($id);
        if ($rs === false) {
            $this->error($this->AdminModel->getError() ?: '删除失败！');
        }
        
        $this->success("删除成功！");
    }
    
    /**
     * 管理员详情
     *
     * @create 2019-8-1
     * @author deatil
     */
    public function view()
    {
        if (!$this->request->isGet()) {
            $this->error('访问错误！');
        }
        
        $id = $this->request->param('id/s');
        if (empty($id)) {
            $this->error('参数错误！');
        }
        
        $data = $this->AdminModel->where([
            "id" => $id,
        ])->find();
        if (empty($data)) {
            $this->error('该信息不存在！');
        }
        
        $gids = AuthGroupAccessModel::where([
                'module' => 'admin',
                'admin_id' => $id,
            ])
            ->column('group_id');
        $authGroups = AuthGroupModel::getGroups();
        
        $groups = [];
        if (!empty($authGroups)) {
            foreach ($authGroups as $authGroup) {
                if (in_array($authGroup['id'], $gids)) {
                    $groups[] = $authGroup['title'];
                }
            }
        }
        
        $data['groups'] = implode(',', $groups);
        
        $this->assign("data", $data);
        return $this->fetch();
    }
    
    /**
     * 管理员更新密码
     *
     * @create 2019-7-28
     * @author deatil
     */
    public function password()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post('');
            
            if (empty($post) 
                || !isset($post['id']) 
                || !is_array($post)
            ) {
                $this->error('没有修改的数据！');
                return false;
            }
            
            if (empty($post['password'])) {
                $this->error('新密码不能为空！');
            }
            if (empty($post['password_confirm'])) {
                $this->error('确认密码不能为空！');
            }
            
            if ($post['password'] != $post['password_confirm']) {
                $this->error('两次密码不一致！');
            }
            
            $data['id'] = $post['id'];
            $data['password'] = $post['password'];
        
            $rs = $this->AdminModel->editManager($data);
            if ($rs === false) {
                $this->error($this->AdminModel->getError() ?: '修改失败！');
            }
            
            $this->success("修改成功！");
        } else {
            $id = $this->request->param('id/s');
            $data = $this->AdminModel
                ->where([
                    "id" => $id,
                ])
                ->find();
            if (empty($data)) {
                $this->error('该信息不存在！');
            }
            
            $this->assign("data", $data);
            
            return $this->fetch();
        }
    }
    
}
