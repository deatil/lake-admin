<?php

namespace Lake\Admin\Auth;

/**
 * 权限认证类
 * 
 * 功能特性：
 * 1，是对规则进行认证，不是对节点进行认证。用户可以把节点当作规则名称实现对节点进行认证。
 *      $auth = new Permission();  $auth->check('规则名称','用户id')
 * 2，可以同时对多条规则进行认证，并设置多条规则的关系（or或者and）
 *      $auth = new Permission();  $auth->check('规则1,规则2','用户id','and')
 *      第三个参数为and时表示，用户需要同时具有规则1和规则2的权限。 当第三个参数为or时，表示用户值需要具备其中一个条件即可。默认为or
 *
 * @create 2019-7-9
 * @author deatil
 */
class Permission
{
    protected $ruleType = 1;
    
    /**
     * 设置
     *
     * @create 2019-7-9
     * @author deatil
     */
    public function withRuleType($ruleType = 1)
    {
        if (!in_array($ruleType, [1, 2])) {
            $ruleType = 1;
        }
        $this->ruleType = $ruleType;
        return $this;
    }
    
    /**
     * 检查权限
     * @param string|array name  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
     * @param integer uid        认证用户的id
     * @param string relation    如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
     * @param array type 查询类型
     * @param string mode        执行check的模式
     * @return boolean           通过验证返回true;失败返回false
     *
     * @create 2019-7-9
     * @author deatil
     */
    public function check(
        $name, 
        $uid = '', 
        $relation = 'or', 
        $type = [1], 
        $mode = 'url'
    ) {
        $authList = $this->getAuthList($uid, $type);
        
        $checkAuthList = (new Check)->withAuths($authList)->check($name, $relation, $mode);
        if ($checkAuthList !== false) {
            return $checkAuthList;
        }
        
        return false;
    }

    /**
     * 获得权限列表
     * @param integer $uid  用户id
     * @param integer $type 选择类型
     *
     * @create 2019-7-9
     * @author deatil
     */
    public function getAuthList($uid, $type)
    {
        // 获取用户需要验证的所有有效规则列表
        $authList = (new Rule($this->ruleType))->getAuthList($uid, $type); 
        
        return $authList;
    }
    
}
