<?php

namespace Lake\Admin\Service;

/**
 * 屏幕锁屏
 *
 * @create 2020-7-20
 * @author deatil
 */
class Screen
{
    protected $key = 'lake_admin_screen';
    
    /**
     * 锁定
     *
     * @create 2020-7-20
     * @author deatil
     */
    public function lock($value = '')
    {
        session($this->key, $value);
        
        return true;
    }
    
    /**
     * 解除锁定
     *
     * @create 2020-7-20
     * @author deatil
     */
    public function unlock()
    {
        session($this->key, null);
        
        return true;
    }
    
    /**
     * 检测
     *
     * @create 2020-7-20
     * @author deatil
     */
    public function check()
    {
        $data = session($this->key);
        if (empty($data)) {
            return false;
        }
        
        return $data;
    }
    
}
