<?php

namespace app\admin\command;

use think\Db;

use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

use lake\File;
use lake\Sql;

use lake\Module;

/**
 * lake-admin 插件cmd安装-静态文件移动
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
        // 使用 getArgument() 取出参数值
		// $dbpre = $input->getArgument('dbpre');
		
		// 使用 getOption() 取出选项值
		$dbpre = $input->getOption('dbpre');
		
		if (empty($dbpre)) {
			$dbpre = config('database.prefix');
		}
		
		// 数据库配置
		$database = config('database.database');
		$databaseCharset = config('database.charset');
		
		if (empty($database)) {
			$output->info("lake-admin tip: place set database!");
			return false;
		}
		if (empty($databaseCharset)) {
			$output->info("lake-admin tip: place set database charset!");
			return false;
		}
		
		// 创建数据库
		$dbConfig = config('database.');
		$db = Db::connect([
			'type' => "mysql",
			'hostname' => $dbConfig['hostname'],
			'username' => $dbConfig['username'],
			'password' => $dbConfig['password'],
			'hostport' => $dbConfig['hostport'],
			'charset' => $dbConfig['charset'],
		]);
		$db->execute("CREATE DATABASE IF NOT EXISTS `".$database."` DEFAULT CHARACTER SET ".$databaseCharset." COLLATE ".$databaseCharset."_unicode_ci;");
		
		// 导入数据库
		$Module = new Module();
		$sqlFile = env('lake_admin_app_path') 
			. 'lake' . DIRECTORY_SEPARATOR
			. 'data' . DIRECTORY_SEPARATOR
			. 'lake.sql';
        if (!file_exists($sqlFile)) {
			$output->info("lake-admin tip: sql is not exist!");
			return false;
		}
		
		$sqlStatement = Sql::getSqlFromFile($sqlFile);
		if (empty($sqlStatement)) {
			$output->info("lake-admin tip: sql is empty!");
            return false;
		}
		
		$db2 = Db::connect([
			'type' => "mysql",
			'hostname' => $dbConfig['hostname'],
			'database' => $dbConfig['database'],
			'username' => $dbConfig['username'],
			'password' => $dbConfig['password'],
			'hostport' => $dbConfig['hostport'],
			'charset' => $dbConfig['charset'],
			'prefix' => $dbpre,
		]);
		
		foreach ($sqlStatement as $value) {
			try {
				$value = str_replace([
					'pre__',
				], [
					$dbpre,
				], trim($value));
				$db2->execute($value);
			} catch (\Exception $e) {
				$output->info("lake-admin tip: import sql is error!");
				return false;
			}
		}
		
		// 复制静态文件
		$admin_static_path = env('lake_admin_app_path') . DIRECTORY_SEPARATOR 
			. 'lake' . DIRECTORY_SEPARATOR
			. 'static' . DIRECTORY_SEPARATOR;
        $static_path = env('root_path') . 'public' . DIRECTORY_SEPARATOR 
			. 'static' . DIRECTORY_SEPARATOR;
		File::copyDir($admin_static_path, $static_path);
 			
		// 复制lak-admin附件
		$from_path = env('lake_admin_app_path') . DIRECTORY_SEPARATOR 
			. 'lake' . DIRECTORY_SEPARATOR 
			. 'data' . DIRECTORY_SEPARATOR
			. 'public' . DIRECTORY_SEPARATOR
			. 'uploads' . DIRECTORY_SEPARATOR;
		$to_path = env('root_path') 
			. 'public' . DIRECTORY_SEPARATOR 
			. 'uploads' . DIRECTORY_SEPARATOR;
		File::copyDir($from_path, $to_path);
       
		$output->info("Install lake-admin Successed!");
    }
}
