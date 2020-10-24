<?php

namespace Lake\Admin\Controller;

use think\facade\Db;
use think\facade\Event;
use think\facade\Cache;
use think\captcha\Captcha;

use Lake\File;

use Lake\Admin\Model\AuthRule as AuthRuleModel;
use Lake\Admin\Model\Module as ModuleModel;
use Lake\Admin\Model\Attachment as AttachmentModel;
use Lake\Admin\Service\AuthRule as AuthRuleService;

/**
 * 后台首页
 *
 * @create 2019-7-7
 * @author deatil
 */
class Index extends Base
{

    /**
     * 后台首页
     *
     * @create 2019-7-7
     * @author deatil
     */
    public function index()
    {
        // 用户信息
        $this->assign('user_info', env('admin_info'));

        // 左侧菜单
        $menus = (new AuthRuleService)->getMenuList();
        $this->assign("menus", $menus);
        
        // 默认后台首页
        $defaultMainUrl = (string) url('index/main');
        
        // 兼容自定义后台首页
        $mainUrl = Event::trigger('LakeAdminMainUrl', $defaultMainUrl, true);
        if (empty($mainUrl)) {
            $mainUrl = $defaultMainUrl;
        }
        
        $this->assign("main_url", $mainUrl);
        
        return $this->fetch();
    }
    
    /**
     * 欢迎首页
     *
     * @create 2019-8-14
     * @author deatil
     */
    public function main()
    {
        $this->assign('user_info', env('admin_info'));
        
        // 模型数量
        $moduleCount = ModuleModel::count();
        $this->assign('module_count', $moduleCount);
        
        // 附件数量
        $attachmentCount = AttachmentModel::count();
        $this->assign('attachment_count', $attachmentCount);
        
        $this->assign('sys_info', $this->getSysInfo());
        
        return $this->fetch();
    }

    /**
     * phpinfo信息 按需显示在前台
     *
     * @create 2019-8-14
     * @author deatil
     */
    protected function getSysInfo()
    {
        //$sys_info['os'] = PHP_OS; //操作系统
        $sys_info['ip'] = GetHostByName($_SERVER['SERVER_NAME']); //服务器IP
        $sys_info['php_uname'] = php_uname();
        $sys_info['web_server'] = $_SERVER['SERVER_SOFTWARE']; //服务器环境
        $sys_info['phpv'] = phpversion(); //php版本
        $sys_info['fileupload'] = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknown'; //文件上传限制
        //$sys_info['memory_limit'] = ini_get('memory_limit'); //最大占用内存
        //$sys_info['set_time_limit'] = function_exists("set_time_limit") ? true : false; //最大执行时间
        //$sys_info['zlib'] = function_exists('gzclose') ? 'YES' : 'NO'; //Zlib支持
        //$sys_info['safe_mode'] = (boolean) ini_get('safe_mode') ? 'YES' : 'NO'; //安全模式
        //$sys_info['timezone'] = function_exists("date_default_timezone_get") ? date_default_timezone_get() : "no_timezone";
        $sys_info['curl'] = function_exists('curl_init') ? 'YES' : 'NO'; //Curl支持
        //$sys_info['max_ex_time'] = @ini_get("max_execution_time") . 's';
        $sys_info['domain'] = $_SERVER['HTTP_HOST']; //域名
        //$sys_info['remaining_space'] = round((disk_free_space(".") / (1024 * 1024)), 2) . 'M'; //剩余空间
        //$sys_info['user_ip'] = $_SERVER['REMOTE_ADDR']; //用户IP地址
        $sys_info['beijing_time'] = gmdate("Y年n月j日 H:i:s", time() + 8 * 3600); //北京时间
        $sys_info['time'] = date("Y年n月j日 H:i:s"); //服务器时间
        //$sys_info['web_directory'] = $_SERVER["DOCUMENT_ROOT"]; //网站目录
        $mysqlinfo = Db::query("SELECT VERSION() as version");
        $sys_info['mysql_version'] = $mysqlinfo[0]['version'];
        if (function_exists("gd_info")) {
            //GD库版本
            $gd = gd_info();
            $sys_info['gdinfo'] = $gd['GD Version'];
        } else {
            $sys_info['gdinfo'] = __("未知");
        }
        return $sys_info;
    }
    
    /**
     * 清空缓存
     *
     * @create 2019-7-7
     * @author deatil
     */
    public function clear()
    {
        $type = $this->request->request("type", 'all');
        switch ($type) {
            case 'data':
                File::delDir(root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'cache');
                Cache::clear();
                break;
                
            case 'template':
                File::delDir(root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'temp');
                break;
                
            case 'all':
                File::delDir(root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'cache');
                Cache::clear();
                File::delDir(root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'temp');
                break;
        }
        
        $this->success(__('清理缓存成功'));
    }

}
