<?php

namespace lake\admin\auth;

/**
 * 检测
 *
 * @create 2020-8-27
 * @author deatil
 */
class Check
{
    /** 
     * param array
     */
    protected $auths = [];
    
    /**
     * 设置
     * @param array $auths 权限数据
     * @return object
     *
     * @create 2020-8-27
     * @author deatil
     */
    public function withAuths($auths)
    {
        $this->auths = $auths;
        return $this;
    }
    
    /**
     * 获取权限数据
     * @return array
     *
     * @create 2020-8-27
     * @author deatil
     */
    public function getAuths()
    {
        return $this->auths;
    }
    
    /**
     * 检查权限
     * @param string|array name  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
     * @param string relation    如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
     * @param string mode        执行check的模式
     * @return boolean           通过验证返回true;失败返回false
     *
     * @create 2020-8-28
     * @author deatil
     */
    public function check($name, $relation = 'or', $mode = 'url') {
        if (empty($name)) {
            return false;
        }
        
        $name = $this->fotmatName($name);
        
        $list = []; // 保存验证通过的规则名
        foreach ($name as $nameValue) {
            $authPassList = [];
            $auths = $this->auths; 
            if (!empty($auths)) {
                foreach ($auths as $auth) {
                    if ($mode == 'url') {
                        $matchUrl = $this->checkMatchUrl($nameValue, $auth);
                        if ($matchUrl === true) {
                            $authPassList[] = $auth;
                        }
                    } elseif ($auth == $nameValue) {
                        $authPassList[] = $auth;
                    }
                }
            }
            
            $nameMd5 = md5($nameValue);
            $list[$nameMd5] = $authPassList;
        }
        
        if ($relation == 'or') {
            $or = $this->checckOrRelation($list);
            if ($or === true) {
                return true;
            }
        }
        
        if ($relation == 'and') {
            $and = $this->checckAndRelation($list);
            if ($and === true) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 获取权限数据
     * @param string $name 要验证的规则
     * @param string $auth 权限
     * @return boolean
     *
     * @create 2020-8-28
     * @author deatil
     */
    protected function checkMatchUrl($name, $auth)
    {
        $nameParse = (new Parser)->withUrl($name)->parse();
        $nameAuth = $nameParse->getPath();
        $nameParam = $nameParse->getParam();
        
        $authParse = (new Parser)->withUrl($auth)->parse();
        $auth2 = $authParse->getPath();
        $param = $authParse->getParam();
        
        if ($auth != $auth2) {
            $intersect = array_intersect_assoc($nameParam, $param);
            
            if ($auth2 == $nameAuth
                && serialize($intersect) == serialize($param)
            ) {
                return true;
            }
        } elseif ($auth2 == $nameAuth) {
            return true;
        }
        
        return false;
    }
   
    /**
     * 格式化name
     * @param string $name 要检测的名称
     * @return string
     *
     * @create 2020-8-27
     * @author deatil
     */
    protected function fotmatName($name)
    {
        if (is_string($name)) {
            $name = strtolower($name);
            if (strpos($name, ',') !== false) {
                $name = explode(',', $name);
            } else {
                $name = [$name];
            }
        }
        
        return $name;
    }
    
    /**
     * 检测 And
     * @param array $list 数据列表
     * @return boolean
     *
     * @create 2020-8-27
     * @author deatil
     */
    protected function checckAndRelation($list)
    {
        if (empty($list)) {
            return false;
        }
        
        $allStatus = true;
        foreach ($list as $value2) {
            if (empty($value2)) {
                $allStatus = false;
                break;
            }
        }
        
        if ($allStatus !== false) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 检测 Or
     * @param array $list 数据列表
     * @return boolean
     *
     * @create 2020-8-27
     * @author deatil
     */
    protected function checckOrRelation($list)
    {
        if (empty($list)) {
            return false;
        }
        
        foreach ($list as $value) {
            if (!empty($value)) {
                return true;
            }
        }
        
        return false;
    }
     
}
