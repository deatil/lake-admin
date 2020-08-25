<?php

namespace lake\admin\service;

use lake\admin\model\Admin as AdminModel;
use lake\admin\model\AuthGroupAccess as AuthGroupAccessModel;
use lake\admin\facade\Password as PasswordFacade;

/**
 * 管理员
 *
 * @create 2020-8-25
 * @author deatil
 */
class Manager
{
    /**
     * 创建管理员
     * @param array $data
     * @return boolean
     */
    public function createManager($data)
    {
        if (empty($data)) {
            $this->error = '没有数据！';
            return false;
        }
        
        $data['add_time'] = time();
        $data['add_ip'] = request()->ip();
        
        $saveInfo = AdminModel::create($data);
        if ($saveInfo === false) {
            $this->error = '入库失败！';
            return false;
        }
        
        if (isset($data['roleid']) && !empty($data['roleid'])) {
            $roles = explode(',', $data['roleid']);
            unset($data['roleid']);
            
            $groupAccess = [];
            foreach ($roles as $role) {
                $groupAccess[] = [
                    'module' => 'admin',
                    'admin_id' => $saveInfo->id,
                    'group_id' => $role,
                ];
            }
            AuthGroupAccessModel::insertAll($groupAccess);
        }
        
        return $saveInfo;
    }

    /**
     * 编辑管理员
     * @param array $data [修改数据]
     * @return boolean
     */
    public function editManager($data)
    {
        if (empty($data) 
            || !isset($data['id']) 
            || !is_array($data)
        ) {
            $this->error = '没有修改的数据！';
            return false;
        }
        $info = AdminModel::where([
            'id' => $data['id']
        ])->find();
        if (empty($info)) {
            $this->error = '该管理员不存在！';
            return false;
        }
        
        // 密码为空，表示不修改密码
        if (!isset($data['password']) || empty($data['password'])) {
            unset($data['password']);
            unset($data['encrypt']);
        } else {
            // 对密码进行处理
            $passwordinfo = $this->encryptPassword($data['password']); 
            $data['encrypt'] = $passwordinfo['encrypt'];
            $data['password'] = $passwordinfo['password'];
        }
        
        if (isset($data['roleid']) && !empty($data['roleid'])) {
            $roleid = $data['roleid'];
        }
        unset($data['roleid']);
        
        $status = AdminModel::where([
                'id' => $data['id'],
            ])
            ->update($data);
        if ($status === false) {
            $this->error = '管理员信息更新失败！';
            return false;
        }
        
        if (isset($roleid) && !empty($roleid)) {
            $roles = explode(',', $roleid);
            
            AuthGroupAccessModel::where([
                'module' => 'admin',
                'admin_id' => $data['id'],
            ])->delete();
            
            $groupAccess = [];
            foreach ($roles as $role) {
                $groupAccess[] = [
                    'module' => 'admin',
                    'admin_id' => $data['id'],
                    'group_id' => $role,
                ];
            }
            AuthGroupAccessModel::insertAll($groupAccess);
        }
        
        return true;
    }

    /**
     * 删除管理员
     * @param type $id
     * @return boolean
     */
    public function deleteManager($id)
    {
        $id = trim($id);
        if (empty($id)) {
            $this->error = '请指定需要删除的用户ID！';
            return false;
        }
        if ($id == 1) {
            $this->error = '禁止对超级管理员执行该操作！';
            return false;
        }
        
        $status = AdminModel::where([
            'id' => $id,
        ])->delete();
        
        if (false !== $status) {
            AuthGroupAccessModel::where([
                'module' => 'admin',
                'admin_id' => $id,
            ])->delete();
            
            return true;
        } else {
            $this->error = '删除失败！';
            return false;
        }
    }
    
    /**
     * 管理员密码加密
     * @param $password
     * @param $encrypt //传入加密串，在修改密码时做认证
     * @return array/password
     */
    protected function encryptPassword($password, $encrypt = '')
    {
        $pwd = PasswordFacade::setSalt(config("app.admin_salt"))->encrypt($password, $encrypt);
        return $pwd;
    }
    
    /**
     * 获取错误信息
     * @access public
     * @return mixed
     *
     * @create 2019-7-9
     * @author deatil
     */
    public function getError()
    {
        return $this->error;
    }
    
}
