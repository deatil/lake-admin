<?php

namespace app\admin\model;

use think\Db;
use think\Model;
use think\facade\Session;

/**
 * 管理员
 *
 * @create 2019-7-9
 * @author deatil
 */
class Admin extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $name = 'admin';
    protected $insert = ['status' => 1];
    
    /**
     * 设置ID信息
     *
     * @create 2019-12-29
     * @author deatil
     */
    protected function setIdAttr($value) {
        return md5(microtime().mt_rand(100000, 999999));
    }
    
    /**
     * 获取格式化时间
     *
     * @create 2019-12-29
     * @author deatil
     */
    public function getLastLoginTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    /**
     * 获取格式化IP
     *
     * @create 2019-12-29
     * @author deatil
     */
    public function getLastLoginIpAttr($value)
    {
        $value = intval($value);
        return long2ip($value);
    }

    /**
     * 用户登录
     * @param string $username 用户名
     * @param string $password (md5值) 密码
     * @return bool|mixed
     */
    public function login($username = '', $password = '')
    {
        $username = trim($username);
        $password = trim($password);
        $userInfo = $this->getUserInfo($username, $password);
        if (false == $userInfo) {
            return false;
        }
        
        $this->autoLogin($userInfo);
        return true;
    }

    /**
     * 自动登录用户
     */
    public function autoLogin($userInfo)
    {
        /* 更新登录信息 */
        $data = [
            'uid' => $userInfo['id'],
            'last_login_time' => time(),
            'last_login_ip' => request()->ip(1),
        ];
        $this->loginStatus($userInfo['id']);
        /* 记录登录SESSION和COOKIES */
        $auth = [
            'uid' => $userInfo['id'],
            'username' => $userInfo['username'],
            'last_login_time' => $userInfo['last_login_time'],
        ];
        Session::set('admin_user_auth', $auth);
        Session::set('admin_user_auth_sign', data_auth_sign($auth));
    }

    /**
     * 创建管理员
     * @param type $data
     * @return boolean
     */
    public function createManager($data)
    {
        if (empty($data)) {
            $this->error = '没有数据！';
            return false;
        }
        
        $data['id'] = md5(microtime().mt_rand(100000, 999999));
        $data['add_time'] = time();
        $data['add_ip'] = request()->ip(1);
        
        $id = $this->allowField(true)->save($data);
        if ($id !== false) {
            if (isset($data['roleid']) && !empty($data['roleid'])) {
                $roles = explode(',', $data['roleid']);
                unset($data['roleid']);
                
                $group_access = [];
                foreach ($roles as $role) {
                    $group_access[] = [
                        'module' => 'admin',
                        'admin_id' => $this->id,
                        'group_id' => $role,
                    ];
                }
                Db::name('auth_group_access')->insertAll($group_access);
            }            
            
            return $id;
        }
        $this->error = '入库失败！';
        return false;
    }

    /**
     * 编辑管理员
     * @param [type] $data [修改数据]
     * @return boolean
     */
    public function editManager($data)
    {
        if (empty($data) || !isset($data['id']) || !is_array($data)) {
            $this->error = '没有修改的数据！';
            return false;
        }
        $info = $this->where([
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
            $data['password'] = md5(trim($data['password']));
            $passwordinfo = $this->encryptPassword($data['password']); 
            $data['encrypt'] = $passwordinfo['encrypt'];
            $data['password'] = $passwordinfo['password'];
        }
        
        if (isset($data['roleid']) && !empty($data['roleid'])) {
            $roleid = $data['roleid'];
            unset($data['roleid']);
        }
        
        /*
        $status = $this->allowField(true)
            ->isUpdate(true)
            ->save($data, [
                'id' => $data['id'],
            ]);
        */
            
        $status = $this
            ->where([
                'id' => $data['id'],
            ])
            ->update($data);
        if ($status === false) {
            $this->error = '管理员信息更新失败！';
            return false;
        }
        
        if (isset($roleid) && !empty($roleid)) {
            $roles = explode(',', $roleid);
            
            Db::name('auth_group_access')->where([
                'module' => 'admin',
                'admin_id' => $data['id'],
            ])->delete();
            
            $group_access = [];
            foreach ($roles as $role) {
                $group_access[] = [
                    'module' => 'admin',
                    'admin_id' => $data['id'],
                    'group_id' => $role,
                ];
            }
            Db::name('auth_group_access')->insertAll($group_access);
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
        
        $status = $this->where([
            'id' => $id,
        ])->delete();
        
        if (false !== $status) {
            Db::name('auth_group_access')->where([
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
     * 获取用户信息
     * @param type $identifier 用户名或者用户ID
     * @return boolean|array
     */
    public function getUserInfo($identifier, $password = null)
    {
        if (empty($identifier)) {
            return false;
        }

        $userInfo = $this->where([
            'id' => $identifier,
        ])->whereOr([
            'username' => $identifier,
        ])->find();
        if (empty($userInfo)) {
            return false;
        }
        
        // 密码验证
        if (!empty($password) 
            && $this->encryptPassword($password, $userInfo['encrypt']) != $userInfo['password']
        ) {
            return false;
        }
        
        return $userInfo;
    }

    /**
     * 更新登录状态信息
     * @param type $id
     * @return type
     */
    public function loginStatus($id)
    {
        $data = [
            'last_login_time' => time(), 
            'last_login_ip' => request()->ip(1)
        ];
        return $this->save($data, ['id' => $id]);
    }

    /**
     * 管理员密码加密
     * @param $password
     * @param $encrypt //传入加密串，在修改密码时做认证
     * @return array/password
     */
    protected function encryptPassword($password, $encrypt = '')
    {
        $pwd = [];
        $pwd['encrypt'] = $encrypt ? $encrypt : get_random_string();
        $pwd['password'] = md5(md5(trim($password) . $pwd['encrypt']) . config("admin_salt"));
        return $encrypt ? $pwd['password'] : $pwd;
    }
    
}
