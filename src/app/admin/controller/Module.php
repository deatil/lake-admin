<?php

namespace app\admin\controller;

use think\Db;
use think\Controller;

use lake\PclZip;

use app\admin\module\Module as ModuleModule;

/**
 * 模型管理
 *
 * @create 2019-7-9
 * @author deatil
 */
class Module extends Base
{
    private $ModuleModule = null;
    
    /**
     * 框架构造函数
     *
     * @create 2019-8-5
     * @author deatil
     */
    protected function initialize()
    {
        parent::initialize();
        
        $this->ModuleModule = new ModuleModule();
    }
    
    /**
     * 已安装
     *
     * @create 2019-9-20
     * @author deatil
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $limit = $this->request->param('limit/d', 10);
            $page = $this->request->param('page/d', 1);
            
            $list = Db::name('module')
                ->page($page, $limit)
                ->order('listorder asc')
                ->select();
            
            if (!empty($list)) {
                foreach ($list as $k => $v) {
                    $list[$k]['icon'] = $this->ModuleModule->getModuleIconData($v['module']);
                }
            }
            
            return json([
                "code" => 0, 
                "data" => $list,
            ]);
        } else {
            return $this->fetch();
        }
    }

    /**
     * 本地全部模块列表
     *
     * @create 2019-9-20
     * @author deatil
     */
    public function all()
    {
        if ($this->request->isAjax()) {
            $list = $this->ModuleModule->getAll();
            
            if (!empty($list)) {
                foreach ($list as $k => $v) {
                    $list[$k]['icon'] = $this->ModuleModule->getModuleIconData($v['module']);
                }
            }

            return json([
                "code" => 0, 
                "data" => $list,
            ]);
        } else {
            return $this->fetch();
        }
    }

    /**
     * 模块安装
     *
     * @create 2019-7-24
     * @author deatil
     */
    public function install()
    {
        if ($this->request->isPost()) {
            $module = $this->request->param('module');
            if (empty($module)) {
                $this->error('请选择需要安装的模块！');
            }
            if ($this->ModuleModule->install($module)) {
                $this->success('模块安装成功！一键清理缓存后生效！', url('Module/index'));
            } else {
                $error = $this->ModuleModule->getError();
                $this->error($error ? $error : '模块安装失败！');
            }
        } else {
            $module = $this->request->param('module', '');
            if (empty($module)) {
                $this->error('请选择需要安装的模块！');
            }
            
            $config = $this->ModuleModule->getInfoFromFile($module);
            
            // 版本检查
            $version_check = '';
            if ($config['adaptation']) {
                if (version_compare(config('lake.version'), $config['adaptation'], '>=') == false) {
                    $version_check = '<i class="iconfont icon-delete text-danger"></i>';
                } else {
                    $version_check = '<i class="iconfont icon-success text-success"></i>';
                }
            }
            
            $needModule = [];
            $tableCheck = [];
            
            // 检查模块依赖
            if (isset($config['need_module']) && !empty($config['need_module'])) {
                $needModule = $this->checkDependence($config['need_module']);
            }
            
            // 检查数据表
            if (isset($config['tables']) && !empty($config['tables'])) {
                foreach ($config['tables'] as $table) {
                    $table = config('database.prefix') . $table;
                    if (Db::query("SHOW TABLES LIKE '{$table}'")) {
                        $tableCheck[] = [
                            'table' => "{$table}",
                            'result' => '<span class="text-danger">存在同名</span>',
                        ];
                    } else {
                        $tableCheck[] = [
                            'table' => "{$table}",
                            'result' => '<i class="iconfont icon-success text-success"></i>',
                        ];
                    }
                }

            }
            
            $this->assign('need_module', $needModule);
            $this->assign('version_check', $version_check);
            $this->assign('table_check', $tableCheck);
            $this->assign('config', $config);
            
            return $this->fetch();
            
        }
    }

    /**
     * 模块卸载
     *
     * @create 2019-7-24
     * @author deatil
     */
    public function uninstall()
    {
        if ($this->request->isPost()) {
            $module = $this->request->param('module');
            if (empty($module)) {
                $this->error('请选择需要安装的模块！');
            }
            if ($this->ModuleModule->uninstall($module)) {
                $this->success("模块卸载成功！一键清理缓存后生效！", url("Module/index"));
            } else {
                $error = $this->ModuleModule->getError();
                $this->error($error ? $error : "模块卸载失败！", url("Module/index"));
            }
        } else {
            $module = $this->request->param('module', '');
            if (empty($module)) {
                $this->error('请选择需要安装的模块！');
            }
            $config = $this->ModuleModule->getInfoFromFile($module);
            $this->assign('config', $config);
            return $this->fetch();

        }
    }

    /**
     * 模块更新
     *
     * @create 2019-7-24
     * @author deatil
     */
    public function upgrade()
    {
        if ($this->request->isPost()) {
            $module = $this->request->param('module');
            if (empty($module)) {
                $this->error('请选择需要更新的模块！');
            }
            if ($this->ModuleModule->upgrade($module)) {
                $this->success('模块更新成功！一键清理缓存后生效！', url('Module/index'));
            } else {
                $error = $this->ModuleModule->getError();
                $this->error($error ? $error : '模块更新失败！');
            }
        } else {
            $module = $this->request->param('module', '');
            if (empty($module)) {
                $this->error('请选择需要安装的模块！');
            }
            
            $config = $this->ModuleModule->getInfoFromFile($module);
            
            // 版本检查
            if ($config['adaptation']) {
                if (version_compare(config('lake.version'), $config['adaptation'], '>=') == false) {
                    $version_check = '<i class="iconfont icon-delete text-danger"></i>';
                } else {
                    $version_check = '<i class="iconfont icon-success text-success"></i>';
                }
            }
            
            $needModule = [];
            $tableCheck = [];
            
            // 检查模块依赖
            if (isset($config['need_module']) && !empty($config['need_module'])) {
                $needModule = $this->checkDependence($config['need_module']);
            }
            
            // 检查数据表
            if (isset($config['tables']) && !empty($config['tables'])) {
                foreach ($config['tables'] as $table) {
                    $table = str_replace([
                        'pre__'
                    ], [
                        config('database.prefix')
                    ], $table);
                    if (Db::query("SHOW TABLES LIKE '{$table}'")) {
                        $tableCheck[] = [
                            'table' => "{$table}",
                            'result' => '<span class="text-danger">存在同名</span>',
                        ];
                    } else {
                        $tableCheck[] = [
                            'table' => "{$table}",
                            'result' => '<i class="iconfont icon-success text-success"></i>',
                        ];
                    }
                }

            }
            
            $this->assign('need_module', $needModule);
            $this->assign('version_check', $version_check);
            $this->assign('table_check', $tableCheck);
            $this->assign('config', $config);
            
            return $this->fetch();

        }
    }

    /**
     * 检查依赖
     * @param string $type 类型：module
     * @param array $data 检查数据
     * @return array
     */
    private function checkDependence($data = [])
    {
        $need = [];
        foreach ($data as $key => $value) {
            if (!isset($value[2])) {
                $value[2] = '=';
            }
            
            // 当前版本
            $curr_version = Db::name('Module')->where('module', $value[0])->value('version');

            $result = version_compare($curr_version, $value[1], $value[2]);
            $need[$key] = [
                'module' => $value[0],
                'version' => $curr_version ? $curr_version : '未安装',
                'version_need' => $value[2] . $value[1],
                'result' => $result ? '<i class="iconfont icon-success text-success"></i>' : '<i class="iconfont icon-delete text-danger"></i>',
            ];
        }

        return $need;
    }

    /**
     * 本地安装
     *
     * @create 2019-7-24
     * @author deatil
     */
    public function local()
    {
        if (!$this->request->isPost()) {
            $this->error('访问错误！');
        }
    
        $files = $this->request->file('file');
        if ($files == null) {
            $this->error("请选择上传文件！");
        }
        
        if (strtolower(substr($files->getInfo('name'), -3, 3)) != 'zip') {
            $this->error("上传的文件格式有误！");
        }
        
        // 插件名称
        $moduleName = pathinfo($files->getInfo('name'));
        $moduleName = $moduleName['filename'];
        // 检查插件目录是否存在
        if (!$this->ModuleModule->isLocal($moduleName)) {
            $this->error($this->ModuleModule->getError());
        }
        
        // 上传临时文件地址
        $filename = $files->getInfo('tmp_name');
        $zip = new PclZip($filename);
        $status = $zip->extract(PCLZIP_OPT_PATH, env('lake_module_path') . $moduleName);
        if (!$status) {
            $this->error('模块解压失败！');
        }
        
        $this->success('模块上传成功，可以进入模块管理进行安装！', url('all'));
    }
    
    /**
     * 设置插件页面
     *
     * @create 2019-7-24
     * @author deatil
     */
    public function config()
    {
        if (!$this->request->isGet()) {
            $this->error("请求错误！");
        }
        
        $moduleId = $this->request->param('module/s');
        if (empty($moduleId)) {
            $this->error('请选择需要操作的模块！');
        }
        
        // 获取插件信息
        $module = Db::name('module')->where([
            'module' => $moduleId,
            'status' => 1,
        ])->find();
        if (empty($module)) {
            $this->error('该模块没有安装或者被禁用！');
        }
        
        $module['setting'] = json_decode($module['setting'], true);
        $settingData = $module['setting_data'];
        
        // 载入插件配置数组
        if (!empty($settingData)) {
            $settingData = json_decode($settingData, true);
            if (!empty($settingData)) {
                foreach ($module['setting'] as $key => $value) {
                    if ($value['type'] != 'group') {
                        $module['setting'][$key]['value'] = isset($settingData[$key]) ? $settingData[$key] : '';
                    } else {
                        foreach ($value['options'] as $gourp => $options) {
                            foreach ($options['options'] as $gkey => $value) {
                                $module['setting'][$key]['options'][$gourp]['options'][$gkey]['value'] = $settingData[$gkey];
                            }
                        }
                    }
                }
            }
        }
        
        $this->assign('data', $module);
        
        return $this->fetch();
    }

    /**
     * 保存模块设置
     *
     * @create 2019-7-24
     * @author deatil
     */
    public function saveConfig()
    {
        if (!$this->request->isPost()) {
            $this->error('访问错误！');
        }
        
        $moduleId = $this->request->param('module/s');
        if (empty($moduleId)) {
            $this->error('请选择需要操作的模块！');
        }
        
        //获取插件信息
        $module = Db::name('module')->where([
            'module' => $moduleId,
            'status' => 1,
        ])->find();
        if (empty($module)) {
            $this->error('该模块没有安装或者被禁用！');
        }
        
        $config = $this->request->param('config/a');
        $flag = Db::name('module')->where([
            'module' => $moduleId,
        ])->setField('setting_data', json_encode($config));
        
        if ($flag === false) {
            $this->error('保存失败');
        }
        
        $this->success('保存成功');
    }

    /**
     * 模块详情
     *
     * @create 2019-7-30
     * @author deatil
     */
    public function view()
    {
        if (!$this->request->isGet()) {
            $this->error("请求错误！");
        }
        
        $module = $this->request->param('module/s');
        $data = Db::name('module')->where([
            "module" => $module,
        ])->find();
        if (empty($data)) {
            $this->error('信息不存在！');
        }
        
        $this->assign("data", $data);
        return $this->fetch();
    }
    
    /**
     * 启用
     *
     * @create 2019-7-14
     * @author deatil
     */
    public function enable()
    {
        if (!$this->request->isPost()) {
            $this->error('访问错误！');
        }
        
        $module = $this->request->param('module/s');
        if (empty($module)) {
            $this->error('模块ID错误！');
        }
        
        $status = $this->ModuleModule->enable($module);

        if ($status === false) {
            $error = $this->ModuleModule->getError();
            $this->error($error);
        }
        
        $this->success('启用成功！');
    }

    /**
     * 禁用
     *
     * @create 2019-7-14
     * @author deatil
     */
    public function disable()
    {
        if (!$this->request->isPost()) {
            $this->error('访问错误！');
        }
        
        $module = $this->request->param('module/s');
        if (empty($module)) {
            $this->error('模块ID错误！');
        }
        
        $status = $this->ModuleModule->disable($module);

        if ($status === false) {
            $error = $this->ModuleModule->getError();
            $this->error($error);
        }
        
        $this->success('禁用成功！');
    }

}
