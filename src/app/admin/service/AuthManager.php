<?php

namespace app\admin\service;

use think\Db;

use lake\Tree;

use app\admin\service\Auth;

/**
 * 权限管理服务
 *
 * @create 2019-10-13
 * @author deatil
 */
class AuthManager
{
    
    /**
     * 检测权限是否满足当前用户权限
     *
     * @create 2019-10-13
     * @author deatil
     */
    public function checkUserAuth($ruleId, $userAuthIds)
    {
        $isRoot = env('admin_is_root');
        if ($isRoot) {
            return false;
        }
        
        return in_array($ruleId, $userAuthIds) ? false : true;
    }
    
    /**
     * 获取提交的正确权限
     *
     * @create 2019-10-13
     * @author deatil
     */
    public function getUserRightAuth($rules)
    {
        if (empty($rules) || !is_array($rules)) {
            return [];
        }
        
        $uid = env('admin_id');
        
        // 当前用户权限ID列表
        $userAuthIds = (new Auth())->getUserAuthIdList($uid);
        
        $isRoot = env('admin_is_root');
        if ($isRoot) {
            return $rules;
        }
        
        $newRules = [];
        foreach ($rules as $rule) {
            if (in_array($rule, $userAuthIds)) {
                $newRules[] = $rule;
            }
        }
        
        return  $newRules;
    }
    
    /**
     * 检测用户的用户组
     *
     * @create 2019-10-13
     * @author deatil
     */
    public function checkUserGroup($groupId)
    {
        if (empty($groupId)) {
            return [
                'status' => false,
                'msg' => '用户组ID不能为空',
            ];
        }
        
        $Auth = new Auth();
        
        $group = Db::name('auth_group')->where([
            'id' => $groupId,
        ])->find();
        if (empty($group)) {
            return [
                'status' => false,
                'msg' => '角色组不存在',
            ];
        }
        
        $isRoot = env('admin_is_root');
        if ($isRoot) {
            return [
                'status' => true,
                'msg' => '权限通过',
            ];
        }
        
        $adminId = env('admin_id');
        
        // 当前用户组ID列表
        $userGroupIds = $Auth->getUserGroupIdList($adminId);
        if (!in_array($group['parentid'], $userGroupIds)) {
            return [
                'status' => false,
                'msg' => '访问受限',
            ];
        }

        return [
            'status' => true,
            'msg' => '权限通过',
        ];
    }
    
    /**
     * 检测用户组是否属于当前登陆用户的用户组子集
     *
     * @create 2019-10-13
     * @author deatil
     */
    public function checkGroupForUser($groupId)
    {
        if (empty($groupId)) {
            return [
                'status' => false,
                'msg' => '用户组ID不能为空',
            ];
        }
        
        $Auth = new Auth();
        
        $group = Db::name('auth_group')->where([
            'id' => $groupId,
        ])->find();
        if (empty($group)) {
            return [
                'status' => false,
                'msg' => '角色组不存在',
            ];
        }
        
        $isRoot = env('admin_is_root');
        if ($isRoot) {
            return [
                'status' => true,
                'msg' => '权限通过',
            ];
        }
        
        // 用户组列表
        $authGroupList = Db::name('AuthGroup')
            ->where([
                'module' => 'admin',
            ])
            ->order([
                'id' => 'ASC',
            ])
            ->select();
    
        // 当前用户组ID列表
        $userGroupIds = $Auth->getUserGroupIdList(env('admin_id'));
        
        $Tree = new Tree();
        
        $userChildGroupIds = [];
        if (!empty($userGroupIds)) {
            foreach ($userGroupIds as $userGroupId) {
                $getChildGroupIds = $Tree->getChildsId($authGroupList, $userGroupId);
                $userChildGroupIds = array_merge($userChildGroupIds, $getChildGroupIds);
            }
        }
        
        $userChildGroupIds = array_merge($userChildGroupIds, $userGroupIds);
        
        if (!in_array($groupId, $userChildGroupIds)) {
            return [
                'status' => false,
                'msg' => '访问受限',
            ];
        }

        return [
            'status' => true,
            'msg' => '权限通过',
        ];
    }
    
    /**
     * 获取正确的用户组
     *
     * @create 2019-10-14
     * @author deatil
     */
    public function getUserRightGroup($list)
    {
        if (empty($list)) {
            return [];
        }
        
        $isRoot = env('admin_is_root');
        if ($isRoot) {
            return $list;
        }
        
        $Auth = new Auth();
        
        // 用户组列表
        $authGroupList = Db::name('AuthGroup')
            ->where([
                'module' => 'admin',
            ])
            ->order([
                'id' => 'ASC',
            ])
            ->select();
    
        // 当前用户组ID列表
        $userGroupIds = $Auth->getUserGroupIdList(env('admin_id'));
        
        $Tree = new Tree();
        
        $userChildGroupIds = [];
        if (!empty($userGroupIds)) {
            foreach ($userGroupIds as $user_group_id) {
                $getChildGroupIds = $Tree->getChildsId($authGroupList, $user_group_id);
                $userChildGroupIds = array_merge($userChildGroupIds, $getChildGroupIds);
            }
        }
        
        $userChildGroupIds = array_merge($userChildGroupIds, $userGroupIds);
        
        foreach ($list as $k => $v) {
            if (!in_array($v['id'], $userChildGroupIds)) {
                $list[$k]['title'] = '匿名权限组';
                $list[$k]['description'] = '匿名权限组描述';
            }
        }
        
        return  $list;
    }
    
    /**
     * 获取用户的用户组ID列表
     *
     * @create 2019-10-19
     * @author deatil
     */
    public function getUserGroupIds($uid)
    {
        $Auth = new Auth();
        // 当前用户组ID列表
        $userGroupIds = $Auth->getUserGroupIdList($uid);
        return $userGroupIds;
    }
    
    /**
     * 获取用户的父级用户组ID列表
     *
     * @create 2019-10-19
     * @author deatil
     */
    public function getUserParentGroupIds($uid)
    {
        $Auth = new Auth();
        // 当前用户组ID列表
        $userGroupIds = $Auth->getUserGroupIdList($uid);
        $userParentGroupIds = $Auth->getParentGroupIdList($userGroupIds);
        
        return $userParentGroupIds;
    }
    
    /**
     * 获取用户的子级用户组ID列表
     *
     * @create 2019-10-19
     * @author deatil
     */
    public function getUserChildGroupIds($uid)
    {
        if (empty($uid)) {
            return [];
        }
        
        $Auth = new Auth();
        
        // 用户组列表
        $authGroupList = Db::name('AuthGroup')
            ->where([
                'module' => 'admin',
            ])
            ->order([
                'id' => 'ASC',
            ])
            ->select();
    
        // 当前用户组ID列表
        $userGroupIds = $Auth->getUserGroupIdList($uid);
        
        $Tree = new Tree();
        
        $userChildGroupIds = [];
        if (!empty($userGroupIds)) {
            foreach ($userGroupIds as $user_group_id) {
                $getChildGroupIds = $Tree->getChildsId($authGroupList, $user_group_id);
                $userChildGroupIds = array_merge($userChildGroupIds, $getChildGroupIds);
            }
        }
        
        return  $userChildGroupIds;
    }
    
}
