<?php

namespace Lake\Admin\Module;

use think\Container;
use think\facade\Db;

use Lake\Sql;

use Lake\Admin\Model\Module as ModuleModel;

/**
 * 工具
 *
 * @create 2020-9-16
 * @author deatil
 */
class Tool
{
    /**
     * 解析格式化模块配置为正确配置
     * @param type $name 模块名(目录名)
     * @return array
     */
    public static function parseModuleConfig($moduleConfig = [])
    {
        $defaultConfig = [
            // 模块目录
            'module' => '',
            // 模块名称
            'name' => '',
            // 模块简介
            'introduce' => '',
            // 模块作者
            'author' => '',
            // 作者地址
            'authorsite' => '',
            // 作者邮箱
            'authoremail' => '',
            // 版本号，请不要带除数字外的其他字符
            'version' => '',
            // 适配最低lake-admin版本，
            'adaptation' => '',
            // 模块路径，默认为空，自定义包插件可填写
            'path' => '',
            // 依赖模块
            'need_module' => [],
            // 设置
            'setting' => [],
            // 事件类
            'event' => [],
            // 菜单
            'menus' => [],
            // 数据表，不用加表前缀
            'tables' => [],
            // 演示数据
            'demo' => 0,
        ];
        
        $config = array_merge($defaultConfig, $moduleConfig);
        
        return $config;
    }
    
    /**
     * 检查依赖
     * @param string $type 类型：module
     * @param array $data 检查数据
     * @return array
     */
    public static function checkDependence($data = [])
    {
        $need = [];
        if (empty($data) || !is_array($data)) {
            return $need;
        }
        
        foreach ($data as $key => $value) {
            if (!isset($value[2])) {
                $value[2] = '=';
            }
            
            // 当前版本
            $currVersion = ModuleModel::where('module', $value[0])->value('version');
            
            $result = version_compare($currVersion, $value[1], $value[2]);
            $need[$key] = [
                'module' => $value[0],
                'version' => $currVersion ? $currVersion : '未安装',
                'version_need' => $value[2] . $value[1],
                'result' => $result ? '<i class="iconfont icon-success text-success"></i>' : '<i class="iconfont icon-delete text-danger"></i>',
            ];
        }
        
        return $need;
    }
    
    /**
     * 执行数据库脚本
     * @param string $sqlFile 数据库脚本文件
     * @param string $dbPre 数据库前缀
     * @return boolean
     */
    public static function runSQL($sqlFile = '', $dbPre = '')
    {
        if (empty($sqlFile)) {
            return false;
        }
        
        if (!file_exists($sqlFile)) {
            return false;
        }
        
        $sqlStatement = Sql::getSqlFromFile($sqlFile);
        if (empty($sqlStatement)) {
            return false;
        }
        
        if (empty($dbPre)) {
            $dbPre = app()->db->connect()->getConfig('prefix');
        }
    
        foreach ($sqlStatement as $value) {
            try {
                $value = str_replace([
                    'pre__',
                ], [
                    $dbPre,
                ], $value);
                Db::execute($value);
            } catch (\Exception $e) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * 执行安装脚本
     * @param type $name 模块名(目录名)
     * @return boolean
     */
    public static function runScript(
        $name = '', 
        $type = 'run', 
        $dir = 'Install'
    ) {
        if (empty($name)) {
            return false;
        }
        
        // 检查是否有安装脚本
        $class = "\\app\\{$name}\\{$dir}";
        if (!class_exists($class)) {
            return true;
        }
        
        $installObj = Container::getInstance()->make($class);
        
        if (!method_exists($installObj, $type)) {
            return true;
        }
        
        // 执行安装
        if (false === Container::getInstance()->invoke([$installObj, $type], [])) {
            return false;
        }
        
        return true;
    }
    
}
