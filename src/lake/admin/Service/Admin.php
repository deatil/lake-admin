<?php

namespace Lake\Admin\Service;

use think\facade\Session;
use think\facade\Config;

use Lake\Arr;

use Lake\Admin\Model\Admin as AdminModel;
use Lake\Admin\Model\AuthGroup as AuthGroupModel;
use Lake\Admin\Model\AuthGroupAccess as AuthGroupAccessModel;
use Lake\Admin\Facade\Password as PasswordFacade;

/**
 * 管理员服务
 *
 * @create 2020-7-28
 * @author deatil
 */
class Admin
{
    // 用户信息
    protected $userInfo = [];
    
    /**
     * 获取当前登录用户资料
     * @return array
     */
    public function getLoginUserInfo($name = '')
    {
        if (empty($this->userInfo)) {
            $this->userInfo = $this->getUserInfo($this->isLogin());
        }
        
        if (empty($name)) {
            return !empty($this->userInfo) ? $this->userInfo : false;
        }
        
        if (!empty($this->userInfo) && isset($this->userInfo[$name])) {
            return $this->userInfo[$name];
        }
        
        return false;
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
        $passwordPass = $this->checkPassword($username, $password);
        if ($passwordPass === false) {
            return false;
        }
        
        $userInfo = $this->getUserInfo($username);
        $this->autoLogin($userInfo);
        
        return true;
    }

    /**
     * 自动登录用户
     */
    public function autoLogin($userInfo)
    {
        /* 更新登录信息 */
        $this->loginStatus($userInfo['id']);
        
        /* 记录登录信息 */
        $user = [
            'uid' => $userInfo['id'],
            'username' => $userInfo['username'],
            'last_login_time' => $userInfo['last_login_time'],
        ];
        
        Session::set('lake_admin_user_auth', $user);
        Session::set('lake_admin_user_auth_sign', Arr::dataAuthSign($user));
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
            'last_login_ip' => request()->ip()
        ];
        return AdminModel::where([
            'id' => $id,
        ])->update($data);
    }

    /**
     * 获取用户信息
     * @param type $identifier 用户名或者用户ID
     * @return array
     */
    public function getUserInfo($identifier)
    {
        if (empty($identifier)) {
            return false;
        }

        $userInfo = AdminModel::where([
            'id' => $identifier,
        ])->whereOr([
            'username' => $identifier,
        ])->find();
        if (empty($userInfo)) {
            return false;
        }
        
        return $userInfo;
    }

    /**
     * 检测密码
     * @param type $identifier 用户名或者用户ID
     * @param type $password 密码
     * @return boolean
     */
    public function checkPassword($identifier, $password)
    {
        if (empty($identifier) || empty($password)) {
            return false;
        }

        $userInfo = $this->getUserInfo($identifier);
        if ($userInfo === false) {
            return false;
        }
        
        // 密码验证
        $encryptPassword = $this->encryptPassword($password, $userInfo['encrypt']);
        if ($encryptPassword != $userInfo['password']) {
            return false;
        }
        
        return true;
    }

    /**
     * 检验用户是否已经登陆
     * @return boolean 失败返回false，成功返回当前登陆用户基本信息
     */
    public function isLogin()
    {
        $user = Session::get('lake_admin_user_auth');
        if (empty($user)) {
            return 0;
        }
        
        $sign = Session::get('lake_admin_user_auth_sign');
        if (empty($sign)) {
            return 0;
        }
        
        if ($sign == Arr::dataAuthSign($user)) {
            return $user['uid'];
        }
        
        return 0;
    }

    /**
     * 检查当前用户是否超级管理员
     * @return boolean
     */
    public function isAdministrator($uid = null)
    {
        if (empty($uid)) {
            $uid = $this->getLoginUserInfo('id');
        }
        
        if (!empty($uid)) {
            $gids = AuthGroupModel::hasWhere('groupAccess', [
                    ['admin_id', '=', $uid],
                ])
                ->where([
                    'is_system' => 1,
                    'is_root' => 1,
                ])
                ->visible([
                    'groupAccess' => [
                        'admin_id',
                    ]
                ])
                ->column('id');
            if (!empty($gids) && (Config::get('app.admin_super_id') == $uid)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 注销登录状态
     * @return boolean
     */
    public function logout()
    {
        Session::clear();
        return true;
    }
    
    /**
     * 管理员密码加密
     * @param $password
     * @param $encrypt //传入加密串，在修改密码时做认证
     * @return array/password
     */
    protected function encryptPassword($password, $encrypt = '')
    {
        $pwd = PasswordFacade::setSalt(Config::get("app.admin_salt"))->encrypt($password, $encrypt);
        return $pwd;
    }

}
