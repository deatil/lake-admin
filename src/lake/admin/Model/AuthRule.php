<?php

namespace Lake\Admin\Model;

/**
 * 权限规则模型
 *
 * @create 2020-7-9
 * @author deatil
 */
class AuthRule extends ModelBase
{
    // 设置当前模型对应的数据表名称
    protected $name = 'lakeadmin_auth_rule';
    
    // 设置主键名
    protected $pk = 'id';
    
    // 时间字段取出后的默认时间格式
    protected $dateFormat = false;
    
    const RULE_MENU = 1; // 菜单
    const RULE_URL = 2;

    public static function onBeforeInsert($model)
    {
        $id = md5(mt_rand(10000, 99999) . time() . mt_rand(10000, 99999) . microtime());
        $model->setAttr('id', $id);
        
        $model->setAttr('add_time', time());
        $model->setAttr('add_ip', request()->ip());
    }
    
    /**
     * 规则的分组列表
     *
     * @create 2020-8-19
     * @author deatil
     */
    public function groups()
    {
        return $this->belongsToMany(AuthGroup::class, AuthRuleAccess::class, 'group_id', 'rule_id');
    }
    
    /**
     * 获取不需要鉴权的菜单
     * @return type
     */
    public static function getNoNeedAuthRuleList()
    {
        $data = self::where([
                'is_need_auth' => 0,
                'status' => 1,
            ])
            ->order('listorder ASC,module ASC')
            ->field('name,method')
            ->select()
            ->toArray();
        return $data;
    }
    
}
