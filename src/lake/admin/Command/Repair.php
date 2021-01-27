<?php

namespace Lake\Admin\Command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

use Lake\Symlink;

/**
 * lake-admin：修复网站更改路径后静态文件失效问题
 *
 * php think lake-admin:repair
 *
 * @create 2020-7-23
 * @author deatil
 */
class Repair extends Command
{
    /**
     * 配置
     */
    protected function configure()
    {
        $this
            ->setName('lake-admin:repair')
            ->setDescription('you will repair lake-admin.');
    }

    /**
     * 执行
     */
    protected function execute(Input $input, Output $output)
    {
        $this->adminStatic($input, $output);
    }

    /**
     * 系统静态文件
     */
    protected function adminStatic(Input $input, Output $output)
    {
        // 创建静态文件软链接
        $adminStaticPath = env('lake_admin_app_path') . DIRECTORY_SEPARATOR 
            . 'resource' . DIRECTORY_SEPARATOR
            . 'static' . DIRECTORY_SEPARATOR
            . 'admin' . DIRECTORY_SEPARATOR;
        $staticPath = root_path() . 'public' . DIRECTORY_SEPARATOR 
            . 'static' . DIRECTORY_SEPARATOR
            . 'admin' . DIRECTORY_SEPARATOR;
        
        // 移除旧的链接
        Symlink::remove($staticPath);
        
        // 创建新的链接
        Symlink::make($adminStaticPath, $staticPath);
       
        $output->info("Repair lake-admin successfully!");
    }
}
