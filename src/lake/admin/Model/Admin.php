<?php

namespace Lake\Admin\Model;

/**
 * 管理员
 *
 * @create 2019-7-9
 * @author deatil
 */
class Admin extends ModelBase
{
    // 设置当前模型对应的完整数据表名称
    protected $name = 'lakeadmin_admin';
    
    // 设置主键名
    protected $pk = 'id';
    
    // 时间字段取出后的默认时间格式
    protected $dateFormat = false;
    
    // 插入数据自动
    protected $insert = [
        'status' => 1,
    ];
    
    /**
     * 获取格式化时间
     *
     * @create 2019-12-29
     * @author deatil
     */
    public function getLastLoginTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    /**
     * 获取格式化IP
     *
     * @create 2019-12-29
     * @author deatil
     */
    public function getLastLoginIpAttr($value)
    {
        $value = intval($value);
        return long2ip($value);
    }
    
    /**
     * 管理员的分组列表
     *
     * @create 2020-8-19
     * @author deatil
     */
    public function groups()
    {
        return $this->belongsToMany(AuthGroup::class, AuthGroupAccess::class, 'group_id', 'admin_id');
    }
    
    /**
     * 管理员的日志列表
     *
     * @create 2020-8-19
     * @author deatil
     */
    public function logs()
    {
        return $this->hasMany(AdminLog::class, 'admin_id', 'id');
    }
    
    /**
     * 管理员的附件列表
     * @param string $type 关联类型
     * @return array
     *
     * @create 2020-8-19
     * @author deatil
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, [
            'type',
            'type_id', 
        ], 'admin');
    }
    
}
