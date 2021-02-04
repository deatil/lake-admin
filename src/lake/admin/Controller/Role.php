<?php

namespace Lake\Admin\Controller;

use Lake\TTree;

use Lake\Admin\Model\AuthGroup as AuthGroupModel;
use Lake\Admin\Model\AuthRuleAccess as AuthRuleAccessModel;

use Lake\Admin\Service\AuthRule as AuthRuleService;
use Lake\Admin\Service\AuthManager as AuthManagerService;

/**
 * 角色
 *
 * @create 2020-3-15
 * @author deatil
 */
class Role extends Base
{
    // 分组模型
    protected $AuthGroupModel;
    
    // 服务
    protected $AuthManagerService;

    /**
     * 框架构造函数
     *
     * @create 2019-8-5
     * @author deatil
     */
    protected function initialize()
    {
        parent::initialize();
        
        $this->AuthGroupModel = new AuthGroupModel;
        $this->AuthManagerService = new AuthManagerService;
    }

    /**
     * 权限管理首页
     *
     * @create 2019-7-7
     * @author deatil
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $limit = $this->request->param('limit/d', 10);
            $page = $this->request->param('page/d', 1);
            
            $map = $this->buildparams();
            
            $list = AuthGroupModel::where($map)
                ->page($page, $limit)
                ->order([
                    'add_time' => 'ASC',
                ])
                ->select()
                ->toArray();
            $total = AuthGroupModel::where($map)
                ->count();
            
            $result = [];
            if (empty($map)) {
                $TTree = new TTree();
                $TTree->withData($list);
                $result = [];
                
                if (!env('admin_is_root')) {
                    $userGroupIds = $this->AuthManagerService->getUserGroupIds(env('admin_id'));
                    $data = [];
                    if (!empty($userGroupIds)) {
                        foreach ($userGroupIds as $userGroupId) {
                            $data2 = $TTree->buildArray($userGroupId);
                            $data = array_merge($data, $data2);
                        }
                    }
                } else {
                    $data = $TTree->buildArray(0);
                }
                
                if (!empty($data)) {
                    $result = $TTree->buildFormatList($data, 'title');
                }
            } else {
                $result = $list;
            }
            
            $result = [
                "code" => 0, 
                "count" => $total, 
                "data" => $result,
            ];
            
            return $this->json($result);
        } else {
            return $this->fetch();
        }
    }

    /**
     * 添加管理员角色
     *
     * @create 2019-7-7
     * @author deatil
     */
    public function create()
    {
        if (!$this->request->isGet()) {
            $this->error(__('请求错误！'));
        }
        
        // 清除编辑权限的值
        $this->assign('auth_group', [
            'title' => null, 
            'id' => null, 
            'description' => null, 
            'rules' => null, 
            'status' => 1,
        ]);
        
        $TTree = new TTree();
        $list = AuthGroupModel::order(['id' => 'ASC'])
            ->column('*', 'id');
        
        $TTree->withData($list);
        if (env('admin_is_root')) {
            $groupData = $TTree->buildArray(0);
        } else {
            $groupData = $TTree->buildArray(env('admin_id'));
        }
        $groupData = $TTree->buildFormatList($groupData, 'title');
        
        $this->assign("group_data", $groupData);
        
        return $this->fetch();
    }
    
    /**
     * 管理员角色数据写入
     *
     * @create 2019-7-7
     * @author deatil
     */
    public function write()
    {
        if (!$this->request->isPost()) {
            $this->error(__('请求错误！'));
        }
        
        $data = $this->request->post();
        if (empty($data['parentid'])) {
            $this->error(__('父角色组不能为空'));
        }
        
        $check = $this->AuthManagerService->checkGroupForUser($data['parentid']);
        if ($check['status'] === false) {
            $this->error($check['msg']);
        }
        
        $data['type'] = AuthGroupModel::TYPE_ADMIN;
        
        $result = $this->validate($data, 'Lake\\Admin\\Validate\\AuthGroup');
        if (true !== $result) {
            return $this->error($result);
        }
        
        $r = $this->AuthGroupModel->save($data);
        
        if ($r === false) {
            $this->error(__('操作失败！') . $this->AuthGroupModel->getError());
        }
        
        $this->success(__('操作成功！'));
    }
    
    /**
     * 编辑管理员角色
     *
     * @create 2019-7-7
     * @author deatil
     */
    public function edit()
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error(__('角色组不存在！'));
        }
        
        $authGroup = AuthGroupModel::where([
                'type' => AuthGroupModel::TYPE_ADMIN,
            ])
            ->find($id);
        if (empty($authGroup)) {
            $this->error(__('角色组不存在！'));
        }
        
        if ($authGroup['is_system'] == 1) {
            $this->error(__('系统默认角色不可操作！'));
        }
    
        $check = $this->AuthManagerService->checkUserGroup($id);
        if ($check['status'] === false) {
            $this->error($check['msg']);
        }
        
        $TTree = new TTree();
        
        $list = AuthGroupModel::order([
                'id' => 'ASC',
            ])
            ->column('*', 'id');
            
        $childsId = $TTree->getListChildsId($list, $authGroup['id']);
        $childsId[] = $authGroup['id'];
        
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                if (in_array($val['id'], $childsId)) {
                    unset($list[$key]);
                }
            }
        }
        
        $TTree->withData($list);
        if (env('admin_is_root')) {
            $groupData = $TTree->buildArray(0);
        } else {
            $groupData = $TTree->buildArray(env('admin_id'));
        }
        $groupData = $TTree->buildFormatList($groupData, 'title');
        
        $this->assign("group_data", $groupData);
        $this->assign('auth_group', $authGroup);
        
        return $this->fetch();
    }
    
    /**
     * 数据更新
     *
     * @create 2020-7-26
     * @author deatil
     */
    public function update()
    {
        if (!$this->request->isPost()) {
            $this->error(__('请求错误！'));
        }
        
        $data = $this->request->post();
        if (empty($data['parentid'])) {
            $this->error(__('父角色组不能为空'));
        }
        
        $check = $this->AuthManagerService->checkGroupForUser($data['parentid']);
        if ($check['status'] === false) {
            $this->error($check['msg']);
        }
        
        $data['type'] = AuthGroupModel::TYPE_ADMIN;
        
        if (!isset($data['id']) || empty($data['id'])) {
            $this->error(__('角色组ID不存在！'));
        }
        
        $authGroup = AuthGroupModel::where([
                'type' => AuthGroupModel::TYPE_ADMIN,
            ])
            ->find($data['id']);
        if (empty($authGroup)) {
            $this->error(__('角色组不存在！'));
        }
        
        if ($authGroup['is_system'] == 1) {
            $this->error(__('系统默认角色不可操作！'));
        }
        
        $check = $this->AuthManagerService->checkUserGroup($data['id']);
        if ($check['status'] === false) {
            $this->error($check['msg']);
        }
    
        // 更新
        $r = $this->AuthGroupModel
            ->where([
                'id' => $data['id'],
            ])
            ->update($data);
        
        if ($r === false) {
            $this->error(__('操作失败：') . $this->AuthGroupModel->getError());
        }
        
        $this->success(__('操作成功！'));
    }
    
    /**
     * 删除管理员角色
     *
     * @create 2019-7-7
     * @author deatil
     */
    public function delete()
    {
        if (!$this->request->isPost()) {
            $this->error(__('请求错误！'));
        }
        
        $groupId = $this->request->param('id');
        if (empty($groupId)) {
            $this->error(__('角色组不存在！'));
        }
        
        $authGroup = AuthGroupModel::where([
                'type' => AuthGroupModel::TYPE_ADMIN,
                'id' => $groupId,
            ])
            ->find();
        if (empty($authGroup)) {
            $this->error(__('角色组不存在！'));
        }
        
        if ($authGroup['is_system'] == 1) {
            $this->error(__('系统默认角色不可操作！'));
        }
        
        $check = $this->AuthManagerService->checkUserGroup($groupId);
        if ($check['status'] === false) {
            $this->error($check['msg']);
        }
        
        // 子角色检测
        $childGroupCount = AuthGroupModel::where([
                ['parentid', '=', $groupId],
            ])
            ->count();
        if ($childGroupCount > 0) {
            $this->error(__('删除失败，请删除子角色后再删除！'));
        }
        
        $rs = AuthGroupModel::groupDelete($groupId);
        
        if ($rs === false) {
            $this->error(__('删除失败！'));
        }
        
        $this->success(__("删除成功！"));
    }
    
    /**
     * 访问授权页面
     *
     * @create 2019-7-7
     * @author deatil
     */
    public function access()
    {
        if ($this->request->isPost()) {
            $groupId = $this->request->post('id');
            if (empty($groupId)) {
                $this->error(__('角色组不存在！'));
            }
        
            $authGroup = AuthGroupModel::where([
                    'type' => AuthGroupModel::TYPE_ADMIN,
                    'id' => $groupId,
                ])
                ->find();
            if (empty($authGroup)) {
                $this->error(__('角色组不存在！'));
            }
            
            $check = $this->AuthManagerService->checkGroupForUser($authGroup['parentid']);
            if ($check['status'] === false) {
                $this->error($check['msg']);
            }
            
            $newRules = $this->request->post('rules');
            
            $rules = [];
            if (!empty($newRules)) {
                $rules = explode(',', $newRules);
            }
            
            // 获取提交的正确权限
            $rules = $this->AuthManagerService->getUserRightAuth($rules);
            
            if ($authGroup['is_system'] == 1) {
                $this->error(__('系统默认角色不可操作！'));
            }
            
            $check = $this->AuthManagerService->checkUserGroup($groupId);
            if ($check['status'] === false) {
                $this->error($check['msg']);
            }
            
            // 删除权限
            AuthRuleAccessModel::where([
                'group_id' => $groupId,
            ])->delete();
            
            // 有权限就添加
            if (isset($rules) && !empty($rules)) {
                $ruleAccess = [];
                if (!empty($rules)) {
                    foreach ($rules as $rule) {
                        $ruleAccess[] = [
                            'group_id' => $groupId,
                            'rule_id' => $rule,
                        ];
                    }
                }
                
                $r = AuthRuleAccessModel::insertAll($ruleAccess);
            
                if ($r === false) {
                    $this->error(__('授权失败！'));
                }
            }
            
            $this->success(__('授权成功！'));
        } else {
            $groupId = $this->request->param('group_id');
            if (empty($groupId)) {
                $this->error(__('角色组ID不能为空！'));
            }
            
            $check = $this->AuthManagerService->checkUserGroup($groupId);
            if ($check['status'] === false) {
                $this->error($check['msg']);
            }
            
            $rules = AuthGroupModel::withJoin(['ruleAccess'])
                ->where([
                    'id' => $groupId,
                    'type' => AuthGroupModel::TYPE_ADMIN,
                ])
                ->visible([
                    'ruleAccess' => [
                        'rule_id',
                    ]
                ])
                ->column('ruleAccess.rule_id');
            $this->assign('rules', $rules);
            
            // 当前用户权限ID列表
            $userAuthIds = (new AuthManagerService)->getAuthIdList(env('admin_id'));
        
            $result = (new AuthRuleService)->returnNodes(false);
            
            $json = [];
            if (!empty($result)) {
                foreach ($result as $rs) {
                    $data = [
                        'id' => $rs['id'],
                        'parentid' => $rs['parentid'],
                        'title' => (empty($rs['method']) ? $rs['title'] : ($rs['title'] . '[' . strtoupper($rs['method']) . ']')),
                        // 'checked' => in_array($rs['id'], $rules) ? true : false,
                        'field' => 'roleid',
                        'spread' => false,
                    ];
                    $json[] = $data;
                }
            }
            
            $json = (new TTree)->withConfig('buildChildKey', 'children')->withData($json)->buildArray(0);
            
            $this->assign('group_id', $groupId);
            $this->assign('json', $json);
            
            $authGroup = AuthGroupModel::where([
                'type' => AuthGroupModel::TYPE_ADMIN,
                'id' => $groupId,
            ])->find();
            $this->assign('auth_group', $authGroup);
            
            return $this->fetch('access');
        }
    }

    /**
     * 菜单排序
     *
     * @create 2020-7-26
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
        
        $rs = AuthGroupModel::update([
            'listorder' => $listorder,
        ], [
            'id' => $id,
        ]);
        if ($rs === false) {
            $this->error(__("排序失败！"));
        }
        
        $this->success(__("排序成功！"));
    }
    
}
