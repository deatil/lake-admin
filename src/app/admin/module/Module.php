<?php

namespace app\admin\module;

use think\Db;
use think\Loader;
use think\Container;
use think\facade\Hook;
use think\facade\Cache;

use lake\File;
use lake\Sql;

use app\admin\model\Module as ModuleModel;

/**
 * 模块管理
 *
 * @create 2019-7-9
 * @author deatil
 */
class Module
{
    // 模块所处目录路径
    protected $appPath;
	
    // 模块模板安装路径
    protected $installdir;
    protected $uninstaldir;
	
    // 静态资源目录
    public $staticPath = null;
	
    // 模块列表
    protected $moduleList = [];
	
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
        $this->staticPath = env('root_path') . 'public' . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR;
    
		$this->appPath = config('module_path');
		$this->systemModuleList = config('system_module_list');
	}

    /**
     * 获取所有模块信息
	 *
	 * @create 2019-7-25
	 * @author deatil
     */
    public function getAll()
    {
		if (!empty($this->moduleList)) {
			return $this->moduleList;
		}
		
        $dirs = array_map('basename', glob($this->appPath . '*', GLOB_ONLYDIR));
        if ($dirs === false || !file_exists($this->appPath)) {
            $this->error = '模块目录不可读或者不存在';
            return false;
        }
		
        // 正常模块(包括已安装和未安装)
        $dirs_arr = array_diff($dirs, $this->systemModuleList);

        $list = [];
        foreach ($dirs_arr as $module) {
            $module_info = $this->getInfoFromLocalFile($module);
			if ($module_info !== false) {
				$list[$module] = $module_info;
			}
        }
		
		$hookModules = Hook::listen('lake_admin_modules_get_all_end');
		if (!empty($hookModules)) {
			foreach ($hookModules as $hookModule) {
				if (isset($hookModule['module']) && !empty($hookModule['module'])) {
					$list[$hookModule['module']] = $hookModule;
				}
			}
		}

        // 读取数据库已经安装模块表
		$moduleList = model('admin/Module')->getModuleList();
		
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
     * 获取经安装模块数量
	 *
	 * @create 2019-7-25
	 * @author deatil
     */
    public function getCount()
    {
        // 读取数据库已经安装模块表
        $moduleCount = ModuleModel::order('listorder asc')->count();
		
        return $moduleCount;
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
		
		$moduleFile = $this->appPath . $name;
		if (file_exists($moduleFile)) {
            $this->error = '该模块目录已经存在！';
            return false;
		}
		
        return true;
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
		
		$models = $this->getAll();
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
		
        $config = array(
            // 模块目录
            'module' => $name,
            // 模块名称
            'name' => $name,
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
            // 适配最低lake版本，
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
            // 缓存
            'cache' => [],
        );

        // 从配置文件获取
        if (!is_file($this->appPath . $name . DIRECTORY_SEPARATOR . 'info.php')) {
			return false;
		}
		
		$moduleConfig = include $this->appPath . $name . DIRECTORY_SEPARATOR . 'info.php';
		
		$config = array_merge($config, $moduleConfig);
		
		if (empty($config['path'])) {
			$config['path'] = $this->appPath . $name;
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
		
        // 检查模块是否已经安装
        if ($this->isInstall($name)) {
            $this->error = '模块已经安装，无法重复安装！';
            return false;
        }

        // 加载模块基本配置
        $config = $this->getInfoFromFile($name);
        if ($config === false) {
            $this->error = '模块配置错误，无法安装！';
            return false;
        }
		
		// 安装方法增加模块命名空间
		if (!empty($config['path'])) {
			$namespace_module_path = rtrim($config['path'], DIRECTORY_SEPARATOR);
		} else {
			$namespace_module_path = $this->appPath . trim($config['module'], DIRECTORY_SEPARATOR);
		}
		
		$app_namespace = app()->getNamespace();
		Loader::addNamespace([
			$app_namespace . '\\' . $name => $namespace_module_path . DIRECTORY_SEPARATOR,
		]);
		
        // 保存在安装表
        if (!$this->installModuleConfig($name, $config)) {
            $this->error = '安装失败！';
            return false;
        }
		
        // 执行安装脚本
        $this->runScript($name);
		
        // 执行菜单项安装
        if (isset($config['menus']) && !empty($config['menus'])) {
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
        $this->runScript($name, 'end');
		
        // 更新缓存
        cache('module', null);
        cache('hooks', null);
        cache('modules', null);
		
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
        $info = ModuleModel::where(array('module' => $name))->find();
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
        $this->runScript($name, 'run', 'uninstall');
		
        // 删除菜单项
		$this->uninstallMenu($name);
		
        // 去除对应行为
		Db::name('hook')->where([
			'module' => $name,
		])->delete();
		
        // 卸载结束，最后调用卸载脚本完成
        $this->runScript($name, 'end', 'uninstall');
		
        // 更新缓存
        cache('module', null);
        cache('hooks', null);
        cache('modules', null);
		
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

        // 加载模块基本配置
        $config = $this->getInfoFromFile($name);
        if ($config === false) {
            return false;
        }
		
        // 更新配置信息
        if (!$this->upgradeModuleConfig($name, $config)) {
            $this->error = '安装失败！';
            return false;
        }
		
        // 执行更新脚本
        $this->runScript($name, 'run', 'upgrade');
		
        // 执行菜单项安装
		$this->uninstallMenu($name);
        if (isset($config['menus']) && !empty($config['menus'])) {
			if ($this->installMenu($name, $config['menus']) === false) {
				return false;
			}
        }
		
        // 安装行为
		Db::name('hook')->where([
			'module' => $name,
		])->delete();
        if (isset($config['hooks']) && !empty($config['hooks'])) {
			$this->installModuleHooks($name, $config['hooks']);
        }
		
        // 更新结束，最后调用安装脚本完成
        $this->runScript($name, 'end', 'upgrade');
		
        // 更新缓存
        cache('module', null);
        cache('hooks', null);
        cache('modules', null);
		
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
		
		$data = [
			'module' => $name,
			'name' => $config['name'],
			'introduce' => isset($config['introduce']) ? $config['introduce'] : '',
			'author' => isset($config['author']) ? $config['author'] : '',
			'authorsite' => isset($config['authorsite']) ? $config['authorsite'] : '',
			'authoremail' => isset($config['authoremail']) ? $config['authoremail'] : '',
			'version' => $config['version'],
			'adaptation' => isset($config['adaptation']) ? $config['adaptation'] : '',
			'path' => (isset($config['path']) && !empty($config['path'])) 
				? $config['path'] 
				: $this->appPath . $name,
			'sign' => $config['sign'],
			
			'need_module' => (isset($config['need_module']) && !empty($config['need_module'])) ? json_encode($config['need_module']) : '',
			'setting' => (isset($config['setting']) && !empty($config['setting'])) ?  json_encode($config['setting']) : '',
			'setting_data' => (isset($config['setting_data']) && !empty($config['setting_data'])) ?  json_encode($config['setting_data']) : '',
			'listorder' => (isset($config['listorder']) && !empty($config['listorder'])) ? intval($config['listorder']) : 100,
		
			'installtime' => time(),
			'add_time' => time(),
			'add_ip' => request()->ip(1),
		];
		
        // 保存在安装表
        if (!ModuleModel::create($data, true)) {
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
		
		$data = [
			'name' => $config['name'],
			'introduce' => isset($config['introduce']) ? $config['introduce'] : '',
			'author' => isset($config['author']) ? $config['author'] : '',
			'authorsite' => isset($config['authorsite']) ? $config['authorsite'] : '',
			'authoremail' => isset($config['authoremail']) ? $config['authoremail'] : '',
			'version' => $config['version'],
			'adaptation' => isset($config['adaptation']) ? $config['adaptation'] : '',
			'path' => (isset($config['path']) && !empty($config['path'])) 
				? $config['path'] 
				: $this->appPath . $name,
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
			Db::name('hook')->insert([
				'id' => md5(time().to_guid_string(time()).mt_rand(0, 100000)),
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
		
        Db::name('hook')->where([
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
		
        $status = model('admin/AuthRule')->installModuleMenu($menu, $this->getInfoFromFile($name));
        if ($status === true) {
            return true;
        } else {
            $this->error = model('admin/AuthRule')->getError() ?: '安装菜单项出现错误！';
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
		
        model('admin/AuthRule')->delModuleMenu($name);
        
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
		
        // 静态资源文件
		$from_path = $this->appPath 
			. $name . DIRECTORY_SEPARATOR 
			. "install" . DIRECTORY_SEPARATOR 
			. "public" . DIRECTORY_SEPARATOR;
		$to_path = $this->staticPath 
			. strtolower($name) . DIRECTORY_SEPARATOR;
        
		if (file_exists($from_path)) {
            // 拷贝静态资源文件到前台静态资源目录
            File::copyDir($from_path, $to_path);
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
		
		// 移除静态资源
		$moduleStatic = $this->staticPath . strtolower($name) . DIRECTORY_SEPARATOR;
		if (is_dir($moduleStatic)) {
			File::delDir($moduleStatic);
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
		$dir = 'install'
	) {
        if (empty($name)) {
            return false;
        }
		
        // 检查是否有安装脚本
		$class = "\\app\\{$name}\\{$dir}\\{$dir}";
		if (!class_exists($class)) {
            return false;
		}
		
        $installObj = Container::get($class);
		
		if (!method_exists($installObj, $type)) {
            return true;
		}
		
        // 执行安装
        if (false === $installObj->$type()) {
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
		Db::name('hook')->where([
			'module' => $name,
		])->delete();
		
        // 更新缓存
        cache('module', null);
        cache('hooks', null);
        cache('modules', null);
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
		
        $moduleList = model('admin/Module')->getModuleList();
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
		
		$status = Db::name('module')->where([
			'module' => $name,
		])->update([
			'status' => 1,
		]);

        if ($status === false) {
            $this->error = '模块启用失败！';
            return false;
        }
		
		$status = Db::name('hook')->where([
			'module' => $name,
		])->update([
			'status' => 1,
		]);
		
		$status = Db::name('auth_rule')->where([
			'module' => $name,
		])->update([
			'status' => 1,
		]);
		
        //更新缓存
        cache('module', null);
        cache('hooks', null);
        cache('modules', null);
		
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
		
		$status = Db::name('module')->where([
			'module' => $name,
		])->update([
			'status' => 0,
		]);

        if ($status === false) {
            $this->error = '模块禁用失败！';
            return false;
        }
		
		$status = Db::name('hook')->where([
			'module' => $name,
		])->update([
			'status' => 0,
		]);
		
		$status = Db::name('auth_rule')->where([
			'module' => $name,
		])->update([
			'status' => 0,
		]);
		
        //更新缓存
        cache('module', null);
        cache('hooks', null);
        cache('modules', null);
		
		return true;
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
		
        $realFile = $this->appPath . ltrim($file, '/');
		
		return $realFile;
	}

    /**
     * 获取模块路径
	 *
	 * @create 2019-11-23
	 * @author deatil
     */	
	public function getModulePath($name = '')
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
			$dbPre = config('database.prefix');
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
		
        $module = $this->appPath . ltrim($name, '/');
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
            $this->error = '模块名称不能为空！';
            return false;
        }
		
		if (in_array($name, $this->systemModuleList)) {
			return true;
		}
		
		$module = Db::name('module')->where([
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

}
