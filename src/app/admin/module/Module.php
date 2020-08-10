<?php

namespace app\admin\module;

use Composer\Autoload\ClassLoader;

use think\Container;
use think\facade\Db;
use think\facade\Event;
use think\facade\Cache;
use think\facade\Config;

use lake\File;
use lake\Sql;
use lake\Symlink;

use app\admin\model\Hook as HookModel;
use app\admin\model\Module as ModuleModel;
use app\admin\model\AuthRule as AuthRuleModel;
use app\admin\service\Module as ModuleService;

/**
 * 模块管理
 *
 * @create 2019-7-9
 * @author deatil
 */
class Module
{
    // 根目录
    protected $rootPath;
    
    // 模块所处目录路径
    protected $modulePath;
    
    // 模块模板安装路径
    protected $installdir;
    protected $uninstaldir;
    
    // 模块配置
    protected $options = [
        'root_path' => '',
        'module_path' => '',
        'system_module_list' => '',
        'module_static_path' => '',
    ];
    
    // 静态资源目录
    protected $staticPath = null;
    
    // 模块列表
    protected $moduleList = [];
    
    // 模块本地列表
    protected $moduleLocalList = [];
    
    // 系统模块，隐藏
    protected $systemModuleList = [];
    
    // 安装模块失败内容
    protected $error = '安装模块失败';    

    /**
     * @param 单例
     * @return static
     *
     * @create 2019-8-5
     * @author deatil
     */
    public static function instance($options = [])
    {
        static $instance = null;
        
        if (is_null($instance)) {
            $instance = new static($options);
        }
        
        return $instance;
    }
    
    /**
     * 构造方法
     *
     * @create 2019-8-5
     * @author deatil
     */
    public function __construct($options = [])
    {
        $this->options = array_merge($this->options, [
            'root_path' => config('app.root_path'),
            'module_path' => config('app.module_path'),
            'system_module_list' => config('app.system_module_list'),
            'module_static_path' => config('app.module_static_path'),
        ]);
        
        if (!empty($options) && is_array($options)) {
            $this->options = array_merge($this->options, $options);
        }
        
        $this->rootPath = $this->options['root_path'];
        $this->modulePath = $this->options['module_path'];
        $this->systemModuleList = $this->options['system_module_list'];
        $this->staticPath = realpath($this->options['module_static_path']);
    }
    
    /**
     * 所有模块
     *
     * @create 2019-7-25
     * @author deatil
     */
    public function getAll()
    {
        if (!empty($this->moduleList)) {
            return $this->moduleList;
        }
        
        $list = $this->getLocalList();
        
        // 读取数据库已经安装模块表
        $moduleList = (new ModuleService())->getList();
        
        if (!empty($list)) {
            foreach ($list as $name => $config) {
                // 检查是否安装，如果安装了，加载模块安装后的相关配置信息
                if (isset($moduleList[$name])) {
                    $list[$name] = array_merge($config, $moduleList[$name]);
                    
                    if (version_compare($config['version'], $moduleList[$name]['version'], '>') !== false) {
                        $list[$name]['upgrade'] = [
                            'old' => $moduleList[$name]['version'],
                            'new' => $config['version'],
                        ];
                    }
                }
                
                $list[$name]['icon'] = rtrim($config['path'], '/') . '/icon.png';
            }
        }
        
        $this->moduleList = $list;
        
        return $list;
    }
    
    /**
     * 所有本地模块
     *
     * @create 2020-7-23
     * @author deatil
     */
    public function getLocalList()
    {
        if (!empty($this->moduleLocalList)) {
            return $this->moduleLocalList;
        }

        $dirs = array_map('basename', glob($this->modulePath . '*', GLOB_ONLYDIR));
        if ($dirs === false || !file_exists($this->modulePath)) {
            $this->error = '模块目录不可读或者不存在';
            return false;
        }
        
        // 本地模块
        $dirsArr = array_diff($dirs, $this->systemModuleList);
        
        $list = [];
        if (!empty($dirsArr)) {
            foreach ($dirsArr as $module) {
                $moduleInfo = $this->getInfoFromLocalFile($module);
                if ($moduleInfo !== false) {
                    $moduleInfo = $this->parseModuleConfig($moduleInfo);
                    $list[$module] = $moduleInfo;
                }
            }
        }
        
        // 自定义包插件
        $composerModules = Event::trigger('lake_admin_module');
        if (!empty($composerModules)) {
            foreach ($composerModules as $composerModule) {
                if (isset($composerModule['module']) && !empty($composerModule['module'])) {
                    $composerModule = $this->parseModuleConfig($composerModule);
                    $list[$composerModule['module']] = $composerModule;
                }
            }
        }
        
        $this->moduleLocalList = $list;
        
        return $list;
    }
    
    /**
     * 本地模块数量
     *
     * @create 2020-7-23
     * @author deatil
     */
    public function getLocalCount()
    {
        $list = $this->getLocalList();
        
        return count($list);
    }

    /**
     * 判断模块是否本地存在
     *
     * @create 2019-11-22
     * @author deatil
     */
    public function isLocal($name = '')
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        $moduleFile = $this->modulePath . $name;
        if (file_exists($moduleFile)) {
            $this->error = '该模块目录已经存在！';
            return false;
        }
        
        return true;
    }

    /**
     * 获取模块信息，包括数据库合并信息
     * @param string $name 模块名称
     * @return array|mixed
     *
     * @create 2020-7-23
     * @author deatil
     */
    public function getInfo($name = '')
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        $models = $this->getAll();
        if (!isset($models[$name])) {
            $this->error = '模块信息不能存在！';
            return false;
        }
        
        return $models[$name];
    }

    /**
     * 获取模块信息
     * @param string $name 模块名称
     * @return array|mixed
     *
     * @create 2019-10-27
     * @author deatil
     */
    public function getInfoFromFile($name = '')
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        $models = $this->getLocalList();
        if (!isset($models[$name])) {
            $this->error = '模块信息不能存在！';
            return false;
        }
        
        return $models[$name];
    }

    /**
     * 从文件获取本地模块信息
     * @param string $name 模块名称
     * @return array|mixed
     *
     * @create 2019-8-5
     * @author deatil
     */
    public function getInfoFromLocalFile($name = '')
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        // 从配置文件获取
        if (!is_file($this->modulePath . $name . DIRECTORY_SEPARATOR . 'info.php')) {
            return false;
        }
        
        $moduleConfig = include $this->modulePath . $name . DIRECTORY_SEPARATOR . 'info.php';
        
        $config = $this->parseModuleConfig($moduleConfig);
        
        if (empty($config['path'])) {
            $config['path'] = $this->modulePath . $name;
        }
        
        return $config;
    }

    /**
     * 从安装处获取模块信息
     * @param string $name 模块名称
     * @return array|mixed
     *
     * @create 2020-4-10
     * @author deatil
     */
    public function getInfoFromInstall($name = '')
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        $info = ModuleModel::where([
            'module' => $name,
        ])->find();
        if (empty($info)) {
            $this->error = '该模块未安装！';
            return false;
        }
        
        if (!empty($info['path'])) {
            $info = rtrim($this->getModuleRealPath($info['path']), '/') . '/info.php';
        } else {
            $info = $this->modulePath . $name . '/info.php';
        }
        
        if (!file_exists($info)) {
            $this->error = '模块信息文件不存在！';
            return false;
        }
        
        $config = include $info;
        
        if (!is_array($config)) {
            $this->error = '模块信息错误！';
            return false;
        }
        
        return $config;
    }

    /**
     * 解析格式化模块配置为正确配置
     * @param type $name 模块名(目录名)
     * @return array
     *
     * @create 2020-7-25
     * @author deatil
     */
    public function parseModuleConfig($moduleConfig = [])
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
            // 签名
            'sign' => '',
            // 依赖模块
            'need_module' => [],
            // 设置
            'setting' => [],
            // 嵌入点
            'hooks' => [],
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
     * 执行模块安装
     * @param type $name 模块名(目录名)
     * @return boolean
     *
     * @create 2019-8-5
     * @author deatil
     */
    public function install($name = '')
    {
        defined('INSTALL') or define("INSTALL", true);
        
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        // 检查模块是否已经安装
        if ($this->isInstall($name)) {
            $this->error = '模块已经安装，无法重复安装！';
            return false;
        }
        
        // 设置脚本最大执行时间
        @set_time_limit(0);
        
        // 加载模块基本配置
        $config = $this->getInfoFromFile($name);
        if ($config === false) {
            $this->error = '模块配置错误，无法安装！';
            return false;
        }
        
        // 安装方法增加模块命名空间
        if (!empty($config['path'])) {
            $namespaceModulePath = rtrim($this->getModuleRealPath($config['path']), DIRECTORY_SEPARATOR);
        } else {
            $namespaceModulePath = $this->modulePath . trim($config['module'], DIRECTORY_SEPARATOR);
        }
        
        $appNamespace = config('app.module_namespace');
        
        $loader = new ClassLoader();
        $loader->addPsr4($appNamespace . '\\' . $name . '\\', $namespaceModulePath . DIRECTORY_SEPARATOR);
        $loader->register();
        
        // 保存在安装表
        if (!$this->installModuleConfig($name, $config)) {
            $this->error = '安装失败！';
            return false;
        }
        
        // 执行安装脚本
        $installScript = $this->runScript($name);
        if ($installScript === false) {
            return false;
        }
        
        // 执行菜单项安装
        if (isset($config['menus']) 
            && !empty($config['menus'])
        ) {
            if ($this->installMenu($name, $config['menus']) !== true) {
                $this->installRollback($name);
                return false;
            }
        }
        
        // 安装行为
        if (isset($config['hooks']) && !empty($config['hooks'])) {
            $this->installModuleHooks($name, $config['hooks']);
        }
        
        // 安装结束，最后调用安装脚本完成
        $installScript = $this->runScript($name, 'end');
        if ($installScript === false) {
            return false;
        }
        
        // 更新缓存
        $this->clearModuleCache();
        
        return true;
    }

    /**
     * 模块卸载
     * @param type $name 模块名(目录名)
     * @return boolean
     *
     * @create 2019-8-5
     * @author deatil
     */
    public function uninstall($name = '')
    {
        defined('UNINSTALL') or define("UNINSTALL", true);
        
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        // 设置脚本最大执行时间
        @set_time_limit(0);

        // 加载模块基本配置
        $config = $this->getInfoFromFile($name);
        if ($config === false) {
            return false;
        }
        
        // 取得该模块数据库中记录的安装信息
        $info = ModuleModel::where([
            'module' => $name,
        ])->find();
        if (empty($info)) {
            $this->error = '该模块未安装，无需卸载！';
            return false;
        }
        
        // 删除
        if (ModuleModel::where([
            'module' => $name,
        ])->delete() === false) {
            $this->error = '卸载失败！';
            return false;
        }
        
        // 执行卸载脚本
        $installScript = $this->runScript($name, 'run', 'Uninstall');
        if ($installScript === false) {
            return false;
        }
        
        // 删除菜单项
        $this->uninstallMenu($name);
        
        // 去除对应行为
        HookModel::where([
            'module' => $name,
        ])->delete();
        
        // 卸载结束，最后调用卸载脚本完成
        $installScript = $this->runScript($name, 'end', 'Uninstall');
        if ($installScript === false) {
            return false;
        }
        
        // 更新缓存
        $this->clearModuleCache();
        
        return true;
    }

    /**
     * 执行模块更新
     * @param type $name 模块名(目录名)
     * @return boolean
     *
     * @create 2019-8-5
     * @author deatil
     */
    public function upgrade($name = '')
    {
        defined('UPGRADE') or define("UPGRADE", true);
        
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        // 检查模块是否已经安装
        if (!$this->isInstall($name)) {
            $this->error = '模块没有安装，不需要更新！';
            return false;
        }
        
        // 设置脚本最大执行时间
        @set_time_limit(0);
        
        // 加载模块基本配置
        $config = $this->getInfoFromInstall($name);
        if ($config === false) {
            return false;
        }
        
        // 更新配置信息
        if (!$this->upgradeModuleConfig($name, $config)) {
            $this->error = '安装失败！';
            return false;
        }
        
        // 执行更新脚本
        $installScript = $this->runScript($name, 'run', 'Upgrade');
        if ($installScript === false) {
            return false;
        }
        
        // 执行菜单项安装
        $this->uninstallMenu($name);
        if (isset($config['menus']) && !empty($config['menus'])) {
            if ($this->installMenu($name, $config['menus']) === false) {
                return false;
            }
        }
        
        // 安装行为
        HookModel::where([
            'module' => $name,
        ])->delete();
        if (isset($config['hooks']) && !empty($config['hooks'])) {
            $this->installModuleHooks($name, $config['hooks']);
        }
        
        // 更新结束，最后调用安装脚本完成
        $installScript = $this->runScript($name, 'end', 'Upgrade');
        if ($installScript === false) {
            return false;
        }
        
        // 更新缓存
        $this->clearModuleCache();
        
        return true;
    }

    /**
     * 安装模块数据
     * @param type $name 模块名称
     * @param type $config 模块信息
     * @return boolean
     *
     * @create 2019-8-5
     * @author deatil
     */
    private function installModuleConfig($name = '', $config = [])
    {
        if (empty($config['name']) 
            || empty($config['version']) 
            || empty($config['sign'])
        ) {
            $this->error = '模块信息错误！';
            return false;
        }
        
        $modulePath = (isset($config['path']) && !empty($config['path'])) 
            ? $config['path'] 
            : $this->modulePath . $name;
        $modulePath = $this->getModulePath($modulePath);
        
        $data = [
            'module' => $name,
            'name' => $config['name'],
            'introduce' => isset($config['introduce']) ? $config['introduce'] : '',
            'author' => isset($config['author']) ? $config['author'] : '',
            'authorsite' => isset($config['authorsite']) ? $config['authorsite'] : '',
            'authoremail' => isset($config['authoremail']) ? $config['authoremail'] : '',
            'version' => $config['version'],
            'adaptation' => isset($config['adaptation']) ? $config['adaptation'] : '',
            'path' => $modulePath,
            'sign' => $config['sign'],
            
            'need_module' => (isset($config['need_module']) && !empty($config['need_module'])) ? json_encode($config['need_module']) : '',
            'setting' => (isset($config['setting']) && !empty($config['setting'])) ?  json_encode($config['setting']) : '',
            'setting_data' => (isset($config['setting_data']) && !empty($config['setting_data'])) ?  json_encode($config['setting_data']) : '',
            'listorder' => (isset($config['listorder']) && !empty($config['listorder'])) ? intval($config['listorder']) : 100,
        
            'installtime' => time(),
            'status' => 1,
            'add_time' => time(),
            'add_ip' => request()->ip(1),
        ];
        
        // 保存在安装表
        if (!ModuleModel::create($data, [], true)) {
            return false;
        }

        return true;
    }

    /**
     * 更新模块数据
     * @param type $name 模块名称
     * @param type $config 模块信息
     * @return boolean
     *
     * @create 2019-8-5
     * @author deatil
     */
    private function upgradeModuleConfig($name = '', $config = [])
    {
        if (empty($config['name']) 
            || empty($config['version']) 
            || empty($config['sign'])
        ) {
            $this->error = '模块信息错误！';
            return false;
        }
        
        $modulePath = (isset($config['path']) && !empty($config['path'])) 
            ? $config['path'] 
            : $this->modulePath . $name;
        $modulePath = $this->getModulePath($modulePath);
        
        $data = [
            'name' => $config['name'],
            'introduce' => isset($config['introduce']) ? $config['introduce'] : '',
            'author' => isset($config['author']) ? $config['author'] : '',
            'authorsite' => isset($config['authorsite']) ? $config['authorsite'] : '',
            'authoremail' => isset($config['authoremail']) ? $config['authoremail'] : '',
            'version' => $config['version'],
            'adaptation' => isset($config['adaptation']) ? $config['adaptation'] : '',
            'path' => $modulePath,
            'sign' => $config['sign'],
            
            'need_module' => isset($config['need_module']) ? json_encode($config['need_module']) : '',
            'setting' => isset($config['setting']) ?  json_encode($config['setting']) : '',
            'need_module' => isset($config['need_module']) ? json_encode($config['need_module']) : '',
        
            'update_time' => time(),
            'update_ip' => request()->ip(1),
        ];
        
        $status = ModuleModel::where([
            'module' => $name,
        ])->data($data)->update();
        if ($status === false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 安装模块嵌入点
     * @param type $name 模块名称
     * @param type $hooks 嵌入点信息
     * @return boolean
     *
     * @create 2019-8-5
     * @author deatil
     */
    private function installModuleHooks($name = '', $hooks = [])
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        if (empty($hooks)) {
            $this->error = '嵌入点信息不能为空！';
            return false;
        }
        
        foreach ($hooks as $hook) {
            HookModel::insert([
                'id' => md5(time().lake_to_guid_string(time()).mt_rand(0, 100000)),
                'module' => $name,
                'name' => $hook['name'],
                'class' => $hook['class'],
                'description' => $hook['description'],
                'listorder' => isset($hook['listorder']) ? $hook['listorder'] : 100,
                'status' => (isset($hook['status']) && $hook['status'] == 1) ? 1 : 0,
                'add_time' => time(),
                'add_ip' => request()->ip(1),
            ]);
        }
        
    }
    
    /**
     * 卸载菜单项项
     * @param type $name
     * @return boolean
     *
     * @create 2019-8-5
     * @author deatil
     */
    private function uninstallModuleHooks($name = '')
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        HookModel::where([
            'module' => $name,
        ])->delete();
        
        return true;
    }
    
    /**
     * 安装菜单项
     * @param type $name 模块名称
     * @param type $file 文件
     * @return boolean
     *
     * @create 2019-8-5
     * @author deatil
     */
    private function installMenu($name = '', $menu = [])
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        if (empty($menu)) {
            return false;
        }
        
        $AuthRuleModel = new AuthRuleModel;
        $status = $AuthRuleModel->installModuleMenu($menu, $this->getInfoFromFile($name));
        if ($status === true) {
            return true;
        } else {
            $this->error = $AuthRuleModel->getError() ?: '安装菜单项出现错误！';
            return false;
        }
    }
    
    /**
     * 卸载菜单项项
     * @param type $name
     * @return boolean
     *
     * @create 2019-8-5
     * @author deatil
     */
    private function uninstallMenu($name = '')
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        (new AuthRuleModel)->delModuleMenu($name);
        
        return true;
    }
    
    /**
     * 添加静态文件
     *
     * @create 2019-8-12
     * @author deatil
     */
    public function installStatic($name = '')
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        $name = strtolower($name);
        
        // 静态资源文件软链接
        $fromPath = $this->modulePath 
            . $name . DIRECTORY_SEPARATOR 
            . "static" . DIRECTORY_SEPARATOR;
        $toPath = $this->staticPath . DIRECTORY_SEPARATOR 
            . $name . DIRECTORY_SEPARATOR;
        
        // 创建静态资源文件软链接
        $status = Symlink::make($fromPath, $toPath);
        if ($status === false) {
            $this->error = '创建模块静态资源软链接失败！';
            return false;
        }
        
        return true;
    }

    /**
     * 删除静态文件
     *
     * @create 2019-8-12
     * @author deatil
     */    
    public function uninstallStatic($name = '')
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        // 移除静态资源软链接
        $moduleStatic = $this->staticPath . DIRECTORY_SEPARATOR
            . strtolower($name) . DIRECTORY_SEPARATOR;
        if (file_exists($moduleStatic)) {
            Symlink::remove($moduleStatic);
        }
        
        return true;
    }

    /**
     * 执行安装脚本
     * @param type $name 模块名(目录名)
     * @return boolean
     *
     * @create 2019-8-5
     * @author deatil
     */
    private function runScript(
        $name = '', 
        $type = 'run', 
        $dir = 'Install'
    ) {
        if (empty($name)) {
            $this->error = '模块名不嫩为空';
            return false;
        }
        
        // 检查是否有安装脚本
        $class = "\\app\\{$name}\\{$dir}";
        if (!class_exists($class)) {
            return true;
        }
        
        $installObj = Container::getInstance()->make($class);
        
        if (!method_exists($installObj, $type)) {
            $this->error = '安装脚本错误';
            return true;
        }
        
        // 执行安装
        if (false === Container::getInstance()->invoke([$installObj, $type], [])) {
            $this->error = '安装模块失败';
            return false;
        }
        
        return true;
    }

    /**
     * 安装回滚
     * @param type $name 模块名(目录名)
     *
     * @create 2019-8-5
     * @author deatil
     */
    private function installRollback($name = '')
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        // 删除安装状态
        ModuleModel::where([
            'module' => $name,
        ])->delete();
        
        // 删除菜单项
        $this->uninstallMenu($name);
        
        // 去除对应行为
        HookModel::where([
            'module' => $name,
        ])->delete();
        
        // 更新缓存
        $this->clearModuleCache();
    }

    /**
     * 是否已经安装
     * @param type $name 模块名(目录名)
     * @return boolean
     *
     * @create 2019-8-5
     * @author deatil
     */
    public function isInstall($name = '')
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        $moduleList = (new ModuleService())->getList();
        return (isset($moduleList[$name]) && $moduleList[$name]) ? true : false;
    }
    
    /**
     * 启用
     *
     * @create 2019-7-14
     * @author deatil
     */
    public function enable($name)
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        if (!$this->isInstall($name)) {
            $this->error = '模块没有安装，无法启用！';
            return false;
        }
        
        $status = ModuleModel::where([
            'module' => $name,
        ])->update([
            'status' => 1,
        ]);
        
        if ($status === false) {
            $this->error = '模块启用失败！';
            return false;
        }
        
        $status = HookModel::where([
            'module' => $name,
        ])->update([
            'status' => 1,
        ]);
        
        $status = AuthRuleModel::where([
            'module' => $name,
        ])->update([
            'status' => 1,
        ]);
        
        // 更新缓存
        $this->clearModuleCache();
        
        return true;
    }
    
    /**
     * 禁用
     *
     * @create 2019-7-14
     * @author deatil
     */
    public function disable($name)
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        if (!$this->isInstall($name)) {
            $this->error = '模块没有安装，无法禁用！';
            return false;
        }
        
        $status = ModuleModel::where([
            'module' => $name,
        ])->update([
            'status' => 0,
        ]);
        
        if ($status === false) {
            $this->error = '模块禁用失败！';
            return false;
        }
        
        $status = HookModel::where([
            'module' => $name,
        ])->update([
            'status' => 0,
        ]);
        
        $status = AuthRuleModel::where([
            'module' => $name,
        ])->update([
            'status' => 0,
        ]);
        
        // 更新缓存
        $this->clearModuleCache();
        
        return true;
    }
    
    /**
     * 检查依赖
     * @param string $type 类型：module
     * @param array $data 检查数据
     * @return array
     */
    public function checkDependence($data = [])
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
     * 获取模块内文件
     * @param string $file 模块内文件
     * @return string
     *
     * @create 2019-8-5
     * @author deatil
     */    
    public function getModuleFile($file = '')
    {
        if (empty($file)) {
            return false;
        }
        
        $realFile = $this->modulePath . ltrim($file, '/');
        
        return $realFile;
    }
    
    /**
     * 获取模块路径信息
     *
     * @create 2019-11-23
     * @author deatil
     */    
    public function getModulePathInfo($name = '')
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        $models = $this->getAll();
        if (!isset($models[$name])) {
            $this->error = '模块信息不存在！';
            return false;
        }
        
        if (!isset($models[$name]['path'])) {
            $this->error = '模块路径不存在！';
            return false;
        }
        
        return $models[$name]['path'];
    }
    
    /**
     * 获取模块相对路径，去掉绝对路径信息
     *
     * @create 2020-7-23
     * @author deatil
     */    
    public function getModulePath($moduleRealPath = '')
    {
        if (empty($moduleRealPath)) {
            return '';
        }
        
        $moduleRealPath = str_replace([
            DIRECTORY_SEPARATOR,
            '\\',
        ], '/', $moduleRealPath);
        $this->rootPath = str_replace([
            DIRECTORY_SEPARATOR,
            '\\',
        ], '/', $this->rootPath);
        
        $modulePath = substr($moduleRealPath, strlen($this->rootPath));
        return $modulePath;
    }
    
    /**
     * 获取模块真实路径，添加绝对路径信息
     *
     * @create 2020-7-23
     * @author deatil
     */    
    public function getModuleRealPath($modulePath = '')
    {
        if (empty($modulePath)) {
            return '';
        }
        
        $modulePath = str_replace('/', DIRECTORY_SEPARATOR, $modulePath);
        $this->rootPath = str_replace('/', DIRECTORY_SEPARATOR, $this->rootPath);
        
        if (file_exists($modulePath)) {
            return $modulePath;
        }
        
        $moduleRealPath = $this->rootPath . ltrim($modulePath, DIRECTORY_SEPARATOR);
        return $moduleRealPath;
    }
    
    /**
     * 获取模块标识
     *
     * @create 2019-11-23
     * @author deatil
     */    
    public function getModuleIcon($name = '')
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        $models = $this->getAll();
        if (!isset($models[$name])) {
            $this->error = '模块信息不存在！';
            return false;
        }
        
        if (!isset($models[$name]['icon'])) {
            $this->error = '模块标识不存在！';
            return false;
        }
        
        return $models[$name]['icon'];
    }
    
    /**
     * 获取模块标识数据
     *
     * @create 2019-11-23
     * @author deatil
     */    
    public function getModuleIconData($name = '')
    {
        $icon = $this->getModuleIcon($name);
        if (!$icon) {
            return false;
        }
        
        if (!file_exists($icon)) {
            $icon = __DIR__ . '/icon/lake.png';
        }
        
        ob_start();
        $data = file_get_contents($icon);
        ob_end_clean();
        $base64Data = base64_encode($data);
        
        $iconData = "data:image/png;base64,{$base64Data}";
        
        return $iconData;
    }
    
    /**
     * 执行数据库脚本
     * @param string $sqlFile 数据库脚本文件
     * @param string $dbPre 数据库前缀
     * @return boolean
     *
     * @create 2019-8-5
     * @author deatil
     */
    public function runSQL($sqlFile = '', $dbPre = '')
    {
        if (empty($sqlFile)) {
            $this->error = 'SQL文件不能为空';
            return false;
        }
        
        if (!file_exists($sqlFile)) {
            $this->error = 'SQL文件不存在';
            return false;
        }
        
        $sqlStatement = Sql::getSqlFromFile($sqlFile);
        if (empty($sqlStatement)) {
            $this->error = 'SQL文件内容为空';
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
                $this->error = '导入SQL失败';
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 删除模块文件，只支持模块文件夹内删除
     *
     * @create 2019-11-23
     * @author deatil
     */    
    public function removeModule($name = '')
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空';
            return false;
        }
        
        $module = $this->modulePath . ltrim($name, '/');
        if (!file_exists($module)) {
            $this->error = '模块不存在';
            return false;
        }
        
        $delStatus = File::delDir($module);
        if (!$delStatus) {
            $this->error = '删除失败';
            return false;
        }
        
        return true;
    }
    
    /**
     * 检测安装模块
     *
     * @create 2019-7-14
     * @author deatil
     */
    public function checkModule($name)
    {
        if (empty($name)) {
            $this->error = '应用模块不存在！';
            return false;
        }
        
        if (in_array($name, $this->systemModuleList)) {
            return true;
        }
        
        // 设置自定义的安装模块
        $installModules = Config::get('install_modules');
        if (in_array($name, $installModules)) {
            return true;
        }
        
        $module = ModuleModel::where([
            'module' => $name,
        ])->find();
        if (empty($module)) {
            $this->error = '应用模块不存在！';
            return false;
        }
        
        if ($module['status'] != 1) {
            $this->error = '应用模块已禁用！';
            return false;
        }
        
        return true;
    }
    
    /**
     * 判断是否为系统模块
     *
     * @create 2019-7-14
     * @author deatil
     */
    public function checkSystemModule($name)
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        if (!in_array($name, $this->systemModuleList)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 获取错误信息
     * @return string
     *
     * @create 2019-8-5
     * @author deatil
     */
    public function getError()
    {
        return $this->error;
    }
    
    /**
     * 清空缓存
     *
     * @create 2020-3-30
     * @author deatil
     */
    private function clearModuleCache()
    {
        // 清空缓存
        cache('lake_admin_module_list', null);
        cache('lake_admin_hooks', null);
        cache('lake_admin_modules', null);
    }
    
}
