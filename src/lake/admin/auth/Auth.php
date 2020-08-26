<?php

namespace lake\admin\auth;

use think\facade\Db;

/**
 * 权限认证类
 * 
 * 功能特性：
 * 1，是对规则进行认证，不是对节点进行认证。用户可以把节点当作规则名称实现对节点进行认证。
 *      $auth = new Auth();  $auth->check('规则名称','用户id')
 * 2，可以同时对多条规则进行认证，并设置多条规则的关系（or或者and）
 *      $auth = new Auth();  $auth->check('规则1,规则2','用户id','and')
 *      第三个参数为and时表示，用户需要同时具有规则1和规则2的权限。 当第三个参数为or时，表示用户值需要具备其中一个条件即可。默认为or
 *
 * @create 2019-7-9
 * @author deatil
 */
class Auth
{    
    // 默认配置
    protected $config = [
        'AUTH_ON' => true, // 认证开关
        'AUTH_TYPE' => 1, // 认证方式，1为实时认证；2为登录认证。
        'AUTH_ACCESS_FIELD' => '', // 用户关联字段，相当于UID字段，必填 admin_id
        'AUTH_GROUP' => '', // 用户组数据表名 auth_group
        'AUTH_GROUP_ACCESS' => '', // 授权表 auth_group_access
        'AUTH_RULE' => '', // 权限规则表 auth_rule
        'AUTH_RULE_ACCESS' => '', // 权限规则关系表 auth_rule_access
        'AUTH_RULE_EXTEND' => '', // 扩展表 auth_rule_extend
    ];

    /**
     * 类架构函数
     * Auth constructor.
     *
     * @create 2019-7-9
     * @author deatil
     */
    public function __construct($config = [])
    {
        $this->setConfig($config);
    }

    /**
     * 设置配置信息
     * @param array|string $name 配置信息
     * @param string $value 配置信息
     * @return void
     *
     * @create 2020-8-16
     * @author deatil
     */
    public function setConfig($name = '', $value = '')
    {
        if (!empty($name)) {
            if (is_array($name)) {
                $this->config = array_merge($this->config, $name);
            } else {
                $this->config = array_merge($this->config, [
                    $name => (string) $value,
                ]);
            }
        }
    }

    /**
     * 检查权限
     * @param name string|array  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
     * @param integer uid        认证用户的id
     * @param string relation    如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
     * @param integer type       查询类型
     * @param string mode        执行check的模式
     * @return boolean           通过验证返回true;失败返回false
     *
     * @create 2019-7-9
     * @author deatil
     */
    public function check(
        $name, 
        $uid, 
        $relation = 'or', 
        $type = 1, 
        $mode = 'url'
    ) {
        if (!$this->config['AUTH_ON']) {
            return true;
        }
        
        if (empty($name)) {
            return false;
        }
        
        if (is_string($name)) {
            $name = strtolower($name);
            if (strpos($name, ',') !== false) {
                $name = explode(',', $name);
            } else {
                $name = [$name];
            }
        }
        
        $list = []; // 保存验证通过的规则名
        
        foreach ($name as $nameValue) {
            if ($mode == 'url') {
                $nameParam = [];
                $nameQuery = preg_replace('/^.+\?/U', '', $nameValue);
                $nameAuth = preg_replace('/\?.*$/U', '', $nameValue);
                if ($nameAuth != $nameValue) {
                    parse_str($nameQuery, $nameParam);
                }
            }
            
            $authPassList = [];
            $authList = $this->getAuthList($uid, $type); // 获取用户需要验证的所有有效规则列表
            if (!empty($authList)) {
                foreach ($authList as $auth) {
                    if ($mode == 'url') {
                        $query = preg_replace('/^.+\?/U', '', $auth);
                        $auth2 = preg_replace('/\?.*$/U', '', $auth);
                        
                        if ($auth != $auth2) {
                            parse_str($query, $param); // 解析规则中的param
                            $intersect = array_intersect_assoc($nameParam, $param);
                            
                            if ($auth2 == $nameAuth
                                && serialize($intersect) == serialize($param)
                            ) {
                                // 如果节点相符且url参数满足
                                $authPassList[] = $auth;
                            }
                        } elseif ($auth2 == $nameAuth) {
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
            foreach ($list as $value) {
                if (!empty($value)) {
                    return true;
                }
            }
        }
        
        if ($relation == 'and') {
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
        }
        
        return false;
    }

    /**
     * 获得用户组ID列表
     * @param integer $uid  用户id
     * @return array
     *
     * @create 2010-10-13
     * @author deatil
     */
    public function getUserGroupIdList($uid)
    {        
        // 读取用户所属用户组
        $groups = $this->getGroups($uid);
        $gids = [];
        foreach ($groups as $g) {
            $gids[] = $g['id'];
        }
        
        return $gids;
    }

    /**
     * 获得用户权限ID列表
     * @param integer $uid  用户id
     * @return array
     *
     * @create 2010-10-13
     * @author deatil
     */
    public function getUserAuthIdList($uid)
    {
        static $authIdCacheList = []; //保存用户验证通过的权限列表
        if (isset($authIdCacheList[$uid])) {
            return $authIdCacheList[$uid];
        }
        
        // 读取用户所属用户组
        $groups = $this->getGroups($uid);
        $gids = [];
        foreach ($groups as $g) {
            $gids[] = $g['id'];
        }
        
        $ids = $this->getGroupRuleidList($gids); // 保存用户所属用户组设置的所有权限规则id
        $ids = array_unique($ids);
        if (empty($ids)) {
            $authIdCacheList[$uid] = [];
            return [];
        }    
        
        return $ids;
    }
    
    /**
     * 获得权限ID列表
     * @param array $gids 分组id列表
     * @return array
     * 
     * @create 2019-7-8
     * @author deatil
     */
    public function getGroupRuleidList($gids = [])
    {
        $map = [
            ['ara.group_id', 'in', $gids],
        ];
        
        $rules = Db::name($this->config['AUTH_RULE'])
            ->alias('ar')
            ->leftJoin($this->config['AUTH_RULE_ACCESS'] . ' ara ', 'ara.rule_id = ar.id')
            ->where($map)
            ->column('ar.id');
        
        return $rules;
    }    
    
    /**
     * 获得父级权限ID列表
     * @param array $gids 分组id列表
     * @return array
     * 
     * @create 2019-10-19
     * @author deatil
     */
    public function getParentGroupIdList($gids = [])
    {
        $map = [
            ['id', 'in', $gids],
        ];
        
        $ids = Db::name($this->config['AUTH_GROUP'])
            ->where($map)
            ->column('parentid');
        
        return $ids;
    }

    /**
     * 根据用户id获取用户组,返回值为数组
     * @param integer uid  用户id
     * @return array       用户所属的用户组 
     *  array(
     *      array(
     *          'id' => '用户组id',
     *          'title' => '用户组名称',
     *          'uid' => '用户id',
     *      ),
     *      ...
     *   )
     *
     * @create 2019-7-9
     * @author deatil
     */
    public function getGroups($uid)
    {
        static $groups = [];
        if (isset($groups[$uid])) {
            return $groups[$uid];
        }
        
        $userGroups = Db::name($this->config['AUTH_GROUP_ACCESS'])
            ->alias('aga')
            ->join($this->config['AUTH_GROUP'] . ' ag', "aga.group_id = ag.id")
            ->where('aga.'.$this->config['AUTH_ACCESS_FIELD'], $uid)
            ->where('ag.status', 1)
            ->field('ag.id, ag.title')
            ->select();
        $groups[$uid] = $userGroups ?: [];
        
        return $groups[$uid];
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
        static $authCacheList = []; //保存用户验证通过的权限列表
        $t = implode(',', (array) $type);
        if (isset($authCacheList[$uid . $t])) {
            return $authCacheList[$uid . $t];
        }
        
        if ($this->config['AUTH_TYPE'] == 2 && isset($_SESSION['_AUTH_LIST_' . $uid . $t])) {
            return $_SESSION['_AUTH_LIST_' . $uid . $t];
        }
        
        // 读取用户所属用户组
        $groups = $this->getGroups($uid);
        $gids = [];
        if (!empty($groups)) {
            foreach ($groups as $g) {
                $gids[] = $g['id'];
            }
        }
        
        $ids = $this->getGroupRuleidList($gids); //保存用户所属用户组设置的所有权限规则id
        $ids = array_unique($ids);
        if (empty($ids)) {
            $authCacheList[$uid . $t] = [];
            return [];
        }
        
        $map = [
            ['id', 'in', $ids],
            ['type', 'in', $type],
            ['status', '=', 1],
        ];
        
        // 读取用户组所有权限规则
        $rules = Db::name($this->config['AUTH_RULE'])
            ->where($map)
            ->field('name,condition,method')
            ->select();
            
        // 循环规则，判断结果。
        $authList = [];
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                if (!empty($rule['name'])) {
                    $authList[] = strtolower($rule['method'].':'.$rule['name']);
                }
            }
        }
        
        // 扩展规则
        $extendRules = $this->getRuleExtendList($gids);
        if (!empty($extendRules)) {
            foreach ($extendRules as $extendRule) {
                if (!empty($extendRule['rule'])) {
                    $authList[] = strtolower($extendRule['method'].':'.$extendRule['rule']);
                }
            }
        }
        
        $authCacheList[$uid . $t] = $authList;
        if ($this->config['AUTH_TYPE'] == 2) {
            // 规则列表结果保存到session
            $_SESSION['_AUTH_LIST_' . $uid . $t] = $authList;
        }
        
        return array_unique($authList);
    }
    
    /**
     * 获取扩展规则
     * @param array $gids 分组id列表
     * @return array
     *
     * @create 2019-7-9
     * @author deatil
     */
    public function getRuleExtendList($gids)
    {
        if (!isset($this->config['AUTH_RULE_EXTEND'])
            || empty($this->config['AUTH_RULE_EXTEND'])) {
            return [];
        }
        
        $map = [
            ['group_id', 'in', $gids],
        ];
        
        $rules = Db::name($this->config['AUTH_RULE_EXTEND'])
            ->where($map)
            ->field('rule,condition,method')
            ->select();
        
        return $rules;
    }

    /**
     * 获得权限ID列表
     * @param integer $uid  用户id
     * @param integer $type 选择类型
     *
     * @create 2019-7-9
     * @author deatil
     */
    public function getAuthIdList($uid, $type)
    {
        static $authIdCacheList = []; //保存用户验证通过的权限列表
        $t = implode(',', (array) $type);
        if (isset($authIdCacheList[$uid . $t])) {
            return $authIdCacheList[$uid . $t];
        }
        
        if ($this->config['AUTH_TYPE'] == 2 
            && isset($_SESSION['_AUTH_ID_LIST_' . $uid . $t])
        ) {
            return $_SESSION['_AUTH_ID_LIST_' . $uid . $t];
        }
        
        // 读取用户所属用户组
        $groups = $this->getGroups($uid);
        $gids = [];
        foreach ($groups as $g) {
            $gids[] = $g['id'];
        }
        
        $ids = $this->getGroupRuleidList($gids); // 保存用户所属用户组设置的所有权限规则id
        $ids = array_unique($ids);
        if (empty($ids)) {
            $authIdCacheList[$uid . $t] = [];
            return [];
        }
        
        $map = [
            ['id', 'in', $ids],
            ['type', 'in', $type],
            ['status', '=', 1],
        ];
        
        // 读取用户组所有权限规则
        $rules = Db::name($this->config['AUTH_RULE'])
            ->where($map)
            ->field('id')
            ->select();
            
        // 循环规则，判断结果。
        $authIdList = [];
        foreach ($rules as $rule) {
            $authIdList[] = trim($rule['id']);
        }
        
        $authIdCacheList[$uid . $t] = $authIdList;
        if ($this->config['AUTH_TYPE'] == 2) {
            // 规则列表结果保存到session
            $_SESSION['_AUTH_ID_LIST_' . $uid . $t] = $authIdList;
        }
        
        return array_unique($authIdList);
    }
}
