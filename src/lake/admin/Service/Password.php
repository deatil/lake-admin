<?php

namespace Lake\Admin\Service;

use Lake\Random;

/**
 * 密码
 *
 * @create 2020-7-22
 * @author deatil
 */
class Password
{
    protected $salt = '';
    
    /**
     * 设置盐
     * @param $salt 加密盐
     * @return $this
     *
     * @create 2020-7-22
     * @author deatil
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
        return $this;
    }
    
    /**
     * 密码加密
     * @param $password
     * @param $encrypt //传入加密串，在修改密码时做认证
     * @return array/password
     *
     * @create 2020-7-22
     * @author deatil
     */
    public function encrypt($password, $encrypt = '')
    {
        $pwd = [];
        $pwd['encrypt'] = $encrypt ? $encrypt : $this->randomString();
        $pwd['password'] = md5(md5($password . $pwd['encrypt']) . $this->salt);
        return $encrypt ? $pwd['password'] : $pwd;
    }
    
    /**
     * 产生一个指定长度的随机字符串,并返回给用户
     * @param type $len 产生字符串的长度
     * @return string 随机字符串
     *
     * @create 2020-7-22
     * @author deatil
     */
    protected function randomString($len = 6)
    {
        return Random::alnum($len);
    }

}
