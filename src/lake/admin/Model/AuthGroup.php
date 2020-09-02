<?php

namespace Lake\Admin\Model;

/**
 * 用户组模型类
 *
 * @create 2019-7-9
 * @author deatil
 */
class AuthGroup extends ModelBase
{
    // 设置当前模型对应的数据表名称
    protected $name = 'lakeadmin_auth_group';
    
    // 设置主键名
    protected $pk = 'id';
    
    // 时间字段取出后的默认时间格式
    protected $dateFormat = false;
    
    const TYPE_ADMIN = 1;

    public static function onBeforeInsert($model)
    {
        $id = md5(mt_rand(10000, 99999) . time() . mt_rand(10000, 99999) . microtime());
        $model->setAttr('id', $id);
        
        $model->setAttr('add_time', time());
        $model->setAttr('add_ip', request()->ip());
    }
    
    /**
     * 组的规则授权
     *
     * @create 2020-8-19
     * @author deatil
     */
    public function ruleAccess()
    {
        return $this->hasOne(AuthRuleAccess::class, 'group_id', 'id');
    }
    
    /**
     * 组的权限列表
     *
     * @create 2020-8-19
     * @author deatil
     */
    public function rules()
    {
        return $this->belongsToMany(AuthRule::class, AuthRuleAccess::class, 'rule_id', 'group_id');
    }
    
    /**
     * 组的分组授权
     *
     * @create 2020-8-19
     * @author deatil
     */
    public function groupAccess()
    {
        return $this->hasOne(AuthGroupAccess::class, 'group_id', 'id');
    }
    
    /**
     * 组的管理员列表
     *
     * @create 2020-8-19
     * @author deatil
     */
    public function admins()
    {
        return $this->belongsToMany(Admin::class, AuthGroupAccess::class, 'admin_id', 'group_id');
    }
    
    /**
     * 组的扩展权限列表
     *
     * @create 2020-8-19
     * @author deatil
     */
    public function extendRule()
    {
        return $this->hasOne(AuthRuleExtend::class, 'group_id', 'id');
    }

    /**
     * 返回用户组列表
     * 默认返回正常状态的管理员用户组列表
     * @param array $where   查询条件,供where()方法使用
     */
    public static function getGroups($where = [])
    {
        $map = [
            'status' => 1, 
            'type' => self::TYPE_ADMIN, 
            'module' => 'admin'
        ];
        
        $data = self::where($map)
            ->where($where)
            ->order('listorder ASC')
            ->select()
            ->toArray();
        return $data;
    }

    /**
     * 根据角色Id获取角色名
     * @param int $id 角色id
     * @return string 返回角色名
     */
    public static function getRoleIdName($id)
    {
        return self::where([
            'id' => $id,
        ])->value('title');
    }

    /**
     * 通过递归的方式获取该角色下的全部子角色
     * @param type $id
     * @return string
     */
    public static function getArrchildid($id)
    {
        $list = self::where([
                ['parentid', '=', $id],
            ])
            ->order([
                "add_time" => "ASC",
            ])
            ->field('*')
            ->select()
            ->toArray();
        if (!empty($list)) {
            return [];
        }
        
        $arrchildid = [];
        if (is_array($list)) {
            foreach ($list as $k => $v) {
                $arrchildid[] = $v['id'];
                
                $child = self::getArrchildid($v['id']);
                if (!empty($child)) {
                    $arrchildid = array_merge($arrchildid, $child);
                }
            }
        }
        return $arrchildid;
    }

    /**
     * 删除角色
     * @param int $id 角色ID
     * @return boolean
     */
    public static function groupDelete($id)
    {
        if (empty($id)) {
            return false;
        }
        
        // 角色信息
        $info = self::where([
            'id' => $id,
        ])->find();
        if (empty($info)) {
            return false;
        }
        
        $status = self::where(['id' => $id])->delete();
        if ($status !== false) {
            AuthRuleAccess::where([
                'group_id' => $id,
            ])->delete();
        }
        
        return $status;
    }
}
