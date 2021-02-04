<?php

namespace Lake\Admin\Controller;

use Lake\Admin\Model\Admin as AdminModel;
use Lake\Admin\Model\AuthGroup as AuthGroupModel;
use Lake\Admin\Model\AuthGroupAccess as AuthGroupAccessModel;

use Lake\Admin\Service\Manager as ManagerService;
use Lake\Admin\Service\AuthManager as AuthManagerService;

/**
 * 管理员管理
 *
 * @create 2019-8-1
 * @author deatil
 */
class Manager extends Base
{
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
        
        $this->AuthManagerService = new AuthManagerService;
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
            
            $list = AdminModel::with(['groups'])
                ->where($map)
                ->page($page, $limit)
                ->select()
                ->visible([
                    'groups' => [
                        'title',
                    ]
                ])
                ->toArray();
            $total = AdminModel::where($map)
                ->count();
            
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
            
            $result = $this->validate($data, 'Lake\\Admin\\Validate\\Admin.insert');
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
                    $this->error(__('选择权限组错误！'));
                }
            }
            
            $managerService = (new ManagerService);
            $status = $managerService->create($data);
            if ($status === false) {
                $error = $managerService->getError();
                $this->error($error ? $error : __('添加失败！'));
            }
           
            $this->success(__("添加管理员成功！"));
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
            
            $result = $this->validate($data, 'Lake\\Admin\\Validate\\Admin.update');
            if (true !== $result) {
                return $this->error($result);
            }
            
            if (empty($data['id'])) {
                $this->error(__('参数错误！'));
            }
            
            if (env('admin_is_root') != 1) {
                if ($data['id'] == env('admin_id')) {
                    $this->error(__('你不能修改自己的账号！'));
                }
            }
            
            $adminInfo = AdminModel::where([
                    "id" => $data['id'],
                ])
                ->find();
            if (empty($adminInfo)) {
                $this->error(__('信息不存在！'));
            }
            
            if ($adminInfo['is_system'] == 1) {
                $this->error(__('系统默认账号不可操作！'));
            }
            
            if (isset($data['status'])) {
                $data['status'] = 1;
            } else {
                $data['status'] = 0;
            }
            
            if (env('admin_is_root') === false) {
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
                        $this->error(__('选择权限组错误！'));
                    }
                }
            }
            
            $managerService = (new ManagerService);
            $status = $managerService->edit($data);
            if ($status === false) {
                $error = $managerService->getError();
                $this->error($error ?: __('修改失败！'));
            }
            
            $this->success(__("修改成功！"));
        } else {
            $id = $this->request->param('id/s');
            if (empty($id)) {
                $this->error(__('参数错误！'));
            }
            
            $data = AdminModel::where([
                    "id" => $id,
                ])
                ->find();
            if (empty($data)) {
                $this->error(__('该信息不存在！'));
            }
            
            if ($data['is_system'] == 1) {
                $this->error(__('系统默认账号不可操作！'));
            }
            
            $data['gids'] = AuthGroupAccessModel::where([
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
            $this->error(__('访问错误！'));
        }
        
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error(__('参数错误！'));
        }
        
        $adminInfo = AdminModel::where([
                "id" => $id,
            ])
            ->find();
        if (empty($adminInfo)) {
            $this->error(__('信息不存在！'));
        }
        
        if ($adminInfo['is_system'] == 1) {
            $this->error(__('系统默认账号不可操作！'));
        }
        
        if ($adminInfo['id'] == env('admin_id')) {
            $this->error(__('你不能删除自己的账号！'));
        }
        
        $managerService = (new ManagerService);
        $rs = $managerService->delete($id);
        if ($rs === false) {
            $this->error($managerService->getError() ?: __('删除失败！'));
        }
        
        $this->success(__("删除成功！"));
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
            $this->error(__('访问错误！'));
        }
        
        $id = $this->request->param('id/s');
        if (empty($id)) {
            $this->error(__('参数错误！'));
        }
        
        $data = AdminModel::where([
            "id" => $id,
        ])->find();
        if (empty($data)) {
            $this->error(__('信息不存在！'));
        }
        
        $gids = AuthGroupAccessModel::where([
                'admin_id' => $id,
            ])
            ->column('group_id');
        $authGroups = AuthGroupModel::getGroups();
        
        $groups = [];
        foreach ($authGroups as $authGroup) {
            if (in_array($authGroup['id'], $gids)) {
                $groups[] = $authGroup['title'];
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
            
            if (empty($post) || !isset($post['id'])) {
                $this->error(__('没有修改的数据！'));
                return false;
            }
            
            if (empty($post['password'])) {
                $this->error(__('新密码不能为空！'));
            }
            if (empty($post['password_confirm'])) {
                $this->error(__('确认密码不能为空！'));
            }
            
            if ($post['password'] != $post['password_confirm']) {
                $this->error(__('两次密码不一致！'));
            }
            
            if (env('admin_is_root') != 1) {
                if ($post['id'] == env('admin_id')) {
                    $this->error(__('你不能修改自己账号的密码！'));
                }
            }
            
            $managerService = (new ManagerService);
            $rs = $managerService->changePassword($post['id'], $post['password']);
            if ($rs === false) {
                $this->error($ManagerService->getError() ?: __('修改密码失败！'));
            }
            
            $this->success(__("修改密码成功！"));
        } else {
            $id = $this->request->param('id/s');
            $data = AdminModel::where([
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
