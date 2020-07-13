<?php

namespace app\admin\command;

use think\facade\Db;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

use lake\File;
use lake\Sql;
use lake\Symlink;

use lake\Module;

/**
 * lake-admin 插件cmd安装
 *
 * php think lake-admin:install [--dbpre lake_]
 *
 * @create 2019-10-5
 * @author deatil
 */
class LakeAdminInstall extends Command
{

    /**
     * 配置
     *
     * @create 2019-10-5
     * @author deatil
     */
    protected function configure()
    {
        $this
            ->setName('lake-admin:install')
            // 配置一个参数
            // ->addArgument('dbpre', Argument::REQUIRED, 'db pre setting')            
            // 配置一个选项
            ->addOption('dbpre', null, Option::VALUE_REQUIRED, 'db pre setting')            
            ->setDescription('you will install lake-admin.');
    }

    /**
     * 执行
     *
     * @create 2019-10-5
     * @author deatil
     */
    protected function execute(Input $input, Output $output)
    {
        $installLockFile = root_path() . 'install.lock';
        if (file_exists($installLockFile)) {
            $output->info("<info>lake-admin tip:</info> lake-admin is installed!");
            return false;
        }
        
        // 使用 getArgument() 取出参数值
        // $dbpre = $input->getArgument('dbpre');
        
        // 使用 getOption() 取出选项值
        $dbpre = $input->getOption('dbpre');
        
        // 当前连接数据库配置
        $dbConfig = app()->db->connect()->getConfig();
        
        // 数据库配置
        $database = $dbConfig['database'];
        $databaseCharset = $dbConfig['charset'];
        
        if (empty($database)) {
            $output->info("<info>lake-admin tip:</info> place set database config!");
            return false;
        }
        if (empty($databaseCharset)) {
            $output->info("<info>lake-admin tip:</info> place set database charset config!");
            return false;
        }
        
        // 创建数据库
        $dbConfig1 = $dbConfig;
        unset($dbConfig1['database']);
        app()->config->set([
            'connections' => [
                'lake-admin-db1' => $dbConfig1,
            ],
        ], 'database');
        $db = Db::connect('lake-admin-db1');
        $db->execute("CREATE DATABASE IF NOT EXISTS `".$database."` DEFAULT CHARACTER SET ".$databaseCharset." COLLATE ".$databaseCharset."_unicode_ci;");
        
        // 导入数据库
        $Module = new Module();
        $sqlFile = env('lake_admin_app_path') 
            . 'lake' . DIRECTORY_SEPARATOR
            . 'data' . DIRECTORY_SEPARATOR
            . 'database' . DIRECTORY_SEPARATOR
            . 'lake.sql';
        if (!file_exists($sqlFile)) {
            $output->info("<info>lake-admin tip:</info> sql is not exist!");
            return false;
        }
        
        $sqlStatement = Sql::getSqlFromFile($sqlFile);
        if (empty($sqlStatement)) {
            $output->info("<info>lake-admin tip:</info> sql is empty!");
            return false;
        }
        
        // 执行sql
        $dbConfig2 = $dbConfig;
        if (!empty($dbpre)) {
            $dbConfig2['prefix'] = $dbpre;
        }
        app()->config->set([
            'connections' => [
                'lake-admin-db2' => $dbConfig2,
            ],
        ], 'database');
        $db2 = Db::connect('lake-admin-db2');
        
        $dbPrefix = $dbConfig2['prefix'];
        foreach ($sqlStatement as $value) {
            try {
                $value = str_replace([
                    'pre__',
                ], [
                    $dbPrefix,
                ], trim($value));
                $db2->execute($value);
            } catch (\Exception $e) {
                $output->info("<info>lake-admin tip:</info> import sql is error!");
                return false;
            }
        }
        
        // 创建静态文件软链接
        $adminStaticPath = env('lake_admin_app_path') 
            . 'lake' . DIRECTORY_SEPARATOR
            . 'static' . DIRECTORY_SEPARATOR
            . 'admin' . DIRECTORY_SEPARATOR;
        $staticPath = root_path() . 'public' . DIRECTORY_SEPARATOR 
            . 'static' . DIRECTORY_SEPARATOR
            . 'admin' . DIRECTORY_SEPARATOR;
        Symlink::make($adminStaticPath, $staticPath);

        // 复制文件
        $fromPath = env('lake_admin_app_path') . DIRECTORY_SEPARATOR 
            . 'lake' . DIRECTORY_SEPARATOR 
            . 'data' . DIRECTORY_SEPARATOR
            . 'root' . DIRECTORY_SEPARATOR;
        $toPath = root_path();
        File::copyDir($fromPath, $toPath);
        
        // 添加安装锁定文件
        file_put_contents($installLockFile, '');
       
        $output->info("Install lake-admin Successed!");
    }
}
