<?php

use think\Db;
use think\facade\Hook;

use app\admin\model\Attachment;
use app\admin\service\Auth;

if (!function_exists('p')) {
    /**
     * 打印输出数据到文件
     * @param mixed $data 输出的数据
     * @param boolean $force 强制替换
     * @param string|null $file 文件名称
     */
    function p($data, $force = false, $file = null)
    {
        if (is_null($file)) {
			$file = env('runtime_path') . date('Ymd') . '.txt';
		}
        $str = (is_string($data) ? $data : (is_array($data) || is_object($data)) ? print_r($data, true) : var_export($data, true)) . PHP_EOL;
        $force ? file_put_contents($file, $str) : file_put_contents($file, $str, FILE_APPEND);
    }
}

if (!function_exists('var_exports')) {
    /**
     * 返回数组
     * @param array $arr 输出的数据
     * @param string $blankspace 空格
     * @return string
     */
    function var_exports($arr = [], $blankspace = '')
    {
		$blank = '    ';
		$ret = "[\n";
		if (!empty($arr)) {
			foreach ($arr as $k => $v) {
				$ret .= $blankspace . $blank;
				$ret .= (is_numeric($k) ? '' : "'".$k."' => ");
				$_type = strtolower(gettype($v));
				switch($_type){
					case 'integer':
						$ret .= $v.",";
						break;
					case 'array':
						$ret .= var_exports($v, $blankspace . $blank).",";
						break;
					case 'null':
						$ret .= "NULL,";
						break;
					default:
						$ret  .= "'".$v."',";
						break;
				}
				$ret .= "\n";
			}
		}
		
		$ret .= $blankspace . "]";
		return $ret;
	}
}

if (!function_exists('runhook')) {
    /**
     * 行为
     * @param  string $tag    标签名称
     * @param  mixed  $params 传入参数
     * @param  bool   $once   只获取一个有效返回值
     * @return mixed
     */
    function runhook($tag, $params = null, $once = false)
    {
		$hooks = Hook::listen($tag, $params, $once);
		if ($once) {
			return $hooks;
		} else {
			$html = '';
			if (!empty($hooks)) {
				foreach ($hooks as $hook) {
					$html .= $hook;
				}
			}

			return $html;
		}
		
    }
}

if (!function_exists('is_module_install')) {
	/**
	 * 检查模块是否已经安装
	 * @param type $moduleName 模块名称
	 * @return boolean
	 *
	 * @create 2019-10-13
	 * @author deatil
	 */
	function is_module_install($moduleName)
	{
		$appCache = model('admin/Module')->getModuleList();
		if (isset($appCache[$moduleName])) {
			return true;
		}
		return false;
	}
}

if (!function_exists('get_module_config')) {
	/**
	 * 获取模块的配置值
	 * @param string $name 模块名
	 * @return array
	 *
	 * @create 2019-10-13
	 * @author deatil
	 */
	function get_module_config($name)
	{
		static $_config = [];
		
		if (empty($name)) {
			return [];
		}
		if (isset($_config[$name])) {
			return $_config[$name];
		}	

		$setting = Db::name('module')
			->where([
				'module' => $name,
				'status' => 1,
			])
			->field('setting, setting_data')
			->find();
			
		$config = [];
		if (!empty($setting['setting_data'])) {
			$config = json_decode($setting['setting_data'], true);
		} elseif (!empty($setting['setting'])) {
			$temp_arr = json_decode($setting['setting'], true);
			foreach ($temp_arr as $key => $value) {
				if ($value['type'] == 'group') {
					foreach ($value['options'] as $gkey => $gvalue) {
						foreach ($gvalue['options'] as $ikey => $ivalue) {
							$config[$ikey] = $ivalue['value'];
						}
					}
				} else {
					$config[$key] = $temp_arr[$key]['value'];
				}
			}
		}
		
		$_config[$name] = $config;
		
		return $config;
	}
}

if (!function_exists('get_module_path')) {
	/**
	 * 获取模块的路径
	 * @param string $name 模块名
	 * @return string
	 *
	 * @create 2020-2-28
	 * @author deatil
	 */
	function get_module_path($name)
	{
		static $modules = [];
		
		if (empty($name)) {
			return '';
		}
		if (isset($modules[$name])) {
			return $modules[$name];
		}
		
		$module = Db::name('module')->where([
			'module' => $name,
			'status' => 1,
		])
		->field('path')
		->find();
		if (empty($module)) {
			return '';
		}
		
		$modules[$name] = $module;
		return $module;
	}
}

if (!function_exists('lake_admin_config_update')) {
	/**
	 * 更新配置
	 *
	 * @create 2019-10-17
	 * @author deatil
	 */
	function lake_admin_config_update($name, $value)
	{
		if (empty($name)) {
			return false;
		}
		
		return Db::name('config')->where([
			'name' => $name,
		])->setField('value', $value);
	}
}

if (!function_exists('lake_admin_static')) {
	/**
	 * 静态文件
	 *
	 * @create 2019-10-13
	 * @author deatil
	 */
	function lake_admin_static($file, $domain = false)
	{
		$uri = '/static/';
		
		if ($domain) {
			$uri = request()->domain() . $uri;
		}
		
		return rtrim($uri, '/') . '/' . ltrim($file, '/') ;
	}
}

if (!function_exists('lake_admin_module_static')) {
	/**
	 * 模块静态文件
	 *
	 * @create 2019-10-13
	 * @author deatil
	 */
	function lake_admin_module_static($file, $domain = false)
	{
		$file = '/modules/' . ltrim($file, '/');
		
		return lake_admin_static($file, $domain);
	}
}

if (!function_exists('data_auth_sign')) {
	/**
	 * 数据签名认证
	 * @param  array  $data 被认证的数据
	 * @return string       签名
	 */
	function data_auth_sign($data)
	{
		//数据类型检测
		if (!is_array($data)) {
			$data = (array) $data;
		}
		ksort($data); //排序
		$code = http_build_query($data); //url编码并生成query字符串
		$sign = sha1($code); //生成签名
		return $sign;
	}
}

if (!function_exists('int_to_string')) {
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
	function int_to_string(&$data, $map = array('status' => [1 => '正常', -1 => '删除', 0 => '禁用', 2 => '未审核', 3 => '草稿']))
	{
		if ($data === false || $data === null) {
			return $data;
		}
		$data = (array) $data;
		foreach ($data as $key => $row) {
			foreach ($map as $col => $pair) {
				if (isset($row[$col]) && isset($pair[$row[$col]])) {
					$data[$key][$col . '_text'] = $pair[$row[$col]];
				}
			}
		}
		return $data;
	}
}

if (!function_exists('str2arr')) {
	/**
	 * 字符串转换为数组，主要用于把分隔符调整到第二个参数
	 * @param  string $str  要分割的字符串
	 * @param  string $glue 分割符
	 * @return array
	 */
	function str2arr($str, $glue = ',')
	{
		return explode($glue, $str);
	}
}

if (!function_exists('arr2str')) {
	/**
	 * 数组转换为字符串，主要用于把分隔符调整到第二个参数
	 * @param  array  $arr  要连接的数组
	 * @param  string $glue 分割符
	 * @return string
	 */
	function arr2str($arr, $glue = ',')
	{
		if (is_string($arr)) {
			return $arr;
		}

		return implode($glue, $arr);
	}
}

if (!function_exists('str_cut')) {
	/**
	 * 字符截取
	 * @param $string 需要截取的字符串
	 * @param $length 长度
	 * @param $dot
	 */
	function str_cut($sourcestr, $length, $dot = '...')
	{
		$returnstr = '';
		$i = 0;
		$n = 0;
		$str_length = strlen($sourcestr); //字符串的字节数
		while (($n < $length) && ($i <= $str_length)) {
			$temp_str = substr($sourcestr, $i, 1);
			$ascnum = Ord($temp_str); //得到字符串中第$i位字符的ascii码
			if ($ascnum >= 224) { //如果ASCII位高与224，
				$returnstr = $returnstr . substr($sourcestr, $i, 3); //根据UTF-8编码规范，将3个连续的字符计为单个字符
				$i = $i + 3; //实际Byte计为3
				$n++; //字串长度计1
			} elseif ($ascnum >= 192) { //如果ASCII位高与192，
				$returnstr = $returnstr . substr($sourcestr, $i, 2); //根据UTF-8编码规范，将2个连续的字符计为单个字符
				$i = $i + 2; //实际Byte计为2
				$n++; //字串长度计1
			} elseif ($ascnum >= 65 && $ascnum <= 90) {
				//如果是大写字母，
				$returnstr = $returnstr . substr($sourcestr, $i, 1);
				$i = $i + 1; //实际的Byte数仍计1个
				$n++; //但考虑整体美观，大写字母计成一个高位字符
			} else {
				//其他情况下，包括小写字母和半角标点符号，
				$returnstr = $returnstr . substr($sourcestr, $i, 1);
				$i = $i + 1; //实际的Byte数计1个
				$n = $n + 0.5; //小写字母和半角标点等与半个高位字符宽...
			}
		}
		if ($str_length > strlen($returnstr)) {
			$returnstr = $returnstr . $dot; //超过长度时在尾处加上省略号
		}
		return $returnstr;
	}
}

if (!function_exists('to_time')) {
	/**
	 * 时间转换
	 * @param array $arr        传入数组
	 * @param string $field     字段名
	 * @param string $format    格式
	 * @return mixed
	 */
	function to_time(&$arr, $field = 'time', $format = 'Y-m-d H:i:s')
	{
		if (isset($arr[$field])) {
			$arr[$field] = date($format, $arr[$field]);
		}
		return $arr;
	}
}

if (!function_exists('to_ip')) {
	/**
	 * ip转换
	 * @param array $arr        传入数组
	 * @param string $field     字段名
	 * @return mixed
	 */
	function to_ip(&$arr, $field = 'ip')
	{
		if (isset($arr[$field])) {
			$arr[$field] = long2ip($arr[$field]);
		}
		return $arr;
	}
}

if (!function_exists('list_sort_by')) {
	/**
	 * 对查询结果集进行排序
	 * @access public
	 * @param array $list 查询结果
	 * @param string $field 排序的字段名
	 * @param array $sortby 排序类型
	 * asc正向排序 desc逆向排序 nat自然排序
	 * @return array
	 */
	function list_sort_by($list, $field, $sortby = 'asc')
	{
		if (is_array($list)) {
			$refer = $resultSet = array();
			foreach ($list as $i => $data) {
				$refer[$i] = &$data[$field];
			}

			switch ($sortby) {
				case 'asc': // 正向排序
					asort($refer);
					break;
				case 'desc': // 逆向排序
					arsort($refer);
					break;
				case 'nat': // 自然排序
					natcasesort($refer);
					break;
			}
			foreach ($refer as $key => $val) {
				$resultSet[] = &$list[$key];
			}

			return $resultSet;
		}
		return false;
	}
}

if (!function_exists('list_to_tree')) {
	/**
	 * 把返回的数据集转换成Tree
	 * @param array $list 要转换的数据集
	 * @param string $pid parent标记字段
	 * @param string $level level标记字段
	 * @return array
	 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
	 */
	function list_to_tree(
		$list, 
		$pk = 'id', 
		$pid = 'parentid', 
		$child = '_child', 
		$root = 0
	) {
		// 创建Tree
		$tree = [];
		if (is_array($list)) {
			// 创建基于主键的数组引用
			$refer = [];
			foreach ($list as $key => $data) {
				$refer[$data[$pk]] = &$list[$key];
			}
			foreach ($list as $key => $data) {
				// 判断是否存在parent
				$parentId = $data[$pid];
				if ($root == $parentId) {
					$tree[] = &$list[$key];
				} else {
					if (isset($refer[$parentId])) {
						$parent = &$refer[$parentId];
						$parent[$child][] = &$list[$key];
					}
				}
			}
		}
		return $tree;
	}
}

if (!function_exists('parse_attr')) {
	/**
	 * 解析配置
	 * @param string $value 配置值
	 * @return array|string
	 */
	function parse_attr($value = '')
	{
		$array = preg_split('/[,;\r\n ]+/', trim($value, ",;\r\n"));
		if (strpos($value, ':')) {
			$value = [];
			foreach ($array as $val) {
				list($k, $v) = explode(':', $val);
				$value[$k] = $v;
			}
		} else {
			$value = $array;
		}
		return $value;
	}
}
	
if (!function_exists('parse_fieldlist')) {
	/**
	 * 解析配置信息
	 *
	 * @create 2019-11-16
	 * @author deatil
	 */
	function parse_fieldlist($data = '')
	{
		if (empty($data)) {
			return [];
		}
		
		$json = json_decode($data, true);
		if (empty($json)) {
			return [];
		}
		
		$res = [];
		foreach ($json as $v) {
			$res[$v['key']] = $v['value'];
		}
		
		return $res;
	}
}

if (!function_exists('time_format')) {
	/**
	 * 时间戳格式化
	 * @param int $time
	 * @return string 完整的时间显示
	 */
	function time_format($time = null, $type = 0)
	{
		$types = ['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d'];
		$time = $time === null ? $_SERVER['REQUEST_TIME'] : intval($time);
		return date($types[$type], $time);
	}
}

if (!function_exists('format_bytes')) {
	/**
	 * 格式化字节大小
	 * @param  number $size      字节数
	 * @param  string $delimiter 数字和单位分隔符
	 * @return string            格式化后的带单位的大小
	 */
	function format_bytes($size, $delimiter = '')
	{
		$units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
		for ($i = 0; $size >= 1024 && $i < 5; $i++) {
			$size /= 1024;
		}

		return round($size, 2) . $delimiter . $units[$i];
	}
}

if (!function_exists('to_guid_string')) {
	/**
	 * 根据PHP各种类型变量生成唯一标识号
	 * @param mixed $mix 变量
	 * @return string
	 */
	function to_guid_string($mix)
	{
		if (is_object($mix)) {
			return spl_object_hash($mix);
		} elseif (is_resource($mix)) {
			$mix = get_resource_type($mix) . strval($mix);
		} else {
			$mix = serialize($mix);
		}
		return md5($mix);
	}
}

if (!function_exists('get_random_string')) {
	/**
	 * 产生一个指定长度的随机字符串,并返回给用户
	 * @param type $len 产生字符串的长度
	 * @return string 随机字符串
	 */
	function get_random_string($len = 6)
	{
		$chars = [
			"a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
			"l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
			"w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
			"H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
			"S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
			"3", "4", "5", "6", "7", "8", "9",
		];
		$charsLen = count($chars) - 1;
		// 将数组打乱
		shuffle($chars);
		$output = "";
		for ($i = 0; $i < $len; $i++) {
			$output .= $chars[mt_rand(0, $charsLen)];
		}
		return $output;
	}
}

if (!function_exists('http_down')) {
	/**
	 * 下载远程文件，默认保存在temp下
	 * @param  string  $url     网址
	 * @param  string  $filename    保存文件名
	 * @param  integer $timeout 过期时间
	 * @param  bool $repalce 是否覆盖已存在文件
	 * @return string 本地文件名
	 */
	function http_down($url, $filename = "", $timeout = 60)
	{
		if (empty($filename)) {
			$filename = env('root_path') . 'public' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . pathinfo($url, PATHINFO_BASENAME);
		}
		$path = dirname($filename);
		if (!is_dir($path) && !mkdir($path, 0755, true)) {
			return false;
		}
		$url = str_replace(" ", "%20", $url);
		if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			// curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
			// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			if ('https' == substr($url, 0, 5)) {
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			}
			$temp = curl_exec($ch);
			if (file_put_contents($filename, $temp) && !curl_error($ch)) {
				return $filename;
			} else {
				return false;
			}
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

if (!function_exists('safe_replace')) {
	/**
	 * 安全过滤函数
	 * @param $string
	 * @return string
	 */
	function safe_replace($string)
	{
		$string = str_replace('%20', '', $string);
		$string = str_replace('%27', '', $string);
		$string = str_replace('%2527', '', $string);
		$string = str_replace('*', '', $string);
		$string = str_replace('"', '&quot;', $string);
		$string = str_replace("'", '', $string);
		$string = str_replace('"', '', $string);
		$string = str_replace(';', '', $string);
		$string = str_replace('<', '&lt;', $string);
		$string = str_replace('>', '&gt;', $string);
		$string = str_replace("{", '', $string);
		$string = str_replace('}', '', $string);
		$string = str_replace('\\', '', $string);
		return $string;
	}
}


if (!function_exists('is_serialized')) {
	/**
	 * 判断是否为序列化
	 *
	 * @create 2019-7-2
	 * @author deatil
	 */
	function is_serialized($data) 
	{
		$data = trim( $data );
		if ('N;' == $data) {
			return true;
		}
		if (!preg_match( '/^([adObis]):/', $data, $badions )) {
			return false;
		}
		
		switch ($badions[1]) {
			case 'a':
			case 'O':
			case 's':
				if (preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data)) {
					return true;
				}
				break;
			case 'b':
			case 'i':
			case 'd':
				if (preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data )) {
					return true;
				}
			break;
		}
		return false;
	}
}

if (!function_exists('get_file_name')) {
	/**
	 * 根据附件id获取文件名
	 * @param string $id 附件id
	 * @return string
	 */
	function get_file_name($id = '')
	{
		$name = (new Attachment())->getFileName($id);
		return $name ? $name : '没有找到文件';
	}
}

if (!function_exists('get_file_path')) {
	/**
	 * 获取附件路径
	 * @param int $id 附件id
	 * @return string
	 */
	function get_file_path($id)
	{
		$path = (new Attachment())->getFilePath($id);
		return ($path !== false) ? $path : "";
	}
}

if (!function_exists('get_attachment_path')) {
	/**
	 * 获取附件路径
	 * @param int $id 附件id
	 * @return string
	 */
	function get_attachment_path($id, $domain = false)
	{
		$path = (new Attachment())->getFilePath($id);
		return ($path !== false) ? 
			($domain ? request()->domain() . $path : $path)
			: "";
	}
}

if (!function_exists('get_attachment_list')) {
	/**
	 * 获取多附件地址
	 * @param string $ids 附件id列表
	 * @return 返回附件列表
	 */
	function get_attachment_list($ids, $domain = false) {
		if ($ids == '') {
			return false;
		}
		
		$id_list = explode(',', $ids);
		foreach ($id_list as $id) {
			$list[] = get_attachment_path($id, $domain);
		}
		return $list;
	}
}

if (!function_exists('encrypt_password')) {
	/**
	 * 对用户的密码进行加密
	 * @param $password
	 * @param $encrypt //传入加密串，在修改密码时做认证
	 * @return array/password
	 */
	function encrypt_password($password, $encrypt = '')
	{
		$pwd = [];
		$pwd['encrypt'] = $encrypt ? $encrypt : get_random_string();
		$pwd['password'] = md5(trim($password) . $pwd['encrypt']);
		return $encrypt ? $pwd['password'] : $pwd;
	}
}

if (!function_exists('check_auth')) {
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
	function check_auth($rule, $type = [1, 2], $mode = 'url')
	{
		if (env('is_root')) {
			return true;
		}
		
		static $Auth = null;
		if (!$Auth) {
			$Auth = new Auth();
		}
		if (!$Auth->check($rule, env('admin_id'), $type, $mode)) {
			return false;
		}
		return true;
	}
}

if (!function_exists('thumb')) {
	/**
	 * 生成缩略图
	 * @param type $imgurl 图片地址
	 * @param type $width 缩略图宽度
	 * @param type $height 缩略图高度
	 * @param type $thumbType 缩略图生成方式
	 * @param type $smallpic 图片不存在时显示默认图片
	 * @return type
	 */
	function thumb($imgurl, $width = 100, $height = 100, $thumbType = 1, $smallpic = 'none.png')
	{
		static $_thumb_cache = [];
		$smallpic = config('public_url') . 'static/admin/img/' . $smallpic;
		if (empty($imgurl)) {
			return $smallpic;
		}
		//区分
		$key = md5($imgurl . $width . $height . $thumbType . $smallpic);
		if (isset($_thumb_cache[$key])) {
			return $_thumb_cache[$key];
		}
		if (!$width) {
			return $smallpic;
		}

		$uploadUrl = config('public_url') . 'uploads/';
		$uploadPath = config('upload_path');
		$imgurl_replace = str_replace($uploadUrl, '', $imgurl);

		$newimgname = 'thumb_' . $width . '_' . $height . '_' . basename($imgurl_replace);
		$newimgurl = dirname($imgurl_replace) . '/' . $newimgname;
		//检查生成的缩略图是否已经生成过
		if (is_file($uploadPath . DIRECTORY_SEPARATOR . $newimgurl)) {
			return $uploadUrl . $newimgurl;
		}
		//检查文件是否存在，如果是开启远程附件的，估计就通过不了，以后在考虑完善！
		if (!is_file($uploadPath . DIRECTORY_SEPARATOR . $imgurl_replace)) {
			return $imgurl;
		}
		//取得图片相关信息
		list($width_t, $height_t, $type, $attr) = getimagesize($uploadPath . DIRECTORY_SEPARATOR . $imgurl_replace);
		//如果高是0，自动计算高
		if ($height <= 0) {
			$height = round(($width / $width_t) * $height_t);
		}
		//判断生成的缩略图大小是否正常
		if ($width >= $width_t || $height >= $height_t) {
			return $imgurl;
		}
		(new Attachment())->createThumb($uploadPath . DIRECTORY_SEPARATOR . $imgurl_replace, $uploadPath . DIRECTORY_SEPARATOR . dirname($imgurl_replace) . '/', $newimgname, "{$width},{$height}", $thumbType);
		$_thumb_cache[$key] = $uploadUrl . $newimgurl;
		return $_thumb_cache[$key];

	}
}
