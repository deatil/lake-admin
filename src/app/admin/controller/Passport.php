<?php

namespace app\admin\controller;

use think\facade\View;

use app\admin\model\Admin as AdminModel;
use app\admin\service\Admin as AdminService;
use app\admin\service\Screen as ScreenService;

/**
 * 登陆
 *
 * @create 2019-7-25
 * @author deatil
 */
class Passport extends Base
{
    
    /**
     * 框架构造函数
     *
     * @create 2020-4-9
     * @author deatil
     */
    protected function initialize()
    {
        parent::initialize();
        
        $captcha = [];
        
        $captcha['length'] = 4;
        
        // 设置验证码字体大小
        $captcha['fontSize'] = 18;
        
        // 设置验证码图片宽度
        $captcha['imageW'] = 130;
        
        // 设置验证码图片高度
        $captcha['imageH'] = 36;
        
        // 设置背景颜色
        //$checkcode['background'] = '#fff';
        
        // 设置字体颜色
        //$checkcode['fontcolor'] = '#000';
        
        app()->config->set($captcha, 'captcha');
    }
    
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
        if (AdminService::instance()->isLogin()) {
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
            
            $AdminModel = new AdminModel;
            if (!$AdminModel->login($data['username'], $data['password'])) {
                $this->error("用户名或者密码错误，登陆失败！", url('index/login'));
            }
            
            $this->success('恭喜您，登陆成功', url('Index/index'));
        } else {
            return View::fetch();
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
        if (AdminService::instance()->logout()) {
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
        
        $AdminModel = new AdminModel;
        if (!$AdminModel->getUserInfo($adminInfo['username'], $password)) {
            $this->error("密码错误，解除锁定失败！");
        }
        
        (new ScreenService())->unlock();
        
        $this->success('屏幕解除锁定成功');
    }
}
