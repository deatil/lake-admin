<?php

namespace app\admin\behavior;

use app\admin\model\AdminLog as AdminLogModel;

/**
 * 操作记录
 *
 * @create 2019-7-28
 * @author deatil
 */
class AdminLog
{
    
    /**
     * 行为扩展的执行入口必须是run
     *
     * @create 2019-7-15
     * @author deatil
     */
    public function handle($params)
    {
        $msg = request()->param();
        (new AdminLogModel())->record(json_encode($msg), 1);
    }
    
}
