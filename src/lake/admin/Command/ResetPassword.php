<?php

namespace Lake\Admin\Command;

use think\facade\Db;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\console\Table;

use Lake\Admin\Model\Admin as AdminModel;

/**
 * 重设密码
 *
 * php think lake-admin:reset-password
 *
 * @create 2021-1-27
 * @author deatil
 */
class ResetPassword extends Command
{
    /**
     * 配置
     */
    protected function configure()
    {
        $this
            ->setName('lake-admin:reset-password')
            ->setDescription('You will reset an admin password.');
    }

    /**
     * 执行
     */
    protected function execute(Input $input, Output $output)
    {
        $output->newLine();
        
        $adminid = $output->ask($input, '> Before, you need enter an adminid');
        if (empty($adminid)) {
            $output->error('> Adminid is empty!');
            return false;
        }
        
        $password = $this->output->ask($input, '> Please enter a password');
        if (empty($password)) {
            $output->error('> Password is empty!');
            return false;
        }
        
        // 手动加载配置
        $appPath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
        $files = array_merge([], glob($appPath . 'config' . DIRECTORY_SEPARATOR . '*' . $this->app->getConfigExt()));
        foreach ($files as $file) {
            $this->app->config->load($file, pathinfo($file, PATHINFO_FILENAME));
        }
        
        $password = md5($password);
        $passwordInfo = lake_encrypt_password($password);
        
        $data = [];
        $data['encrypt'] = $passwordInfo['encrypt'];
        $data['password'] = $passwordInfo['password'];

        $status = AdminModel::where([
            'id' => $adminid,
        ])->update($data);
        
        if ($status === false) {
            $output->error('> Reset password is error!');
            return false;
        }
        
        $output->info('Reset password successfully!');
    }

}
