<?php

namespace Lake\Admin\Listener;

use Lake\Admin\Model\AdminLog as AdminLogModel;

/**
 * 操作记录
 *
 * @create 2019-7-28
 * @author deatil
 */
class AdminLog
{
    
    /**
     * 执行入口
     *
     * @create 2019-7-15
     * @author deatil
     */
    public function handle($params)
    {
        $msg = request()->param();
        AdminLogModel::record(json_encode($msg), 1);
    }
    
}
