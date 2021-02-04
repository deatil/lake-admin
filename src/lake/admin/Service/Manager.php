<?php

namespace Lake\Admin\Service;

use Lake\Admin\Model\Admin as AdminModel;
use Lake\Admin\Model\AuthGroupAccess as AuthGroupAccessModel;
use Lake\Admin\Facade\Password as PasswordFacade;

/**
 * 管理员
 *
 * @create 2020-8-25
 * @author deatil
 */
class Manager
{
    protected $error = '操作失败！';
    
    /**
     * 创建管理员
     * @param array $data
     * @return boolean
     */
    public function create($data)
    {
        if (empty($data)) {
            $this->error = '没有数据！';
            return false;
        }
        
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
    public function edit($data)
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
        
        // 密码不能被修改
        unset($data['password']);
        unset($data['encrypt']);
        
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
                'admin_id' => $data['id'],
            ])->delete();
            
            $groupAccess = [];
            foreach ($roles as $role) {
                $groupAccess[] = [
                    'admin_id' => $data['id'],
                    'group_id' => $role,
                ];
            }
            AuthGroupAccessModel::insertAll($groupAccess);
        }
        
        return true;
    }

    /**
     * 修改密码
     * @param string $id 账号ID
     * @param string $password 密码
     * @return boolean
     */
    public function changePassword($id, $password)
    {
        if (empty($id)) {
            $this->error = 'ID不能为空！';
            return false;
        }
        
        if (empty($password)) {
            $this->error = '密码不能为空！';
            return false;
        }
        
        $data = [];
        
        // 对密码进行处理
        $passwordinfo = $this->encryptPassword($password); 
        $data['encrypt'] = $passwordinfo['encrypt'];
        $data['password'] = $passwordinfo['password'];
        
        $status = AdminModel::where([
                'id' => $id,
            ])
            ->update($data);
        if ($status === false) {
            $this->error = '密码修改失败！';
            return false;
        }
        
        return true;
    }

    /**
     * 删除管理员
     * @param type $id
     * @return boolean
     */
    public function delete($id)
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
