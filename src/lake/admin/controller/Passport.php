<?php

namespace lake\admin\controller;

use lake\admin\service\Screen as ScreenService;
use lake\admin\facade\Admin as AdminFacade;

/**
 * 登陆
 *
 * @create 2019-7-25
 * @author deatil
 */
class Passport extends Base
{
    /**
     * 验证码
     *
     * @create 2019-7-7
     * @author deatil
     */
    public function captcha()
    {
        return captcha();
    }
    
    /**
     * 登录
     *
     * @create 2019-7-7
     * @author deatil
     */
    public function login()
    {
        if (AdminFacade::isLogin()) {
            return $this->redirect(url('index/index'));
        }
        
        if ($this->request->isPost()) {
            $data = request()->post();
            
            // 验证码
            if (!captcha_check($data['verify'])) {
                return $this->error('验证码输入错误！');
            }
            
            // 验证数据
            $rule = [
                'username|用户名' => 'require|alphaDash|length:3,20',
                'password|密码' => 'require|length:32',
            ];
            $message = [
                'password.length' => '密码错误',
            ];
            $result = $this->validate($data, $rule, $message);
            if (true !== $result) {
                return $this->error($result);
            }
            
            if (!AdminFacade::login($data['username'], $data['password'])) {
                $this->error("用户名或者密码错误，登陆失败！", url('index/login'));
            }
            
            $this->success('登陆成功', url('Index/index'));
        } else {
            return $this->fetch();
        }
    }
    
    /**
     * 手动退出登录
     *
     * @create 2019-7-7
     * @author deatil
     */
    public function logout()
    {
        if (AdminFacade::logout()) {
            $this->success('注销成功！', url("passport/login"));
        }
    }
    
    /**
     * 锁定
     *
     * @create 2020-7-21
     * @author deatil
     */
    public function lockscreen()
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
    public function unlockscreen()
    {
        if (!request()->isPost()) {
            $this->error('访问错误！');
        }
        
        $adminInfo = env('admin_info');
        $password = request()->post('password');
        
        if (!AdminFacade::checkPassword($adminInfo['username'], $password)) {
            $this->error("密码错误，解除锁定失败！");
        }
        
        (new ScreenService())->unlock();
        
        $this->success('屏幕解除锁定成功');
    }
    
}
