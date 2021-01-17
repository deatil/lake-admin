<?php

namespace Lake\Admin\Module;

use Composer\Autoload\ClassLoader;

use think\facade\Event;
use think\facade\Cache;
use think\facade\Config;

use Lake\Admin\Model\Module as ModuleModel;
use Lake\Admin\Model\AuthRule as AuthRuleModel;
use Lake\Admin\Service\Module as ModuleService;
use Lake\Admin\Module\Contracts\Module as ModuleContract;
use Lake\Admin\Module\Menu as ModuleMenu;
use Lake\Admin\Module\Event as ModuleEvent;
use Lake\Admin\Module\Data\Module as ModuleData;

/**
 * 模块管理
 *
 * @create 2019-7-9
 * @author deatil
 */
class Module implements ModuleContract
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
        $moduleList = (new ModuleService)->getList();
        
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
                    $moduleInfo = Tool::parseModuleConfig($moduleInfo);
                    $list[$module] = $moduleInfo;
                }
            }
        }
        
        // 自定义包插件
        $composerModules = Event::trigger('lake_admin_module');
        if (!empty($composerModules)) {
            foreach ($composerModules as $composerModule) {
                if (isset($composerModule['module']) && !empty($composerModule['module'])) {
                    $composerModule = Tool::parseModuleConfig($composerModule);
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
        
        $config = Tool::parseModuleConfig($moduleConfig);
        
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
        
        if (in_array($name, $this->systemModuleList)) {
            $this->error = '该模块名称不能不能被安装！';
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
        
        // 保存到安装表
        if (!$this->installModuleConfig($name, $config)) {
            $this->error = '安装失败！';
            return false;
        }
        
        // 执行安装脚本
        try {
            $installScript = Tool::runScript($name);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
        if ($installScript === false) {
            return false;
        }
        
        // 执行菜单项安装
        if (isset($config['menus']) 
            && !empty($config['menus'])
            && is_array($config['menus'])
        ) {
            if ($this->installMenu($name, $config['menus']) !== true) {
                $this->error = '菜单安装失败！';
                return false;
            }
        }
        
        // 安装事件
        if (isset($config['event']) && !empty($config['event'])) {
            ModuleEvent::install($name, $config['event']);
        }
        
        // 安装结束，最后调用安装脚本完成
        try {
            $installEnd = Tool::runScript($name, 'end');
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
        if ($installEnd === false) {
            $this->error = '脚本安装失败！';
            return false;
        }
        
        // 更新缓存
        $this->clearCache();
        
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
        
        // 取得该模块数据库中记录的安装信息
        $info = ModuleModel::where([
            'module' => $name,
        ])->find();
        if (empty($info)) {
            $this->error = '该模块未安装，无需卸载！';
            return false;
        }
        
        // 执行卸载脚本
        try {
            $uninstallRun = Tool::runScript($name, 'run', 'Uninstall');
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
        
        if ($uninstallRun === false) {
            $this->error = '脚本卸载失败！';
            return false;
        }
        
        // 删除
        $deleteStatus = ModuleData::delete($name);
        if ($deleteStatus === false) {
            $this->error = '卸载失败！';
            return false;
        }
        
        // 删除菜单项
        ModuleMenu::uninstall($name);
        
        // 去除对应行为
        ModuleEvent::uninstall($name);
        
        // 卸载结束，最后调用卸载脚本完成
        try {
            $uninstallEnd = Tool::runScript($name, 'end', 'Uninstall');
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
        if ($uninstallEnd === false) {
            $this->error = '脚本卸载后失败！';
            return false;
        }
        
        // 更新缓存
        $this->clearCache();
        
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
        
        // 执行更新脚本
        try {
            $upgradeRun = Tool::runScript($name, 'run', 'Upgrade');
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
        if ($upgradeRun === false) {
            $this->error = '脚本更新失败！';
            return false;
        }
        
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
        
        // 执行菜单项安装
        ModuleMenu::uninstall($name);
        if (isset($config['menus'])) {
            if ($this->installMenu($name, $config['menus']) === false) {
                return false;
            }
        }
        
        // 安装行为
        ModuleEvent::uninstall($name);
        if (isset($config['event']) && !empty($config['event'])) {
            ModuleEvent::install($name, $config['event']);
        }
        
        // 更新结束，最后调用安装脚本完成
        try {
            $upgradeEnd = Tool::runScript($name, 'end', 'Upgrade');
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
        if ($upgradeEnd === false) {
            $this->error = '脚本更新后失败！';
            return false;
        }
        
        // 更新缓存
        $this->clearCache();
        
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
            
            'need_module' => (isset($config['need_module']) && !empty($config['need_module'])) ? json_encode($config['need_module']) : '',
            'setting' => (isset($config['setting']) && !empty($config['setting'])) ?  json_encode($config['setting']) : '',
            'setting_data' => (isset($config['setting_data']) && !empty($config['setting_data'])) ?  json_encode($config['setting_data']) : '',
            'listorder' => (isset($config['listorder']) && !empty($config['listorder'])) ? intval($config['listorder']) : 100,
        
            'installtime' => time(),
            'status' => 1,
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
            return false;
        }
        
        if (empty($menu)) {
            return false;
        }
        
        $status = ModuleMenu::install($menu, $this->getInfoFromFile($name));
        if ($status === true) {
            return true;
        } else {
            $this->error = '安装菜单项出现错误！';
            return false;
        }
    }
    
    /**
     * 添加静态文件
     * @param string $name 模块名称
     * @param string $fromPath [选填]模块静态文件夹
     * @return boolean
     *
     * @create 2019-8-12
     * @author deatil
     */
    public function installStatic($name = '', $fromPath = '')
    {
        return (new Statics)
            ->withModulePath($this->modulePath)
            ->withStaticPath($this->staticPath)
            ->install($name, $fromPath);
    }

    /**
     * 删除静态文件
     *
     * @create 2019-8-12
     * @author deatil
     */    
    public function uninstallStatic($name = '')
    {
        return (new Statics)
            ->withStaticPath($this->staticPath)
            ->uninstall($name);
    }

    /**
     * 安装回滚
     * @param type $name 模块名(目录名)
     *
     * @create 2019-8-5
     * @author deatil
     */
    public function installRollback($name = '')
    {
        if (empty($name)) {
            return false;
        }
        
        // 删除安装状态
        ModuleData::delete($name);
        
        // 删除菜单项
        ModuleMenu::uninstall($name);
        
        // 去除对应行为
        ModuleEvent::uninstall($name);
        
        // 清除缓存
        $this->clearCache();
    }

    /**
     * 更新回滚
     * @param type $name 模块名(目录名)
     *
     * @create 2020-8-15
     * @author deatil
     */
    public function upgradeRollback($name = '')
    {
        if (empty($name)) {
            return false;
        }
        
        ModuleData::disable($name);
        
        ModuleEvent::disable($name);
        
        ModuleMenu::disable($name);
        
        // 清除缓存
        $this->clearCache();
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
            return false;
        }
        
        return (new ModuleService)->isInstall($name);
    }
    
    /**
     * 启用
     *
     * @create 2019-7-14
     * @author deatil
     */
    public function enable($name = '')
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        if (!$this->isInstall($name)) {
            $this->error = '模块没有安装，无法启用！';
            return false;
        }
        
        $status = ModuleData::enable($name);
        if ($status === false) {
            $this->error = '模块启用失败！';
            return false;
        }
        
        ModuleEvent::enable($name);
        
        ModuleMenu::enable($name);
        
        // 更新缓存
        $this->clearCache();
        
        return true;
    }
    
    /**
     * 禁用
     *
     * @create 2019-7-14
     * @author deatil
     */
    public function disable($name = '')
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        if (!$this->isInstall($name)) {
            $this->error = '模块没有安装，无法禁用！';
            return false;
        }
        
        $status = ModuleData::disable($name);
        if ($status === false) {
            $this->error = '模块禁用失败！';
            return false;
        }
        
        ModuleEvent::disable($name);
        
        ModuleMenu::disable($name);
        
        // 更新缓存
        $this->clearCache();
        
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
        return Tool::checkDependence($data);
    }
    
    /**
     * 执行数据库脚本
     * @param string $sqlFile 数据库脚本文件
     * @param string $dbPre 数据库前缀
     * @return boolean
     */
    public function runSQL($sqlFile = '', $dbPre = '')
    {
        return Tool::runSQL($sqlFile, $dbPre);
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
        
        $data = file_get_contents($icon);
        $base64Data = base64_encode($data);
        
        $iconData = "data:image/png;base64,{$base64Data}";
        
        return $iconData;
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
        $installModules = Config::get('app.install_modules', []);
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
    private function clearCache()
    {
        // 清空缓存
        cache('lake_admin_module_list', null);
        cache('lake_admin_events', null);
        cache('lake_admin_modules', null);
    }
    
}
