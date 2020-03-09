<?php

namespace app\admin\Controller;

use think\Db;
use think\facade\Hook;

use lake\Tree;

use app\admin\service\Auth;
use app\admin\service\AuthManager as AuthManagerService;
use app\admin\model\AuthGroup as AuthGroupModel;
use app\admin\model\AuthRule as AuthRuleModel;

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
            
            $_list = Db::name('AuthGroup')
                ->where([
                    'module' => 'admin',
                ])
                ->where($map)
                ->page($page, $limit)
                ->order([
                    'id' => 'ASC',
                ])
                ->select();
            $total = Db::name('AuthGroup')
                ->where([
                    'module' => 'admin',
                ])
                ->where($map)
                ->count();
        
            $result = [];
            if (empty($map)) {
                $tree = new Tree();
                $tree->init($_list);
                $result = [];
                
                if (!env('is_root')) {
                    $user_group_ids = $this->AuthManagerService->getUserGroupIds(env('admin_id'));
                    $data = [];
                    if (!empty($user_group_ids)) {
                        foreach ($user_group_ids as $user_group_id) {
                            $data2 = $tree->getTreeArray($user_group_id);
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
            
            Hook::listen('AuthManagerIndexAjax', $result);    
            
            return json($result);
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
    public function createGroup()
    {
        if (!$this->request->isGet()) {
            $this->error('请求错误！');
        }
        
        // 清除编辑权限的值
        $this->assign('auth_group', [
            'title' => null, 
            'id' => null, 
            'description' => null, 
            'rules' => null, 
            'status' => 1,
        ]);
        
        $tree = new Tree();
        $str = "'<option value='\$id' \$selected>\$spacer\$title</option>";
        $_list = Db::name('AuthGroup')
            ->where('module', 'admin')
            ->order(['id' => 'ASC'])
            ->column('*', 'id');
        
        Hook::listen('AuthManagerCreateGroup', $_list);
        
        $tree->init($_list);
        
        if (!env('is_root')) {
            $user_parent_group_ids = $this->AuthManagerService->getUserParentGroupIds(env('admin_id'));
            $group_data = '';
            if (!empty($user_parent_group_ids)) {
                foreach ($user_parent_group_ids as $user_parent_group_id) {
                    $group_data .= $tree->get_tree($user_parent_group_id, $str, 0);
                }
            }
        } else {
            $group_data = $tree->get_tree(0, $str, 0);
        }
        
        $this->assign("group_data", $group_data);
        
        return $this->fetch('edit_group');

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
        
        $auth_group = Db::name('AuthGroup')
            ->where([
                'module' => 'admin', 
                'type' => AuthGroupModel::TYPE_ADMIN,
            ])
            ->find($id);
        if (empty($auth_group)) {
            $this->error('角色组不存在！');
        }
        
        if ($auth_group['is_system'] == 1) {
            $this->error('系统默认角色不可操作！');
        }
    
        $check = $this->AuthManagerService->checkUserGroup($id);
        if ($check['status'] === false) {
            $this->error($check['msg']);
        }
    
        $tree = new Tree();
        
        $str = "'<option value='\$id' \$selected>\$spacer\$title</option>";
        $_list = Db::name('AuthGroup')
            ->where('module', 'admin')
            ->order([
                'id' => 'ASC',
            ])
            ->column('*', 'id');
            
        $childsId = $tree->getChildsId($_list, $auth_group['id']);
        $childsId[] = $auth_group['id'];
        
        Hook::listen('AuthManagerEditGroup', $_list);
        
        if (!empty($_list)) {
            foreach ($_list as $key => $val) {
                if (in_array($val['id'], $childsId)) {
                    unset($_list[$key]);
                }
            }
        }

        $tree->init($_list);
        
        if (!env('is_root')) {
            $user_parent_group_ids = $this->AuthManagerService->getUserParentGroupIds(env('admin_id'));
            $group_data = '';
            if (!empty($user_parent_group_ids)) {
                foreach ($user_parent_group_ids as $user_parent_group_id) {
                    $group_data .= $tree->get_tree($user_parent_group_id, $str, $auth_group['parentid']);
                }
            }
        } else {
            $group_data = $tree->get_tree(0, $str, $auth_group['parentid']);
        }
        
        $this->assign("group_data", $group_data);
        $this->assign('auth_group', $auth_group);
        
        return $this->fetch();
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
        
        Hook::listen('AuthManagerWriteGroup', $data);
        
        $rules = [];
        if (isset($data['rules']) && !empty($data['rules'])) {
            $rules = explode(',', $data['rules']);
            unset($data['rules']);
        }
        
        // 监听权限
        Hook::listen('AuthManagerWriteGroupRules', $rules);
        
        // 获取提交的正确权限
        $rules = $this->AuthManagerService->getUserRightAuth($rules);
        
        if (isset($data['id']) && !empty($data['id'])) {
            $auth_group = Db::name('AuthGroup')
                ->where([
                    'module' => 'admin', 
                    'type' => AuthGroupModel::TYPE_ADMIN,
                ])
                ->find($data['id']);
            if (empty($auth_group)) {
                $this->error('角色组不存在！');
            }
            
            if ($auth_group['is_system'] == 1) {
                $this->error('系统默认角色不可操作！');
            }
            
            $check = $this->AuthManagerService->checkUserGroup($data['id']);
            if ($check['status'] === false) {
                $this->error($check['msg']);
            }
        
            // 更新
            $r = $this->AuthGroupModel
                ->allowField(true)
                ->save($data, [
                    'id' => $data['id'],
                ]);
            
            if (isset($rules) && !empty($rules)) {
                Db::name('auth_rule_access')->where([
                    'module' => 'admin',
                    'group_id' => $data['id'],
                ])->delete();
                
                $rule_access = [];
                if (!empty($rules)) {
                    foreach ($rules as $rule) {
                        $rule_access[] = [
                            'module' => 'admin',
                            'group_id' => $data['id'],
                            'rule_id' => $rule,
                        ];
                    }
                }
                
                Hook::listen('AuthManagerWriteUpdateGroup', $rule_access);
                
                Db::name('auth_rule_access')->insertAll($rule_access);
            }
            
        } else {
            $result = $this->validate($data, 'AuthGroup');
            if (true !== $result) {
                return $this->error($result);
            }
            
            $data['add_time'] = time();
            $data['add_ip'] = request()->ip(1);
            
            $r = $this->AuthGroupModel->allowField(true)->save($data);
        
            if (isset($rules) && !empty($rules)) {
                $rule_access = [];
                foreach ($rules as $rule) {
                    $rule_access[] = [
                        'module' => 'admin',
                        'group_id' => $this->id,
                        'rule_id' => $rule,
                    ];
                }
                
                Hook::listen('AuthManagerWriteInsertGroup', $rule_access);
                
                Db::name('auth_rule_access')->insertAll($rule_access);
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
        
        $group_id = $this->request->param('id');
        if (empty($group_id)) {
            $this->error('角色组不存在！');
        }
        
        $auth_group = Db::name('AuthGroup')
            ->where([
                'module' => 'admin', 
                'type' => AuthGroupModel::TYPE_ADMIN,
            ])
            ->find($id);
        if (empty($auth_group)) {
            $this->error('角色组不存在');
        }
        
        if ($auth_group['is_system'] == 1) {
            $this->error('系统默认角色不可操作！');
        }
        
        $check = $this->AuthManagerService->checkUserGroup($group_id);
        if ($check['status'] === false) {
            $this->error($check['msg']);
        }
        
        Hook::listen('AuthManagerDeleteGroup', $group_id);
        
        $rs = $this->AuthGroupModel->GroupDelete($group_id);
        
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
        $group_id = $this->request->param('group_id');
        if (empty($group_id)) {
            $this->error('角色组ID不能为空');
        }
        
        $check = $this->AuthManagerService->checkUserGroup($group_id);
        if ($check['status'] === false) {
            $this->error($check['msg']);
        }
        
        $rules = Db::name('AuthGroup')
            ->alias('ag')
            ->leftJoin('__AUTH_RULE_ACCESS__ ara ', 'ara.group_id = ag.id')
            ->where('ag.status', '<>', 0)
            ->where('ag.id', '=', $group_id)
            ->where([
                'ag.type' => AuthGroupModel::TYPE_ADMIN
            ])
            ->column('ara.rule_id');
            
        // 监听权限
        Hook::listen('AuthManagerAccessRules', [
            'group_id' => $group_id,
            'rules' => $rules,
        ]);
        
        // 当前用户权限ID列表
        $user_auth_ids = (new Auth())->getUserAuthIdList(env('admin_id'));
    
        $result = model('admin/AuthRule')->returnNodes(false);
        
        $json = [];
        if (!empty($result)) {
            foreach ($result as $rs) {
                $data = [
                    'nid' => $rs['id'],
                    'parentid' => $rs['parentid'],
                    'name' => $rs['title'],
                    'id' => $rs['id'],
                    'chkDisabled' => $this->AuthManagerService->checkUserAuth($rs['id'], $user_auth_ids),
                    'checked' => in_array($rs['id'], $rules) ? true : false,
                ];
                $json[] = $data;
            }
        }
        
        Hook::listen('AuthManagerAccess', $json);
        
        $this->assign('group_id', $group_id);
        $this->assign('json', json_encode($json));
        
        $auth_group = Db::name('AuthGroup')->where([
            'module' => 'admin', 
            'type' => AuthGroupModel::TYPE_ADMIN,
        ])->find($group_id);
        $this->assign('auth_group', $auth_group);
        
        return $this->fetch('access');
    }
    
}
