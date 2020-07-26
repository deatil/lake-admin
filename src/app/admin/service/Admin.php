<?php

namespace app\admin\service;

use think\facade\Db;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Config;

use lake\Arr;

use app\admin\model\Admin as AdminModel;
use app\admin\model\AuthGroup as AuthGroupModel;
use app\admin\model\AuthGroupAccess as AuthGroupAccessModel;
use app\admin\facade\Password as PasswordFacade;

/**
 * 管理员服务
 *
 * @create 2019-7-9
 * @author deatil
 */
class Admin
{
    // 当前登录会员详细信息
    protected static $userInfo = array();

    protected static $instance = null;

    /**
     * 获取示例
     * @param array $options 实例配置
     * @return static
     *
     * @create 2019-7-9
     * @author deatil
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($options);
        }

        return self::$instance;
    }

    /**
     * 魔术方法
     * @param type $name
     * @return null
     *
     * @create 2019-7-9
     * @author deatil
     */
    public function __get($name)
    {
        //从缓存中获取
        if (isset(self::$userInfo[$name])) {
            return self::$userInfo[$name];
        } else {
            $userInfo = $this->getInfo();
            if (!empty($userInfo)) {
                return $userInfo[$name];
            }
            return null;
        }
    }
    
    /**
     * 获取当前登录用户资料
     * @return array
     *
     * @create 2019-7-9
     * @author deatil
     */
    public function getInfo()
    {
        if (empty(self::$userInfo)) {
            self::$userInfo = $this->getUserInfo($this->isLogin());
        }
        return !empty(self::$userInfo) ? self::$userInfo : false;
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
        Session::set('admin_user_auth_sign', Arr::dataAuthSign($auth));
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

        $userInfo = AdminModel::where([
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
        return AdminModel::where([
            'id' => $id,
        ])->update($data);
    }

    /**
     * 检验用户是否已经登陆
     * @return boolean 失败返回false，成功返回当前登陆用户基本信息
     *
     * @create 2019-7-9
     * @author deatil
     */
    public function isLogin()
    {
        $user = Session::get('admin_user_auth');
        if (empty($user)) {
            return 0;
        }
 
        return Session::get('admin_user_auth_sign') == Arr::dataAuthSign($user) ? $user['uid'] : 0;
    }

    /**
     * 检查当前用户是否超级管理员
     * @return boolean
     *
     * @create 2019-7-9
     * @author deatil
     */
    public function isAdministrator($uid = null)
    {
        if (empty($uid)) {
            $userInfo = $this->getInfo();
            
            if (!empty($userInfo)) {
                $uid = $userInfo['id'];
            }
        }
        
        if (!empty($uid)) {
            $agaTable = (new AuthGroupAccessModel)->getName();
            $gids = AuthGroupModel::alias('ag')
                ->join($agaTable . ' aga', "aga.group_id = ag.id")
                ->where([
                    'aga.admin_id' => $uid,
                    'ag.is_system' => 1,
                    'ag.is_root' => 1,
                ])
                ->column('ag.id');
            if (!empty($gids) && (Config::get('app.administrator_id') == $uid)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 注销登录状态
     * @return boolean
     *
     * @create 2019-7-9
     * @author deatil
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
        $pwd = PasswordFacade::setSalt(config("app.admin_salt"))->encrypt($password, $encrypt);
        return $pwd;
    }

}
