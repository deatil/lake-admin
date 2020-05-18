<?php

namespace app\admin\Controller;

use think\facade\Db;
use think\facade\View;
use think\facade\Event;

use lake\Tree;

use app\admin\model\AuthGroup as AuthGroupModel;
use app\admin\model\AuthRule as AuthRuleModel;

use app\admin\service\Auth as AuthService;
use app\admin\service\AuthManager as AuthManagerService;

/**
 * 权限管理控制器
 *
 * @create 2019-7-7
 * @author deatil
 */
class AuthManager extends Base
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
            
            $list = Db::name('auth_group')
                ->where([
                    'module' => 'admin',
                ])
                ->where($map)
                ->page($page, $limit)
                ->order([
                    'add_time' => 'ASC',
                ])
                ->select()
                ->toArray();
            $total = Db::name('auth_group')
                ->where([
                    'module' => 'admin',
                ])
                ->where($map)
                ->count();
            
            $result = [];
            if (empty($map)) {
                $tree = new Tree();
                $tree->init($list);
                $result = [];
                
                if (!env('admin_is_root')) {
                    $userGroupIds = $this->AuthManagerService->getUserGroupIds(env('admin_id'));
                    $data = [];
                    if (!empty($userGroupIds)) {
                        foreach ($userGroupIds as $userGroupId) {
                            $data2 = $tree->getTreeArray($userGroupId);
                            $data = array_merge($data, $data2);
                        }
                    }
                } else {
                    $data = $tree->getTreeArray(0);
                }
                
                if (!empty($data)) {
                    $result = $tree->getTreeList($data, 'title');
                }
            }
            
            $result = [
                "code" => 0, 
                "count" => $total, 
                "data" => $result,
            ];
            
            Event::trigger('AuthManagerIndexAjax', $result);
            
            return json($result);
        } else {
            return View::fetch();
        }
    }

    /**
     * 添加管理员角色
     *
     * @create 2019-7-7
     * @author deatil
     */
    public function createGroup()
    {
        if (!$this->request->isGet()) {
            $this->error('请求错误！');
        }
        
        // 清除编辑权限的值
        View::assign('auth_group', [
            'title' => null, 
            'id' => null, 
            'description' => null, 
            'rules' => null, 
            'status' => 1,
        ]);
        
        $tree = new Tree();
        $str = "'<option value='\$id' \$selected>\$spacer\$title</option>";
        $list = Db::name('AuthGroup')
            ->where('module', 'admin')
            ->order(['id' => 'ASC'])
            ->column('*', 'id');
        
        Event::trigger('AuthManagerCreateGroup', $list);
        
        $tree->init($list);
        
        if (!env('admin_is_root')) {
            $userParentGroupIds = $this->AuthManagerService->getUserParentGroupIds(env('admin_id'));
            $groupData = '';
            if (!empty($userParentGroupIds)) {
                foreach ($userParentGroupIds as $userParentGroupId) {
                    $groupData .= $tree->getTree($userParentGroupId, $str, 0);
                }
            }
        } else {
            $groupData = $tree->getTree(0, $str, 0);
        }
        
        View::assign("group_data", $groupData);
        
        return View::fetch('edit_group');

    }

    /**
     * 编辑管理员角色
     *
     * @create 2019-7-7
     * @author deatil
     */
    public function editGroup()
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('角色组不存在！');
        }
        
        $authGroup = Db::name('AuthGroup')
            ->where([
                'module' => 'admin', 
                'type' => AuthGroupModel::TYPE_ADMIN,
            ])
            ->find($id);
        if (empty($authGroup)) {
            $this->error('角色组不存在！');
        }
        
        if ($authGroup['is_system'] == 1) {
            $this->error('系统默认角色不可操作！');
        }
    
        $check = $this->AuthManagerService->checkUserGroup($id);
        if ($check['status'] === false) {
            $this->error($check['msg']);
        }
    
        $tree = new Tree();
        
        $str = "'<option value='\$id' \$selected>\$spacer\$title</option>";
        $list = Db::name('AuthGroup')
            ->where('module', 'admin')
            ->order([
                'id' => 'ASC',
            ])
            ->column('*', 'id');
            
        $childsId = $tree->getChildsId($list, $authGroup['id']);
        $childsId[] = $authGroup['id'];
        
        Event::trigger('AuthManagerEditGroup', $list);
        
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                if (in_array($val['id'], $childsId)) {
                    unset($list[$key]);
                }
            }
        }
        
        $tree->init($list);
        
        if (!env('admin_is_root')) {
            $userParentGroupIds = $this->AuthManagerService->getUserParentGroupIds(env('admin_id'));
            $groupData = '';
            if (!empty($userParentGroupIds)) {
                foreach ($userParentGroupIds as $userParentGroupId) {
                    $groupData .= $tree->getTree($userParentGroupIds, $str, $authGroup['parentid']);
                }
            }
        } else {
            $groupData = $tree->getTree(0, $str, $authGroup['parentid']);
        }
        
        View::assign("group_data", $groupData);
        View::assign('auth_group', $authGroup);
        
        return View::fetch();
    }
    
    /**
     * 管理员角色数据写入/更新
     *
     * @create 2019-7-7
     * @author deatil
     */
    public function writeGroup()
    {
        if (!$this->request->isPost()) {
            $this->error('请求错误！');
        }
        
        $data = $this->request->post();
        if (empty($data['parentid'])) {
            $this->error('父角色组不能为空');
        }
        
        $check = $this->AuthManagerService->checkGroupForUser($data['parentid']);
        if ($check['status'] === false) {
            $this->error($check['msg']);
        }
        
        $data['module'] = 'admin';
        $data['type'] = AuthGroupModel::TYPE_ADMIN;
        
        Event::trigger('AuthManagerWriteGroup', $data);
        
        $rules = [];
        if (isset($data['rules']) && !empty($data['rules'])) {
            $rules = explode(',', $data['rules']);
            unset($data['rules']);
        }
        
        // 监听权限
        Event::trigger('AuthManagerWriteGroupRules', $rules);
        
        // 获取提交的正确权限
        $rules = $this->AuthManagerService->getUserRightAuth($rules);
        
        if (isset($data['id']) && !empty($data['id'])) {
            $authGroup = Db::name('AuthGroup')
                ->where([
                    'module' => 'admin', 
                    'type' => AuthGroupModel::TYPE_ADMIN,
                ])
                ->find($data['id']);
            if (empty($authGroup)) {
                $this->error('角色组不存在！');
            }
            
            if ($authGroup['is_system'] == 1) {
                $this->error('系统默认角色不可操作！');
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
            
            // 删除权限
            Db::name('auth_rule_access')->where([
                'module' => 'admin',
                'group_id' => $data['id'],
            ])->delete();
            
            // 有权限就添加
            if (isset($rules) && !empty($rules)) {
                $ruleAccess = [];
                if (!empty($rules)) {
                    foreach ($rules as $rule) {
                        $ruleAccess[] = [
                            'module' => 'admin',
                            'group_id' => $data['id'],
                            'rule_id' => $rule,
                        ];
                    }
                }
                
                Event::trigger('AuthManagerWriteUpdateGroup', $ruleAccess);
                
                Db::name('auth_rule_access')->insertAll($ruleAccess);
            }
            
        } else {
            $result = $this->validate($data, 'AuthGroup');
            if (true !== $result) {
                return $this->error($result);
            }
            
            $data['id'] = md5(microtime().mt_rand(100000, 999999));
            $data['add_time'] = time();
            $data['add_ip'] = request()->ip(1);
            
            $r = $this->AuthGroupModel->save($data);
        
            if (isset($rules) && !empty($rules)) {
                $ruleAccess = [];
                foreach ($rules as $rule) {
                    $ruleAccess[] = [
                        'module' => 'admin',
                        'group_id' => $this->id,
                        'rule_id' => $rule,
                    ];
                }
                
                Event::trigger('AuthManagerWriteInsertGroup', $ruleAccess);
                
                Db::name('auth_rule_access')->insertAll($ruleAccess);
            }
        }
        
        if ($r === false) {
            $this->error('操作失败' . $this->AuthGroupModel->getError());
        }
        
        $this->success('操作成功!');
    }
    
    /**
     * 删除管理员角色
     *
     * @create 2019-7-7
     * @author deatil
     */
    public function deleteGroup()
    {
        if (!$this->request->isPost()) {
            $this->error('请求错误！');
        }
        
        $groupId = $this->request->param('id');
        if (empty($groupId)) {
            $this->error('角色组不存在！');
        }
        
        $authGroup = Db::name('AuthGroup')
            ->where([
                'module' => 'admin', 
                'type' => AuthGroupModel::TYPE_ADMIN,
                'id' => $groupId,
            ])
            ->find();
        if (empty($authGroup)) {
            $this->error('角色组不存在');
        }
        
        if ($authGroup['is_system'] == 1) {
            $this->error('系统默认角色不可操作！');
        }
        
        $check = $this->AuthManagerService->checkUserGroup($groupId);
        if ($check['status'] === false) {
            $this->error($check['msg']);
        }
        
        Event::trigger('AuthManagerDeleteGroup', $groupId);
        
        $rs = $this->AuthGroupModel->GroupDelete($groupId);
        
        if ($rs === false) {
            $error = $this->AuthGroupModel->getError();
            $this->error($error ? $error : '删除失败！');
        }
        
        $this->success("删除成功！");
    }
    
    /**
     * 访问授权页面
     *
     * @create 2019-7-7
     * @author deatil
     */
    public function access()
    {
        $groupId = $this->request->param('group_id');
        if (empty($groupId)) {
            $this->error('角色组ID不能为空');
        }
        
        $check = $this->AuthManagerService->checkUserGroup($groupId);
        if ($check['status'] === false) {
            $this->error($check['msg']);
        }
        
        $rules = Db::name('AuthGroup')
            ->alias('ag')
            ->leftJoin('auth_rule_access ara ', 'ara.group_id = ag.id')
            ->where([
                'ag.id' => $groupId,
                'ag.type' => AuthGroupModel::TYPE_ADMIN,
            ])
            ->column('ara.rule_id');
            
        // 监听权限
        Event::trigger('AuthManagerAccessRules', [
            'group_id' => $groupId,
            'rules' => $rules,
        ]);
        
        // 当前用户权限ID列表
        $userAuthIds = (new AuthService())->getUserAuthIdList(env('admin_id'));
    
        $result = (new AuthRuleModel)->returnNodes(false);
        
        $json = [];
        if (!empty($result)) {
            foreach ($result as $rs) {
                $data = [
                    'nid' => $rs['id'],
                    'parentid' => $rs['parentid'],
                    'name' => $rs['title'],
                    'id' => $rs['id'],
                    'chkDisabled' => $this->AuthManagerService->checkUserAuth($rs['id'], $userAuthIds),
                    'checked' => in_array($rs['id'], $rules) ? true : false,
                ];
                $json[] = $data;
            }
        }
        
        Event::trigger('AuthManagerAccess', $json);
        
        View::assign('group_id', $groupId);
        View::assign('json', json_encode($json));
        
        $authGroup = Db::name('AuthGroup')->where([
            'module' => 'admin', 
            'type' => AuthGroupModel::TYPE_ADMIN,
            'id' => $groupId,
        ])->find();
        View::assign('auth_group', $authGroup);
        
        return View::fetch('access');
    }
    
}
