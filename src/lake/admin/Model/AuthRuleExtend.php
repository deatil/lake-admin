<?php

namespace Lake\Admin\Model;

/**
 * 规则扩展表模型
 *
 * @create 2019-8-5
 * @author deatil
 */
class AuthRuleExtend extends ModelBase
{
    // 设置当前模型对应的数据表名称
    protected $name = 'lakeadmin_auth_rule_extend';
    
    // 时间字段取出后的默认时间格式
    protected $dateFormat = false;

    public static function onBeforeInsert($model)
    {
        $id = md5(mt_rand(10000, 99999) . time() . mt_rand(10000, 99999));
        $model->setAttr('id', $id);
    }
    
    /**
     * 对应的组
     *
     * @create 2020-8-24
     * @author deatil
     */
    public function group()
    {
        return $this->hasOne(AuthGroup::class, 'id', 'group_id');
    }

}
