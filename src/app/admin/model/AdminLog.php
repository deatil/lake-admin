<?php

namespace app\admin\model;

use think\Model;

use app\admin\service\Admin as AdminUser;

/**
 * 操作日志
 *
 * @create 2019-7-9
 * @author deatil
 */
class AdminLog extends Model
{
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

    public function getIpAttr($value)
    {
        return long2ip($value);
    }

    /**
     * 记录日志
     * @param type $message 说明
     * @param  integer $status  状态
     */
    public function record($message, $status = 0)
    {
		$admin_id = AdminUser::instance()->isLogin();
		if ($admin_id > 0) {
			$userInfo = env("userInfo");
			$admin_username = $userInfo['username'];
		} else {
			$admin_id = 0;
			$admin_username = '';
		}
	
        $data = [
            'id' => md5(time().mt_rand(10000, 99999)),
            'admin_id' => $admin_id,
            'admin_username' => $admin_username,
            'info' => "{$message}",
            'method' => request()->method(),
            'url' => request()->url(),
            'ip' => request()->ip(1),
            'useragent' => request()->server('HTTP_USER_AGENT'),
            'status' => $status,
        ];
        return $this->save($data) !== false ? true : false;
    }

    /**
     * 删除一个月前的日志
     * @return boolean
     */
    public function deleteAMonthago()
    {
        $status = $this->where('create_time', '<= time', time() - (86400 * 30))->delete();
        return $status !== false ? true : false;
    }

}
