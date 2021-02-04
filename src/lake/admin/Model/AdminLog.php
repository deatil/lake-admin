<?php

namespace Lake\Admin\Model;

use Lake\Admin\Facade\Admin as AdminFacade;

/**
 * 操作日志
 *
 * @create 2019-7-9
 * @author deatil
 */
class AdminLog extends ModelBase
{
    // 设置当前模型对应的数据表名称
    protected $name = 'lakeadmin_admin_log';
    
    // 设置主键名
    protected $pk = 'id';
    
    // 时间字段取出后的默认时间格式
    protected $dateFormat = false;

    public static function onBeforeInsert($model)
    {
        $id = md5(mt_rand(10000, 99999) . time() . mt_rand(10000, 99999) . microtime());
        $model->setAttr('id', $id);
        
        $model->setAttr('add_time', time());
        $model->setAttr('add_ip', request()->ip());
    }

    /**
     * 记录日志
     * @param type $message 说明
     * @param  integer $status  状态
     */
    public static function record($message, $status = 0)
    {
        $adminId = AdminFacade::isLogin();
        if ($adminId > 0) {
            $adminInfo = env("admin_info");
            $adminUsername = $adminInfo['username'];
        } else {
            $adminId = 0;
            $adminUsername = '';
        }
    
        $data = [
            'admin_id' => $adminId,
            'admin_username' => $adminUsername,
            'info' => $message,
            'method' => request()->method(),
            'url' => request()->url(),
            'ip' => request()->ip(),
            'useragent' => request()->server('HTTP_USER_AGENT'),
            'status' => $status,
        ];
        return (new self)->save($data) !== false ? true : false;
    }

    /**
     * 删除一个月前的日志
     * @return boolean
     */
    public static function deleteAMonthago()
    {
        $status = self::where('create_time', '<= time', time() - (86400 * 30))->delete();
        return $status !== false ? true : false;
    }

}
