<?php

namespace Lake\Admin\Model;

use think\facade\Filesystem;

/**
 * 附件模型
 *
 * @create 2019-8-5
 * @author deatil
 */
class Attachment extends ModelBase
{
    // 设置当前模型对应的数据表名称
    protected $name = 'lakeadmin_attachment';
    
    // 设置主键名
    protected $pk = 'id';
    
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    
    // 时间字段取出后的默认时间格式
    protected $dateFormat = false;

    public static function onBeforeInsert($model)
    {
        $id = md5(mt_rand(10000, 99999) . time() . mt_rand(10000, 99999) . microtime());
        $model->setAttr('id', $id);
        
        $model->setAttr('add_time', time());
        $model->setAttr('add_ip', request()->ip());
    }

    public function getSizeAttr($value)
    {
        return lake_format_bytes($value);
    }

    public function getUriAttr()
    {
        return static::objectUrl($this->path, $this->driver);
    }

    public function getRealpathAttr()
    {
        return static::objectPath($this->path, $this->driver);
    }
    
    public static function getFilesystemDefaultDisk()
    {
        return config('app.upload_disk');
    }
    
    public static function filesystem($disk = '')
    {
        if (empty($disk)) {
            $disk = static::getFilesystemDefaultDisk();
        }
        
        return Filesystem::disk($disk);
    }
    
    public static function putContents(
        $path, 
        $contents, 
        array $config = []
    ) {
        $path = trim($path, '/');

        $result = static::filesystem()->put($path, $contents, $config);

        return $result ? $path : false;
    }
    
    public static function putStream(
        $path, 
        $fileStream, 
        array $config = []
    ) {
        $path = trim($path, '/');
        
        $stream = fopen($fileStream, 'r');

        $result = static::filesystem()->putStream($path, $stream, $config);

        return $result ? $path : false;
    }
    
    public static function objectPath($path = '', $disk = '')
    {
        return static::filesystem($disk)->path($path);
    }
    
    public static function objectUrl($path = '', $disk = '')
    {
        if (empty($disk)) {
            $disk = static::getFilesystemDefaultDisk();
        }
        
        $url = Filesystem::getDiskConfig($disk, 'url', '');
        return rtrim($url, '/') . '/' . ltrim($path, '/');
    }
}

