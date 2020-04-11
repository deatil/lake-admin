<?php

namespace app\admin\model;

use think\facade\Db;
use think\facade\Config;
use think\Model;

/**
 * 公共模型
 */
class ModelBase extends Model
{
    /**
     * 删除表
     * @param string $tablename 不带表前缀的表名
     * @return type
     */
    public function drop_table($table)
    {
        $dbPrefix = app()->db->getConnection()->getConfig('prefix');
        $table = $dbPrefix . strtolower($table);
        return Db::query("DROP TABLE $table");
    }

    /**
     * 检查表是否存在
     * $table 不带表前缀
     */
    public function table_exists($table)
    {
        $dbPrefix = app()->db->getConnection()->getConfig('prefix');
        $table = $dbPrefix . strtolower($table);
        if (true == Db::query("SHOW TABLES LIKE '{$table}'")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检查字段是否存在
     * $table 不带表前缀
     */
    public function field_exists($table, $field)
    {
        $fields = $this->get_fields($table);
        return array_key_exists($field, $fields);
    }

    /**
     * 获取表字段
     * $table 不带表前缀
     */
    public function get_fields($table)
    {
        $fields = [];
        $dbPrefix = app()->db->getConnection()->getConfig('prefix');
        $table = $dbPrefix . strtolower($table);
        $data = Db::query("SHOW COLUMNS FROM $table");
        foreach ($data as $v) {
            $fields[$v['Field']] = $v['Type'];
        }
        return $fields;
    }
}
