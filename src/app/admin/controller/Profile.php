<?php

namespace app\admin\controller;

use app\admin\model\Admin as AdminModel;
use app\admin\service\Admin as AdminService;

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
        
        $AdminModel = new AdminModel;
        if ($this->request->isPost()) {
            $post = $this->request->post();
            
            $data = [];
            $data['email'] = $post['email'];
            $data['nickname'] = $post['nickname'];
            $data['avatar'] = $post['avatar'];

            $status = $AdminModel
                ->where([
                    'id' => $post['id'],
                ])
                ->data($data)
                ->update();
            
            if ($status === false) {
                $this->error('修改失败！');
            }
            
            $this->success("修改成功！");
        } else {
            $id = $adminInfo['id'];
            $data = $AdminModel->where([
                "id" => $id,
            ])->find();
            if (empty($data)) {
                $this->error('该信息不存在！');
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
        
        $AdminModel = new AdminModel;
        $AdminService = new AdminService;
        if ($this->request->isPost()) {
            $post = $this->request->post();
            
            // 验证数据
            $rule = [
                'password|旧密码' => 'require|length:32',
                'password2|新密码' => 'require|length:32',
                'password2_confirm|确认新密码' => 'require|length:32',
            ];
            $result = $this->validate($post, $rule);
            if (true !== $result) {
                return $this->error($result);
            }
            
            if (empty($post) || !isset($post['id']) || !is_array($post)) {
                $this->error('没有修改的数据！');
            }
            
            if (!isset($post['password']) || empty($post['password'])) {
                $this->error('请填写旧密码！');
            }

            if (!isset($post['password2']) || empty($post['password2'])) {
                $this->error('请填写新密码！');
            }

            if (!isset($post['password2_confirm']) || empty($post['password2_confirm'])) {
                $this->error('请填写确认密码！');
            }
            
            if ($post['password2'] != $post['password2_confirm']) {
                $this->error('确认密码错误！');
            }
            
            if ($post['password2'] == $post['password']) {
                $this->error('请确保新密码与旧密码不同');
            }
            
            if (!$AdminService->getUserInfo($adminInfo['username'], $post['password'])) {
                $this->error('旧密码错误！');
            }

            $passwordinfo = lake_encrypt_password($post['password2']); //对密码进行处理
            
            $data = [];
            $data['encrypt'] = $passwordinfo['encrypt'];
            $data['password'] = $passwordinfo['password'];

            $status = $AdminModel->where([
                'id' => $post['id'],
            ])->update($data);
            
            if ($status === false) {
                $this->error('修改密码失败！');
            }
            
            $this->success("修改密码成功！");
        } else {
            $id = $adminInfo['id'];
            $data = $AdminModel->where([
                "id" => $id,
            ])->find();
            if (empty($data)) {
                $this->error('信息不存在！');
            }
            $this->assign("data", $data);
            return $this->fetch();
        }
    }

}
