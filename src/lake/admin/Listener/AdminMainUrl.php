<?php

namespace Lake\Admin\Listener;

/**
 * 后台控制台设置
 *
 * @create 2020-1-6
 * @author deatil
 */
class AdminMainUrl
{
    
    /**
     * 执行入口
     *
     * @create 2020-1-6
     * @author deatil
     */
    public function handle($params)
    {
        // 首页链接
        $mainUrl = config('app.admin_main_url');
        
        if (empty($mainUrl)) {
            $mainUrl = $params;
        }
        
        return $mainUrl;
    }
    
}
