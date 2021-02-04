<?php

namespace Lake\Admin\Auth;

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
     * @return boolean|array     通过验证返回true;失败返回false
     *
     * @create 2020-8-28
     * @author deatil
     */
    public function check($name, $relation = 'or', $mode = 'url') 
    {
        if (empty($name)) {
            return false;
        }
        
        $name = $this->formatName($name);
        
        $list = []; // 保存验证通过的规则名
        foreach ($name as $nameValue) {
            $authPassList = [];
            
            $checkMatchAuth = $this->checkOnce($nameValue, $mode); 
            if ($checkMatchAuth !== false) {
                $authPassList[] = $checkMatchAuth;
            }
            
            $nameMd5 = md5($nameValue);
            $list[$nameMd5] = $authPassList;
        }
        
        if ($relation == 'or') {
            $or = $this->checkOrRelation($list);
            if ($or === true) {
                return $list;
            }
        }
        
        if ($relation == 'and') {
            $and = $this->checkAndRelation($list);
            if ($and === true) {
                return $list;
            }
        }
        
        return false;
    }
    
    /**
     * 单次检查权限
     * @param string name       需要验证的规则列表
     * @param string relation   如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
     * @param string mode       执行check的模式
     * @return boolean|string   通过验证返回true;失败返回false
     *
     * @create 2020-8-30
     * @author deatil
     */
    public function checkOnce($name, $mode = 'url')
    {
        if (empty($name)) {
            return false;
        }
        
        $auths = $this->getAuths(); 
        if (empty($auths)) {
            return false;
        }
        
        foreach ($auths as $auth) {
            if ($mode == 'url') {
                $matchUrl = $this->checkMatchUrl($name, $auth);
                if ($matchUrl === true) {
                    return $auth;
                }
            } elseif ($auth == $name) {
                return $auth;
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
        $namePath = $nameParse->getPath();
        $nameParam = $nameParse->getParam();
        
        $authParse = (new Parser)->withUrl($auth)->parse();
        $authPath = $authParse->getPath();
        $authParam = $authParse->getParam();
        
        if ($auth != $authPath) {
            $intersectParam = array_intersect_assoc($nameParam, $authParam);
            
            if ($namePath == $authPath
                && serialize($intersectParam) == serialize($authParam)
            ) {
                return true;
            }
        } elseif ($namePath == $authPath) {
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
    protected function formatName($name)
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
    protected function checkAndRelation($list)
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
    protected function checkOrRelation($list)
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
