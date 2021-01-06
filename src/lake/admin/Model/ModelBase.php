<?php

namespace Lake\Admin\Model;

use think\Model;
use think\facade\Db;

/**
 * 公共模型
 */
abstract class ModelBase extends Model
{
    // 设置当前模型对应的数据表名称
    // protected $name = '';
    
    // 设置当前模型对应的完整数据表名称
    // protected $table = '';
    
    // 动态切换后缀，定义默认的表后缀（默认查询中文数据）
    // protected $suffix = '_cn';
    
    // 设置当前模型对应的主键名
    // protected $pk = 'uid';
    
    // 设置当前模型的数据库连接
    // protected $connection = 'db_config';
    
    // 模型使用的查询类名称
    // protected $query = '';
    
    // 模型允许写入的字段列表（数组）
    // protected $field = [];
    
    // 模型对应数据表字段及类型
    // protected $schema = '';
    
    // 模型需要自动转换的字段及类型
    // protected $type = '';
    
    // 是否严格区分字段大小写（默认为true）
    // protected $strict = true;
    
    // 数据表废弃字段（数组）
    // protected $disuse = [];
    
    // 模型初始化
    protected static function init()
    {
        // 初始化内容
    }

    /**
     * 获取当前模型名称
     * @access public
     * @return string
     */
    public static function getModelName()
    {
        $model = new static();
        return $model->getName();
    }
    
    /**
     * 删除表
     * @param string $tablename 不带表前缀的表名
     * @return type
     */
    protected function dropTable($table)
    {
        $dbPrefix = self::$db->connect($this->connection)->getConfig('prefix');
        $table = $dbPrefix . strtolower($table);
        return Db::query("DROP TABLE $table");
    }

    /**
     * 检查表是否存在
     * $table 不带表前缀
     */
    protected function tableExists($table)
    {
        $dbPrefix = self::$db->connect($this->connection)->getConfig('prefix');
        $table = $dbPrefix . strtolower($table);
        if (true == Db::query("SHOW TABLES LIKE '{$table}'")) {
            return true;
        }
        
        return false;
    }

    /**
     * 检查字段是否存在
     * $table 不带表前缀
     */
    protected function fieldExists($table, $field)
    {
        $fields = $this->getFields($table);
        return array_key_exists($field, $fields);
    }

    /**
     * 获取表字段
     * $table 不带表前缀
     */
    protected function getFields($table)
    {
        $fields = [];
        $dbPrefix = self::$db->connect($this->connection)->getConfig('prefix');
        $table = $dbPrefix . strtolower($table);
        $data = Db::query("SHOW COLUMNS FROM $table");
        if (!empty($data)) {
            foreach ($data as $v) {
                $fields[$v['Field']] = $v['Type'];
            }
        }
        return $fields;
    }
}
