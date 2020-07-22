<?php

namespace app\admin\service;

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
        $chars = [
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
            "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
            "3", "4", "5", "6", "7", "8", "9",
        ];
        $charsLen = count($chars) - 1;
        // 将数组打乱
        shuffle($chars);
        $output = "";
        for ($i = 0; $i < $len; $i++) {
            $output .= $chars[mt_rand(0, $charsLen)];
        }
        return $output;
    }

}
