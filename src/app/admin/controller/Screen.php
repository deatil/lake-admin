<?php

namespace app\admin\controller;

use app\admin\model\Admin as AdminModel;
use app\admin\service\Screen as ScreenService;

/**
 * 屏幕锁定
 *
 * @create 2020-7-21
 * @author deatil
 */
class Screen extends Base
{
    
    /**
     * 锁定
     *
     * @create 2020-7-21
     * @author deatil
     */
    public function lock()
    {
        if (!request()->isPost()) {
            $this->error('访问错误！');
        }
        
        $url = request()->url();
        
        (new ScreenService())->lock($url);
        
        $this->success('屏幕锁定成功');
    }
    
    /**
     * 解除锁定
     *
     * @create 2020-7-21
     * @author deatil
     */
    public function unlock()
    {
        if (!request()->isPost()) {
            $this->error('访问错误！');
        }
        
        $adminInfo = env('admin_info');
        $password = request()->post('password');
        
        $AdminModel = new AdminModel;
        if (!$AdminModel->login($adminInfo['username'], $password)) {
            $this->error("用户名或者密码错误，解除锁定失败！");
        }
        
        (new ScreenService())->unlock();
        
        $this->success('屏幕解除锁定成功');
    }
}
