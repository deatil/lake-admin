<?php

namespace app\admin\service;

use think\Db;
use think\facade\Config;
use think\facade\Request;

/**
 * 权限认证类
 * 功能特性：
 * 1，是对规则进行认证，不是对节点进行认证。用户可以把节点当作规则名称实现对节点进行认证。
 *      $auth = new Auth();  $auth->check('规则名称','用户id')
 * 2，可以同时对多条规则进行认证，并设置多条规则的关系（or或者and）
 *      $auth = new Auth();  $auth->check('规则1,规则2','用户id','and')
 *      第三个参数为and时表示，用户需要同时具有规则1和规则2的权限。 当第三个参数为or时，表示用户值需要具备其中一个条件即可。默认为or
 * 3，一个用户可以属于多个用户组(think_auth_group_access表 定义了用户所属用户组)。我们需要设置每个用户组拥有哪些规则(think_auth_group 定义了用户组权限)
 *
 * 4，支持规则表达式。
 *      在think_auth_rule 表中定义一条规则时，如果type为1， condition字段就可以定义规则表达式。 如定义{score}>5  and {score}<100  表示用户的分数在5-100之间时这条规则才会通过。
 */
class Auth
{
    /**
     * 当前请求实例
     * @var Request
     */
    protected $request;
    
    //默认配置
    protected $_config = [
        'AUTH_ON' => true, // 认证开关
        'AUTH_TYPE' => 1, // 认证方式，1为实时认证；2为登录认证。
        'AUTH_USER' => 'admin', // 用户信息表
        'AUTH_GROUP' => 'auth_group', // 用户组数据表名
        'AUTH_GROUP_ACCESS' => 'auth_group_access',
        'AUTH_RULE' => 'auth_rule', // 权限规则表
        'AUTH_RULE_ACCESS' => 'auth_rule_access', // 权限规则关系表
        'AUTH_RULE_EXTEND' => 'auth_rule_extend', // 扩展表
    ];

    /**
     * 类架构函数
     * Auth constructor.
     *
     * @create 2019-7-9
     * @author deatil
     */
    public function __construct()
    {
        //可设置配置项 auth, 此配置项为数组。
        if ($auth = Config::get('auth')) {
            $this->config = array_merge($this->_config, $auth);
        }
        
        // 初始化request
        $this->request = Request::instance();
    }

    /**
     * 检查权限
     * @param name string|array  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
     * @param uid  int           认证用户的id
     * @param string mode        执行check的模式
     * @param relation string    如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
     * @return boolean           通过验证返回true;失败返回false
     *
     * @create 2019-7-9
     * @author deatil
     */
    public function check($name, $uid, $type = 1, $mode = 'url', $relation = 'or')
    {
        if (!$this->_config['AUTH_ON']) {
            return true;
        }
        
        $authList = $this->getAuthList($uid, $type); //获取用户需要验证的所有有效规则列表
        if (is_string($name)) {
            $name = strtolower($name);
            if (strpos($name, ',') !== false) {
                $name = explode(',', $name);
            } else {
                $name = array($name);
            }
        }
        
        $list = []; //保存验证通过的规则名
        if ('url' == $mode) {
            $REQUEST = unserialize(strtolower(serialize($this->request->param())));
        }
        
        if (!empty($authList)) {
            foreach ($authList as $auth) {
                $query = preg_replace('/^.+\?/U', '', $auth);
                if ($mode == 'url' && $query != $auth) {
                    parse_str($query, $param); //解析规则中的param
                    $intersect = array_intersect_assoc($REQUEST, $param);
                    $auth = preg_replace('/\?.*$/U', '', $auth);
                    if (in_array($auth, $name) 
                        && serialize($intersect) == serialize($param)
                    ) {
                        //如果节点相符且url参数满足
                        $list[] = $auth;
                    }
                } elseif (in_array($auth, $name)) {
                    $list[] = $auth;
                }
            }
        }
        
        if ($relation == 'or' and !empty($list)) {
            return true;
        }
        
        $diff = array_diff($name, $list);
        if ($relation == 'and' and empty($diff)) {
            return true;
        }
        
        return false;
    }

    /**
     * 获得用户组ID列表
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
        static $_authIdList = []; //保存用户验证通过的权限列表
        if (isset($_authIdList[$uid])) {
            return $_authIdList[$uid];
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
            $_authIdList[$uid] = [];
            return [];
        }    
        
        return $ids;
    }
    
    /**
     * 获得权限ID列表
     * 
     * @create 2019-7-8
     * @author deatil
     */
    public function getGroupRuleidList($gids = [])
    {
        $map = [
            ['ara.group_id', 'in', $gids],
        ];
        
        $rules = Db::name($this->_config['AUTH_RULE'])
            ->alias('ar')
            ->leftJoin($this->_config['AUTH_RULE_ACCESS'] . ' ara ', 'ara.rule_id = ar.id')
            ->where($map)
            ->column('ar.id');
        
        return $rules;
    }    
    
    /**
     * 获得父级权限ID列表
     * 
     * @create 2019-10-19
     * @author deatil
     */
    public function getParentGroupIdList($gids = [])
    {
        $map = [
            ['id', 'in', $gids],
        ];
        
        $ids = Db::name($this->_config['AUTH_GROUP'])
            ->where($map)
            ->column('parentid');
        
        return $ids;
    }

    /**
     * 根据用户id获取用户组,返回值为数组
     * @param  uid int     用户id
     * @return array       用户所属的用户组 array(
     *                                         array('uid'=>'用户id','group_id'=>'用户组id','title'=>'用户组名称'),
     *                                         ...)
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
        
        $user_groups = Db::name($this->_config['AUTH_USER'])
            ->alias('au')
            ->join($this->_config['AUTH_GROUP_ACCESS'] . ' aga', "aga.admin_id = au.id")
            ->join($this->_config['AUTH_GROUP'] . ' ag', "aga.group_id = ag.id")
            ->where('au.id', $uid)
            ->where('ag.status', 1)
            ->field('au.id, ag.id, ag.title')
            ->select();
        $groups[$uid] = $user_groups ?: [];
        
        return $groups[$uid];
    }

    /**
     * 获得权限列表
     * @param integer $uid  用户id
     * @param integer $type
     *
     * @create 2019-7-9
     * @author deatil
     */
    public function getAuthList($uid, $type)
    {
        static $_authList = []; //保存用户验证通过的权限列表
        $t = implode(',', (array) $type);
        if (isset($_authList[$uid . $t])) {
            return $_authList[$uid . $t];
        }
        
        if ($this->_config['AUTH_TYPE'] == 2 && isset($_SESSION['_AUTH_LIST_' . $uid . $t])) {
            return $_SESSION['_AUTH_LIST_' . $uid . $t];
        }
        
        // 读取用户所属用户组
        $groups = $this->getGroups($uid);
        $gids = [];
        foreach ($groups as $g) {
            $gids[] = $g['id'];
        }
        
        $ids = $this->getGroupRuleidList($gids); //保存用户所属用户组设置的所有权限规则id
        $ids = array_unique($ids);
        if (empty($ids)) {
            $_authList[$uid . $t] = [];
            return [];
        }
        
        $map = [
            ['id', 'in', $ids],
            ['type', 'in', $type],
            ['status', '=', 1],
        ];
        
        // 读取用户组所有权限规则
        $rules = Db::name($this->_config['AUTH_RULE'])
            ->where($map)
            ->field('condition,name')
            ->select();
            
        // 循环规则，判断结果。
        $authList = [];
        foreach ($rules as $rule) {
            $authList[] = strtolower($rule['name']);
        }
        
        // 扩展规则
        $extend_rules = $this->getRuleExtendList($gids);
        if (!empty($extend_rules)) {
            foreach ($extend_rules as $extend_rule) {
                $authList[] = strtolower($extend_rule);
            }
        }
        
        $_authList[$uid . $t] = $authList;
        if ($this->_config['AUTH_TYPE'] == 2) {
            //规则列表结果保存到session
            $_SESSION['_AUTH_LIST_' . $uid . $t] = $authList;
        }
        
        return array_unique($authList);
    }
    
    /**
     * 获取扩展规则
     *
     * @create 2019-7-9
     * @author deatil
     */
    public function getRuleExtendList($gids)
    {
        $map = [
            ['group_id', 'in', $gids],
        ];        
        
        $rules = Db::name($this->_config['AUTH_RULE_EXTEND'])
            ->where($map)
            ->column('rule');
        
        return $rules;
    }

    /**
     * 获得权限ID列表
     * @param integer $uid  用户id
     * @param integer $type
     *
     * @create 2019-7-9
     * @author deatil
     */
    public function getAuthIdList($uid, $type)
    {
        static $_authIdList = []; //保存用户验证通过的权限列表
        $t = implode(',', (array) $type);
        if (isset($_authIdList[$uid . $t])) {
            return $_authIdList[$uid . $t];
        }
        
        if ($this->_config['AUTH_TYPE'] == 2 && isset($_SESSION['_AUTH_ID_LIST_' . $uid . $t])) {
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
            $_authIdList[$uid . $t] = [];
            return [];
        }
        
        $map = [
            ['id', 'in', $ids],
            ['type', 'in', $type],
            ['status', '=', 1],
        ];
        
        // 读取用户组所有权限规则
        $rules = Db::name($this->_config['AUTH_RULE'])
            ->where($map)
            ->field('id')
            ->select();
            
        // 循环规则，判断结果。
        $authIdList = [];
        foreach ($rules as $rule) {
            $authIdList[] = trim($rule['id']);
        }
        
        $_authIdList[$uid . $t] = $authIdList;
        if ($this->_config['AUTH_TYPE'] == 2) {
            // 规则列表结果保存到session
            $_SESSION['_AUTH_ID_LIST_' . $uid . $t] = $authIdList;
        }
        
        return array_unique($authIdList);
    }
    
    /**
     * 获得用户资料,根据自己的情况读取数据库
     *
     * @create 2019-7-9
     * @author deatil
     */
    public function getUserInfo($uid)
    {
        static $userinfo = [];
        if (!isset($userinfo[$uid])) {
            $userinfo[$uid] = Db::name($this->_config['auth_user'])->where([
                'id' => $uid,
            ])->find();
        }
        return $userinfo[$uid];
    }

}
