<?php

namespace Lake\Admin\Controller;

use think\facade\Session;

use phpseclib\Crypt\RSA;

use Lake\Admin\Service\Screen as ScreenService;
use Lake\Admin\Facade\Admin as AdminFacade;

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
                return $this->error(__('验证码输入错误！'));
            }
            
            // 验证数据
            $rule = [
                'username|'.__('用户名') => 'require|alphaDash|length:3,20',
                'password|'.__('密码') => 'require',
            ];
            $message = [
                'username.require' => __('用户名不能为空'),
                'username.alphaDash' => __('用户名格式错误'),
                'username.length' => __('用户名长度在3到20个字符之间'),
                'password.require' => __('密码不能为空'),
            ];
            $result = $this->validate($data, $rule, $message);
            if (true !== $result) {
                return $this->error($result);
            }
            
            // 密码
            $password = base64_decode($data['password']);
            if (empty($password)) {
                return $this->error("用户名或者密码错误");
            }

            try {
                // 私钥
                $prikeyCacheKey = config('app.admin_prikey_cache_key');
                $prikey = Session::get($prikeyCacheKey);
                
                // 导入私钥
                $rsa = new RSA();
                $rsa->loadKey($prikey);
                $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
                
                // RSA 解出密码
                $password = $rsa->decrypt($password);
            } catch(\Exception $e) {
                return $this->error(__("用户名或者密码错误，登陆失败！"));
            }
            
            if (!AdminFacade::login($data['username'], $password)) {
                $this->error(__("用户名或者密码错误，登陆失败！"), url('index/login'));
            }
            
            // 清除信息
            Session::delete($prikeyCacheKey);
            
            $this->success(__('登陆成功'), url('Index/index'));
        } else {
            $rsa = new RSA();
            $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
            $rsakeys = $rsa->createKey(1024);
            
            // 私钥
            $privateKey = $rsakeys["privatekey"];
            
            // 公钥
            $publicKey = $rsakeys["publickey"];
            
            // 缓存私钥
            $prikeyCacheKey = config('app.admin_prikey_cache_key');
            Session::set($prikeyCacheKey, $privateKey);
            
            // 过滤公钥多余字符
            $publicKey = str_replace([
                "-----BEGIN PUBLIC KEY-----", 
                "-----END PUBLIC KEY-----", 
                "\r\n",
                "\r",
                "\n",
            ], "", $publicKey);
            
            $this->assign("publicKey", $publicKey);

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
            $this->success(__('注销成功！'), url("passport/login"));
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
            $this->error(__('访问错误！'));
        }
        
        $url = request()->url();
        
        (new ScreenService())->lock($url);
        
        $this->success(__('屏幕锁定成功'));
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
            $this->error(__('访问错误！'));
        }
        
        $adminInfo = env('admin_info');
        $password = request()->post('password');
        
        if (!AdminFacade::checkPassword($adminInfo['username'], $password)) {
            $this->error(__("密码错误，解除锁定失败！"));
        }
        
        (new ScreenService())->unlock();
        
        $this->success(__('屏幕解除锁定成功'));
    }
    
}
