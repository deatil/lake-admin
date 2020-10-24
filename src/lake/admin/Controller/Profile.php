<?php

namespace Lake\Admin\Controller;

use Lake\Admin\Model\Admin as AdminModel;
use Lake\Admin\Facade\Admin as AdminFacade;

/**
 * 账号信息
 *
 * @create 2019-7-25
 * @author deatil
 */
class Profile extends Base
{
    /**
     * 管理员账号修改
     *
     * @create 2019-7-2
     * @author deatil
     */
    public function index()
    {
        $adminInfo = env('admin_info');
        
        if ($this->request->isPost()) {
            $post = $this->request->post();
            
            $data = [];
            $data['email'] = $post['email'];
            $data['nickname'] = $post['nickname'];
            $data['avatar'] = $post['avatar'];

            $status = AdminModel::where([
                    'id' => $adminInfo['id'],
                ])
                ->data($data)
                ->update();
            
            if ($status === false) {
                $this->error(__('修改失败！'));
            }
            
            $this->success(__("修改成功！"));
        } else {
            $id = $adminInfo['id'];
            $data = AdminModel::where([
                "id" => $id,
            ])->find();
            if (empty($data)) {
                $this->error(__('该信息不存在！'));
            }
            $this->assign("data", $data);
            return $this->fetch();
        }
    }

    /**
     * 管理员密码修改
     *
     * @create 2019-7-2
     * @author deatil
     */
    public function password()
    {
        $adminInfo = env('admin_info');
        
        if ($this->request->isPost()) {
            $post = $this->request->post();
            
            // 验证数据
            $rule = [
                'password|'.__('旧密码') => 'require|length:32',
                'password2|'.__('新密码') => 'require|length:32',
                'password2_confirm|'.__('确认新密码') => 'require|length:32',
            ];
            $result = $this->validate($post, $rule);
            if (true !== $result) {
                return $this->error($result);
            }
            
            if (!isset($post['password']) || empty($post['password'])) {
                $this->error(__('请填写旧密码！'));
            }

            if (!isset($post['password2']) || empty($post['password2'])) {
                $this->error(__('请填写新密码！'));
            }

            if (!isset($post['password2_confirm']) || empty($post['password2_confirm'])) {
                $this->error(__('请填写确认密码！'));
            }
            
            if ($post['password2'] != $post['password2_confirm']) {
                $this->error(__('确认密码错误！'));
            }
            
            if ($post['password2'] == $post['password']) {
                $this->error(__('请确保新密码与旧密码不同'));
            }
            
            if (!AdminFacade::checkPassword($adminInfo['username'], $post['password'])) {
                $this->error(__('旧密码错误！'));
            }

            $passwordinfo = lake_encrypt_password($post['password2']); //对密码进行处理
            
            $data = [];
            $data['encrypt'] = $passwordinfo['encrypt'];
            $data['password'] = $passwordinfo['password'];

            $status = AdminModel::where([
                'id' => $adminInfo['id'],
            ])->update($data);
            
            if ($status === false) {
                $this->error(__('修改密码失败！'));
            }
            
            AdminFacade::logout();
            
            $this->success(__("修改密码成功！"));
        } else {
            $data = AdminModel::where([
                "id" => $adminInfo['id'],
            ])->find();
            if (empty($data)) {
                $this->error(__('信息不存在！'));
            }
            $this->assign("data", $data);
            return $this->fetch();
        }
    }

}
