<?php

namespace app\admin\service;

use think\Db;
use think\facade\Session;

use app\admin\model\Admin as AdminModel;

/**
 * 管理员服务
 *
 * @create 2019-7-9
 * @author deatil
 */
class Admin
{
    // 当前登录会员详细信息
    private static $userInfo = array();

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
 
        return Session::get('admin_user_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0;
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
            $gids = Db::name('auth_group')
                ->alias('ag')
                ->join('__AUTH_GROUP_ACCESS__ aga', "aga.group_id = ag.id")
                ->where([
                    'aga.admin_id' => $uid,
                    'ag.is_system' => 1,
                    'ag.is_root' => 1,
                ])
                ->column('ag.id');
            if (!empty($gids)) {
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
     * 获取用户信息
     * @param type $identifier 用户名或者用户ID
     * @return boolean|array
     *
     * @create 2019-7-9
     * @author deatil
     */
    private function getUserInfo($identifier, $password = null)
    {
        if (empty($identifier)) {
            return false;
        }
        return (new AdminModel())->getUserInfo($identifier, $password);
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
