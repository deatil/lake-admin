<?php

namespace Lake\Admin\Command;

use think\facade\Db;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\console\Table;

use Lake\File;
use Lake\Sql;
use Lake\Symlink;

use Lake\Admin\Module\Module;

/**
 * lake-admin 安装
 *
 * php think lake-admin:install
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
            // ->addOption('dbpre', null, Option::VALUE_REQUIRED, 'db pre setting')            
            ->setDescription('You will install lake-admin.');
    }

    /**
     * 执行
     *
     * @create 2019-10-5
     * @author deatil
     */
    protected function execute(Input $input, Output $output)
    {
        $output->writeln('');
        $output->highlight('Lake-admin version v' . app()->config->get('lake.version') . "\n");
        
        $isCheckFunc = $output->ask($input, '> Before install, you need check system\'fuctions (Y/n)?') ?: 'y';
        if ($isCheckFunc === 'y') {
            $this->lakeAdminCheckFunction($input, $output);
        } else {
            $output->info('> You not check and not install!');
            return false;
        }
        
        $isStart = $this->output->ask($input, '> You will install lake-admin (Y/n)?') ?: 'y';
        if ($isStart === 'y') {
            $this->lakeAdminInstall($input, $output);
        } else {
            $output->info('> You not install!');
        }
    }

    /**
     * 检测扩展
     *
     * @create 2020-8-11
     * @author deatil
     */
    protected function lakeAdminCheckFunction(Input $input, Output $output)
    {
        $output->info("> System's functions is checking...\n");
        
        $table = new Table();
        
        $header = ['Function', 'Status'];
        $table->setHeader($header, Table::ALIGN_LEFT);
        
        $rows = [];
        
        $items = $this->checkFunc();
        foreach ($items as $v) {
            if ($v[2] == 'no') {
                $rows[] = [$v[0], 'no'];
            } else {
                $rows[] = [$v[0], 'yes'];
            }
        }
        $table->setRows($rows, Table::ALIGN_LEFT);
        $table->setStyle('default'); // default,compact,markdown,borderless,box,box-double
        
        $this->table($table);
    }

    /**
     * 执行
     *
     * @create 2019-10-5
     * @author deatil
     */
    protected function lakeAdminInstall(Input $input, Output $output)
    {
        $output->info("> Lake-admin is installing...\n");
        
        $installLockFile = root_path() . 'install.lock';
        if (file_exists($installLockFile)) {
            $output->writeln("<info>Lake-admin tip: lake-admin is installed! Please unlink root 'install.lock' file.</info>");
            return false;
        }
        
        // 使用 getArgument() 取出参数值
        // $dbpre = $input->getArgument('dbpre');
        
        // 使用 getOption() 取出选项值
        // $dbpre = $input->getOption('dbpre');
        
        // 手动添加
        $dbpre = $this->output->ask($input, '> You can set a dbpre') ?: '';
        
        // 当前连接数据库配置
        $dbConfig = app()->db->connect()->getConfig();
        
        // 数据库配置
        $database = $dbConfig['database'];
        $databaseCharset = $dbConfig['charset'];
        
        if (empty($database)) {
            $output->writeln("<info>Lake-admin tip: place set database config!</info>");
            return false;
        }
        if (empty($databaseCharset)) {
            $output->writeln("<info>Lake-admin tip: place set database charset config!</info>");
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
        $sqlFile = env('lake_admin_app_path') . DIRECTORY_SEPARATOR 
            . 'resource' . DIRECTORY_SEPARATOR
            . 'data' . DIRECTORY_SEPARATOR
            . 'database' . DIRECTORY_SEPARATOR
            . 'lake.sql';
        if (!file_exists($sqlFile)) {
            $output->writeln("<info>Lake-admin tip: sql is not exist!</info>");
            return false;
        }
        
        $sqlStatement = Sql::getSqlFromFile($sqlFile);
        if (empty($sqlStatement)) {
            $output->writeln("<info>Lake-admin tip: sql is empty!</info>");
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
                $output->writeln("<info>Lake-admin tip: import sql is error!</info>");
                return false;
            }
        }
        
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

        // 复制文件
        $fromPath = env('lake_admin_app_path') . DIRECTORY_SEPARATOR 
            . 'resource' . DIRECTORY_SEPARATOR 
            . 'data' . DIRECTORY_SEPARATOR
            . 'root' . DIRECTORY_SEPARATOR;
        $toPath = root_path();
        File::copyDir($fromPath, $toPath);
        
        // 添加安装锁定文件
        file_put_contents($installLockFile, '');
       
        $output->info("Install lake-admin Successed!");
    }
    
    /**
     * 函数及扩展检查
     * @return array
     *
     * @create 2020-8-11
     * @author deatil
     */
    private function checkFunc()
    {
        $items = [
            ['pdo', '支持', 'yes', '类'],
            ['pdo_mysql', '支持', 'yes', '模块'],
            ['zip', '支持', 'yes', '模块'],
            ['fileinfo', '支持', 'yes', '模块'],
            ['curl', '支持', 'yes', '模块'],
            ['xml', '支持', 'yes', '函数'],
            ['file_get_contents', '支持', 'yes', '函数'],
            ['mb_strlen', '支持', 'yes', '函数'],
            ['gzopen', '支持', 'yes', '函数'],
        ];

        foreach ($items as &$v) {
            if (('类' == $v[3] && !class_exists($v[0])) 
                || ('模块' == $v[3] && !extension_loaded($v[0])) 
                || ('函数' == $v[3] && !function_exists($v[0])) 
            ) {
                $v[1] = '不支持';
                $v[2] = 'no';
            }
        }

        return $items;
    }

}
