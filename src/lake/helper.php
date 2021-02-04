<?php

use think\facade\Event;
use think\facade\Lang;

use Lake\Arr;
use Lake\Str;
use Lake\Http;
use Lake\Random;

use Lake\Admin\Http\Traits\Json as HttpJsonTrait;
use Lake\Admin\Facade\Password as PasswordFacade;
use Lake\Admin\Service\AdminAuth as AdminAuthService;
use Lake\Admin\Service\Module as ModuleService;
use Lake\Admin\Service\Config as ConfigService;
use Lake\Admin\Service\Attachment as AttachmentService;


if (!function_exists('__')) {

    /**
     * 获取语言变量值
     * @param string $name 语言变量名
     * @param array  $vars 动态变量值
     * @param string $lang 语言
     * @return mixed
     */
    function __($name, $vars = [], $lang = '')
    {
        if (is_numeric($name) || !$name) {
            return $name;
        }
        if (!is_array($vars)) {
            $vars = func_get_args();
            array_shift($vars);
            $lang = '';
        }
        return Lang::get($name, $vars, $lang);
    }
}

if (!function_exists('lake_success_json')) {
    /*
     * 返回成功JSON
     *
     * @create 2020-8-22
     * @author deatil
     */
    function lake_success_json($msg = '获取成功', $data = null, $code = 0, $header = []) {
        return (new class {
            use HttpJsonTrait;
            
            public function json($msg = '获取成功', $data = null, $code = 0, $header = [])
            {
                return $this->successJson($msg, $data, $code, $header);
            }
        })->json($msg, $data, $code, $header);
    }
}

if (!function_exists('lake_error_json')) {
    /*
     * 返回失败JSON
     *
     * @create 2020-8-22
     * @author deatil
     */
    function lake_error_json($msg = null, $code = 1, $data = [], $header = []) {
        return (new class {
            use HttpJsonTrait;
            
            public function json($msg = null, $code = 1, $data = [], $header = [])
            {
                return $this->errorJson($msg, $code, $data, $header);
            }
        })->json($msg, $code, $data, $header);
    }
}

if (!function_exists('lake_app')) {
    /**
     * 快速获取容器中的实例 支持依赖注入
     * @param string $name        类名或标识 默认获取当前应用实例
     * @param array  $args        参数
     * @param bool   $newInstance 是否每次创建新的实例
     * @return object|App
     */
    function lake_app($name = '', $args = [], $newInstance = false)
    {
        return app($name, $args, $newInstance);
    }
}

if (!function_exists('lake_p')) {
    /**
     * 打印输出数据到文件
     * @param mixed $data 输出的数据
     * @param boolean $force 强制替换
     * @param string|null $file 文件名称
     */
    function lake_p($data, $force = false, $file = null)
    {
        if (is_null($file)) {
            $file = runtime_path() . date('Ymd') . '.txt';
        }
        Arr::printr($data, $force, $file);
    }
}

if (!function_exists('lake_var_export')) {
    /**
     * 返回数组
     * @param array $arr 输出的数据
     * @param string $blankspace 空格
     * @return string
     */
    function lake_var_export($arr = [], $blankspace = '')
    {
        $ret = Arr::varExport($arr, $blankspace);
        return $ret;
    }
}

if (!function_exists('lake_json_encode')) {
    /**
     * 返回正常的中文JSON
     * @param array $data 输出的数据
     * @return string
     */
    function lake_json_encode($data = [])
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
    }
}

if (!function_exists('lake_data_auth_sign')) {
    /**
     * 数据签名认证
     * @param  array  $data 被认证的数据
     * @return string       签名
     */
    function lake_data_auth_sign($data)
    {
        return Arr::dataAuthSign($data);
    }
}

if (!function_exists('lake_int_to_string')) {
    /**
     * select返回的数组进行整数映射转换
     *
     * @param array $map  映射关系二维数组  array(
     *                                          '字段名1'=>array(映射关系数组),
     *                                          '字段名2'=>array(映射关系数组),
     *                                           ......
     *                                       )
     * @return array
     *
     *  array(
     *      array('id'=>1,'title'=>'标题','status'=>'1','status_text'=>'正常')
     *      ....
     *  )
     *
     */
    function lake_int_to_string($data, $map = ['status' => [1 => '正常', -1 => '删除', 0 => '禁用', 2 => '未审核', 3 => '草稿']])
    {
        return Arr::intToString($data, $map);
    }
}

if (!function_exists('lake_str2arr')) {
    /**
     * 字符串转换为数组，主要用于把分隔符调整到第二个参数
     * @param  string $str  要分割的字符串
     * @param  string $glue 分割符
     * @return array
     */
    function lake_str2arr($str, $glue = ',')
    {
        return Str::str2arr($str, $glue);
    }
}

if (!function_exists('lake_arr2str')) {
    /**
     * 数组转换为字符串，主要用于把分隔符调整到第二个参数
     * @param  array  $arr  要连接的数组
     * @param  string $glue 分割符
     * @return string
     */
    function lake_arr2str($arr, $glue = ',')
    {
        return Str::arr2str($arr, $glue);
    }
}

if (!function_exists('lake_to_time')) {
    /**
     * 时间转换
     * @param array $arr        传入数组
     * @param string $field     字段名
     * @param string $format    格式
     * @return mixed
     */
    function lake_to_time(&$arr, $field = 'time', $format = 'Y-m-d H:i:s')
    {
        if (isset($arr[$field])) {
            $arr[$field] = date($format, $arr[$field]);
        }
        return $arr;
    }
}

if (!function_exists('lake_to_ip')) {
    /**
     * ip转换
     * @param array $arr        传入数组
     * @param string $field     字段名
     * @return mixed
     */
    function lake_to_ip(&$arr, $field = 'ip')
    {
        if (isset($arr[$field])) {
            $arr[$field] = long2ip($arr[$field]);
        }
        return $arr;
    }
}

if (!function_exists('lake_to_guid_string')) {
    /**
     * 根据PHP各种类型变量生成唯一标识号
     * @param mixed $mix 变量
     * @return string
     */
    function lake_to_guid_string($mix)
    {
        return Str::toGuidString($mix);
    }
}

if (!function_exists('lake_list_sort_by')) {
    /**
     * 对查询结果集进行排序
     * @access public
     * @param array $list 查询结果
     * @param string $field 排序的字段名
     * @param array $sortby 排序类型
     * asc正向排序 desc逆向排序 nat自然排序
     * @return array
     */
    function lake_list_sort_by($list, $field, $sortby = 'asc')
    {
        return Arr::sort($list, $field, $sortby);
    }
}

if (!function_exists('lake_list_to_tree')) {
    /**
     * 把返回的数据集转换成Tree
     * @param array $list 要转换的数据集
     * @param string $pid parent标记字段
     * @param string $level level标记字段
     * @return array
     */
    function lake_list_to_tree(
        $list, 
        $pk = 'id', 
        $pid = 'parentid', 
        $child = '_child', 
        $root = 0
    ) {
        $tree = Arr::listToTree($list, $pk, $pid, $child, $root);
        return $tree;
    }
}

if (!function_exists('lake_parse_attr')) {
    /**
     * 解析配置
     * @param string $value 配置值
     * @return array|string
     */
    function lake_parse_attr($value = '')
    {
        return Arr::parseAttr($value);
    }
}
    
if (!function_exists('lake_parse_fieldlist')) {
    /**
     * 解析配置信息
     *
     * @create 2019-11-16
     * @author deatil
     */
    function lake_parse_fieldlist($data = '')
    {
        return Arr::parseFieldList($data);
    }
}

if (!function_exists('lake_time_format')) {
    /**
     * 时间戳格式化
     * @param int $time
     * @return string 完整的时间显示
     */
    function lake_time_format($time = null, $type = 0)
    {
        $types = ['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d'];
        $time = $time === null ? $_SERVER['REQUEST_TIME'] : intval($time);
        return date($types[$type], $time);
    }
}

if (!function_exists('lake_format_bytes')) {
    /**
     * 格式化字节大小
     * @param  number $size      字节数
     * @param  string $delimiter 数字和单位分隔符
     * @return string            格式化后的带单位的大小
     */
    function lake_format_bytes($size, $delimiter = '')
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        for ($i = 0; $size >= 1024 && $i < 5; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . $delimiter . $units[$i];
    }
}

if (!function_exists('lake_get_random_string')) {
    /**
     * 产生一个指定长度的随机字符串,并返回给用户
     * @param type $len 产生字符串的长度
     * @return string 随机字符串
     */
    function lake_get_random_string($len = 6)
    {
        return Random::alnum($len);
    }
}

if (!function_exists('lake_is_serialized')) {
    /**
     * 判断是否为序列化
     *
     * @create 2019-7-2
     * @author deatil
     */
    function lake_is_serialized($data) 
    {
        return Str::isSerialized($data);
    }
}

if (!function_exists('lake_str_cut')) {
    /**
     * 字符截取
     * @param $string 需要截取的字符串
     * @param $length 长度
     * @param $dot
     */
    function lake_str_cut($sourcestr, $length, $dot = '...')
    {
        return Str::wordCut($sourcestr, $length, $dot);
    }
}

if (!function_exists('lake_safe_replace')) {
    /**
     * 安全过滤函数
     * @param $string
     * @return string
     */
    function lake_safe_replace($string)
    {
        return Str::safeReplace($string);
    }
}

if (!function_exists('lake_http_down')) {
    /**
     * 下载远程文件，默认保存在temp下
     * @param  string  $url     网址
     * @param  string  $filename    保存文件名
     * @param  integer $timeout 过期时间
     * @param  bool $repalce 是否覆盖已存在文件
     * @return string 本地文件名
     */
    function lake_http_down($url, $filename = "", $timeout = 60)
    {
        if (empty($filename)) {
            return false;
        }
        
        $path = dirname($filename);
        if (!is_dir($path) && !mkdir($path, 0755, true)) {
            return false;
        }
        $url = str_replace(" ", "%20", $url);
        if (function_exists('curl_init')) {
            $options = [
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_RETURNTRANSFER => true,
                // CURLOPT_MAXREDIRS => 2,
                // CURLOPT_FOLLOWLOCATION => 1,
            ];
            
            $temp = Http::get($url, [], $options);
            if (empty($temp)) {
                return false;
            }
            
            if (!file_put_contents($filename, $temp)) {
                return false;
            }
            
            return $filename;
        } else {
            $opts = [
                "http" => [
                    "method" => "GET",
                    "header" => "",
                    "timeout" => $timeout,
                ],
            ];
            $context = stream_context_create($opts);
            if (@copy($url, $filename, $context)) {
                //$http_response_header
                return $filename;
            } else {
                return false;
            }
        }
    }
}

if (!function_exists('lake_encrypt_password')) {
    /**
     * 对用户的密码进行加密
     * @param $password
     * @param $encrypt //传入加密串，在修改密码时做认证
     * @return array/password
     */
    function lake_encrypt_password($password, $encrypt = '')
    {
        $pwd = PasswordFacade::setSalt(config("app.admin_salt"))->encrypt($password, $encrypt);
        return $pwd;
    }
}

if (!function_exists('lake_get_file_name')) {
    /**
     * 根据附件id获取文件名
     * @param string $id 附件id
     * @return string
     */
    function lake_get_file_name($id = '')
    {
        $name = (new AttachmentService())->getFileName($id);
        return $name ? $name : '没有找到文件';
    }
}

if (!function_exists('lake_get_file_path')) {
    /**
     * 获取附件路径
     * @param int $id 附件id
     * @return string
     */
    function lake_get_file_path($id)
    {
        $path = (new AttachmentService())->getFilePath($id);
        return ($path !== false) ? $path : "";
    }
}

if (!function_exists('lake_get_attachment_path')) {
    /**
     * 获取附件路径
     * @param int $id 附件id
     * @return string
     */
    function lake_get_attachment_path($id, $domain = false)
    {
        $path = (new AttachmentService())->getFilePath($id);
        return ($path !== false) ? 
            ($domain ? request()->domain() . $path : $path)
            : "";
    }
}

if (!function_exists('lake_get_attachment_list')) {
    /**
     * 获取多附件地址
     * @param string $ids 附件id列表
     * @return 返回附件列表
     */
    function lake_get_attachment_list($ids, $domain = false) {
        if ($ids == '') {
            return false;
        }
        
        $id_list = explode(',', $ids);
        foreach ($id_list as $id) {
            $list[] = lake_get_attachment_path($id, $domain);
        }
        return $list;
    }
}

if (!function_exists('lake_thumb')) {
    /**
     * 生成缩略图
     * @param type $img 图片地址
     * @param type $width 缩略图宽度
     * @param type $height 缩略图高度
     * @param type $thumbType 缩略图生成方式
     * @return type
     */
    function lake_thumb(
        $img, 
        $width = 100, 
        $height = 100, 
        $thumbType = 1
    ) {
        static $thumbCache = [];
        if (empty($img) || !file_exists($img)) {
            return false;
        }
        
        // 区分
        $key = md5($img . $width . $height . $thumbType);
        if (isset($thumbCache[$key])) {
            return $thumbCache[$key];
        }
        
        if (!$width) {
            return false;
        }
        
        $imgPath = dirname($img);
        $imgName = basename($img);

        $newImgName = 'thumb_' . $width . '_' . $height . '_' . $imgName;
        $newImgPath = $imgPath . '/' . $newImgName;
        // 检查生成的缩略图是否已经生成过
        if (is_file($newImgPath)) {
            return $newImgPath;
        }
        
        // 取得图片相关信息
        list($widthT, $heightT, $type, $attr) = getimagesize($img);
        // 如果高是0，自动计算高
        if ($height <= 0) {
            $height = round(($width / $widthT) * $heightT);
        }
        // 判断生成的缩略图大小是否正常
        if ($width >= $widthT || $height >= $heightT) {
            return $img;
        }
        
        (new AttachmentService())->createThumb($img, $newImgPath, $newImgName, "{$width},{$height}", $thumbType);
        $thumbCache[$key] = $newImgPath;
        return $thumbCache[$key];

    }
}

if (!function_exists('lake_check_auth')) {
    /**
     * 权限检测
     * @param string  $rule    检测的规则
     * @param string  $type    check类型
     * @param string  $mode    check模式
     * @return boolean
     *
     * @create 2019-7-2
     * @author deatil
     */
    function lake_check_auth($rule, $type = [1, 2], $mode = 'url')
    {
        if (env('admin_is_root')) {
            return true;
        }
        
        if (AdminAuthService::checkRule($rule, $type, $mode)) {
            return true;
        }
        
        return false;
    }
}

if (!function_exists('lake_runhook')) {
    /**
     * 行为
     * @param  string $tag    标签名称
     * @param  mixed  $params 传入参数
     * @param  bool   $once   只获取一个有效返回值
     * @return mixed
     */
    function lake_runhook($tag, $params = null, $once = false)
    {
        $event = Event::trigger($tag, $params, $once);
        if ($once) {
            return $event;
        } else {
            $html = join("", $event);;
            return $html;
        }
        
    }
}


if (!function_exists('lake_is_module_install')) {
    /**
     * 检查模块是否已经安装
     * @param type $moduleName 模块名称
     * @return boolean
     *
     * @create 2019-10-13
     * @author deatil
     */
    function lake_is_module_install($moduleName)
    {
        return (new ModuleService)->isInstall($moduleName);
    }
}

if (!function_exists('lake_get_module_config')) {
    /**
     * 获取模块的配置值
     * @param string $name 模块名
     * @return array
     *
     * @create 2019-10-13
     * @author deatil
     */
    function lake_get_module_config($name)
    {
        return (new ModuleService)->getConfig($name);
    }
}

if (!function_exists('lake_get_module_path')) {
    /**
     * 获取模块的路径
     * @param string $name 模块名
     * @return string
     *
     * @create 2020-2-28
     * @author deatil
     */
    function lake_get_module_path($name)
    {
        return (new ModuleService)->getPath($name);
    }
}

if (!function_exists('lake_config_update')) {
    /**
     * 更新配置
     *
     * @create 2019-10-17
     * @author deatil
     */
    function lake_config_update($name, $value)
    {
        return (new ConfigService)->updateValue($name, $value);
    }
}

if (!function_exists('lake_static')) {
    /**
     * 静态文件
     *
     * @create 2019-10-13
     * @author deatil
     */
    function lake_static($file, $domain = false)
    {
        $uri = '/static/';
        
        if ($domain) {
            $uri = request()->domain() . $uri;
        }
        
        return rtrim($uri, '/') . '/' . ltrim($file, '/') ;
    }
}

if (!function_exists('lake_module_static')) {
    /**
     * 模块静态文件
     *
     * @create 2019-10-13
     * @author deatil
     */
    function lake_module_static($file, $domain = false)
    {
        $file = '/modules/' . ltrim($file, '/');
        
        return lake_static($file, $domain);
    }
}



