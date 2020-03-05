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
	public function checkUserAuth($rule_id, $user_auth_ids)
	{
		$is_root = env('is_root');
		if ($is_root) {
			return false;
		}
		
		$uid = env('admin_id');
		
		return in_array($rule_id, $user_auth_ids) ? false : true;
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
		$user_auth_ids = (new Auth())->getUserAuthIdList($uid);
		
		$is_root = env('is_root');
		if ($is_root) {
			return $rules;
		}
		
		$new_rules = [];
		foreach ($rules as $rule) {
			if (in_array($rule, $user_auth_ids)) {
				$new_rules[] = $rule;
			}
		}
		
		return  $new_rules;
	}
	
	/**
	 * 检测用户的用户组
	 *
	 * @create 2019-10-13
	 * @author deatil
	 */
	public function checkUserGroup($group_id)
	{
		if (empty($group_id)) {
			return [
				'status' => false,
				'msg' => '用户组ID不能为空',
			];
		}
		
		$Auth = new Auth();
		
		$group = Db::name('auth_group')->where([
			'id' => $group_id,
		])->find();
		if (empty($group)) {
			return [
				'status' => false,
				'msg' => '角色组不存在',
			];
		}
		
		$is_root = env('is_root');
		if ($is_root) {
			return [
				'status' => true,
				'msg' => '权限通过',
			];
		}
		
		// 当前用户组ID列表
		$user_group_ids = $Auth->getUserGroupIdList(env('admin_id'));
		if (!in_array($group['parentid'], $user_group_ids)) {
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
	public function checkGroupForUser($group_id)
	{
		if (empty($group_id)) {
			return [
				'status' => false,
				'msg' => '用户组ID不能为空',
			];
		}
		
		$Auth = new Auth();
		
		$group = Db::name('auth_group')->where([
			'id' => $group_id,
		])->find();
		if (empty($group)) {
			return [
				'status' => false,
				'msg' => '角色组不存在',
			];
		}
		
		$is_root = env('is_root');
		if ($is_root) {
			return [
				'status' => true,
				'msg' => '权限通过',
			];
		}
		
		// 用户组列表
		$auth_group_list = Db::name('AuthGroup')
			->where([
				'module' => 'admin',
			])
			->order([
				'id' => 'ASC',
			])
			->select();		
	
		// 当前用户组ID列表
		$user_group_ids = $Auth->getUserGroupIdList(env('admin_id'));
		
		$Tree = new Tree();
		
		$user_child_group_ids = [];
		if (!empty($user_group_ids)) {
			foreach ($user_group_ids as $user_group_id) {
				$get_child_group_ids = $Tree->getChildsId($auth_group_list, $user_group_id);
				$user_child_group_ids = array_merge($user_child_group_ids, $get_child_group_ids);
			}
		}
		
		$user_child_group_ids = array_merge($user_child_group_ids, $user_group_ids);
		
		if (!in_array($group_id, $user_child_group_ids)) {
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
		
		$is_root = env('is_root');
		if ($is_root) {
			return $list;
		}
		
		$Auth = new Auth();
		
		// 用户组列表
		$auth_group_list = Db::name('AuthGroup')
			->where([
				'module' => 'admin',
			])
			->order([
				'id' => 'ASC',
			])
			->select();		
	
		// 当前用户组ID列表
		$user_group_ids = $Auth->getUserGroupIdList(env('admin_id'));
		
		$Tree = new Tree();
		
		$user_child_group_ids = [];
		if (!empty($user_group_ids)) {
			foreach ($user_group_ids as $user_group_id) {
				$get_child_group_ids = $Tree->getChildsId($auth_group_list, $user_group_id);
				$user_child_group_ids = array_merge($user_child_group_ids, $get_child_group_ids);
			}
		}
		
		$user_child_group_ids = array_merge($user_child_group_ids, $user_group_ids);
		
		foreach ($list as $k => $v) {
			if (!in_array($v['id'], $user_child_group_ids)) {
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
		$user_group_ids = $Auth->getUserGroupIdList($uid);
		return $user_group_ids;
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
		$user_group_ids = $Auth->getUserGroupIdList($uid);
		$user_parent_group_ids = $Auth->getParentGroupIdList($user_group_ids);
		
		return $user_parent_group_ids;
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
		$auth_group_list = Db::name('AuthGroup')
			->where([
				'module' => 'admin',
			])
			->order([
				'id' => 'ASC',
			])
			->select();		
	
		// 当前用户组ID列表
		$user_group_ids = $Auth->getUserGroupIdList($uid);
		
		$Tree = new Tree();
		
		$user_child_group_ids = [];
		if (!empty($user_group_ids)) {
			foreach ($user_group_ids as $user_group_id) {
				$get_child_group_ids = $Tree->getChildsId($auth_group_list, $user_group_id);
				$user_child_group_ids = array_merge($user_child_group_ids, $get_child_group_ids);
			}
		}
		
		return  $user_child_group_ids;
	}
	
}
