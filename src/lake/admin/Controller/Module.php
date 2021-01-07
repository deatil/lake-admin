<?php

namespace Lake\Admin\Controller;

use think\facade\Db;

use Lake\PclZip;

use Lake\Admin\Facade\Module as ModuleFacade;
use Lake\Admin\Model\Module as ModuleModel;

/**
 * 模型管理
 *
 * @create 2019-7-9
 * @author deatil
 */
class Module extends Base
{
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
            
            $list = ModuleModel::page($page, $limit)
                ->order('listorder ASC, module ASC')
                ->paginate([
                    'list_rows' => $limit,
                    'page' => $page
                ])
                ->toArray();
            
            if (!empty($list['data'])) {
                foreach ($list['data'] as $k => $v) {
                    $list['data'][$k]['icon'] = ModuleFacade::getModuleIconData($v['module']);
                }
            }
            
            return $this->json([
                "code" => 0, 
                'msg' => __('获取成功！'),
                'data' => $list['data'],
                'count' => $list['total'],
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
            $list = ModuleFacade::getAll();
            if ($list === false) {
                return $this->json([
                    "code" => 1, 
                    "msg" => ModuleFacade::getError(),
                ]);
            }
            
            if (!empty($list)) {
                foreach ($list as $k => $v) {
                    $list[$k]['icon'] = ModuleFacade::getModuleIconData($v['module']);
                }
            }

            return $this->json([
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
                $this->error(__('请选择需要安装的模块！'));
            }
            
            $status = ModuleFacade::install($module);
            if ($status === false) {
                // 安装错误回滚
                ModuleFacade::installRollback($module);
                
                $error = ModuleFacade::getError();
                $this->error($error ? $error : __('模块安装失败！'));
            }
            
            $this->success(__('模块安装成功！'), url('Module/index'));
        } else {
            $module = $this->request->param('module', '');
            if (empty($module)) {
                $this->error(__('请选择需要安装的模块！'));
            }
            
            $config = ModuleFacade::getInfoFromFile($module);
            
            $icon = ModuleFacade::getModuleIconData($module);
            
            // 版本检查
            $versionCheck = '';
            if ($config['adaptation']) {
                if (version_compare(config('lake.version'), $config['adaptation'], '>=') == false) {
                    $versionCheck = '<i class="iconfont icon-delete text-danger"></i>';
                } else {
                    $versionCheck = '<i class="iconfont icon-success text-success"></i>';
                }
            }
            
            $needModule = [];
            $tableCheck = [];
            
            // 检查模块依赖
            if (isset($config['need_module']) && !empty($config['need_module'])) {
                $needModule = ModuleFacade::checkDependence($config['need_module']);
            }
            
            $this->assign('need_module', $needModule);
            $this->assign('version_check', $versionCheck);
            $this->assign('config', $config);
            $this->assign('icon', $icon);
            
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
                $this->error(__('请选择需要卸载的模块！'));
            }
            
            $status = ModuleFacade::uninstall($module);
            if ($status === false) {
                $error = ModuleFacade::getError();
                $this->error($error ? $error : __("模块卸载失败！"), url("Module/index"));
            }
            
            $this->success(__("模块卸载成功！"), url("Module/index"));
        } else {
            $module = $this->request->param('module', '');
            if (empty($module)) {
                $this->error(__('请选择需要卸载的模块！'));
            }
            
            $config = ModuleFacade::getInfoFromFile($module);
            $icon = ModuleFacade::getModuleIconData($module);
            
            $this->assign('config', $config);
            $this->assign('icon', $icon);
            
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
                $this->error(__('请选择需要更新的模块！'));
            }
            
            $status = ModuleFacade::upgrade($module);
            if ($status === false) {
                // 更新错误回滚，只是禁用模块
                ModuleFacade::upgradeRollback($module);
                
                $error = ModuleFacade::getError();
                $this->error($error ? $error : __('模块更新失败！'));
            }
            
            $this->success(__('模块更新成功！'), url('Module/index'));
        } else {
            $module = $this->request->param('module', '');
            if (empty($module)) {
                $this->error(__('请选择需要更新的模块！'));
            }
            
            $config = ModuleFacade::getInfoFromFile($module);
            
            // 版本检查
            $versionCheck = '';
            if ($config['adaptation']) {
                if (version_compare(config('lake.version'), $config['adaptation'], '>=') == false) {
                    $versionCheck = '<i class="iconfont icon-delete text-danger"></i>';
                } else {
                    $versionCheck = '<i class="iconfont icon-success text-success"></i>';
                }
            }
            
            $needModule = [];
            $tableCheck = [];
            
            // 检查模块依赖
            if (isset($config['need_module']) && !empty($config['need_module'])) {
                $needModule = ModuleFacade::checkDependence($config['need_module']);
            }
            
            $icon = ModuleFacade::getModuleIconData($module);
            
            $this->assign('need_module', $needModule);
            $this->assign('version_check', $versionCheck);
            $this->assign('config', $config);
            $this->assign('icon', $icon);
            
            return $this->fetch();
        
        }
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
            $this->error(__('访问错误！'));
        }
    
        $files = $this->request->file('file');
        if ($files == null) {
            $this->error(__("请选择上传文件！"));
        }
        
        $originalName = $files->getOriginalName();
        if (strtolower(substr($originalName, -3, 3)) != 'zip') {
            $this->error(__("上传的文件格式有误！"));
        }
        
        // 插件名称
        $modulePathinfo = pathinfo($originalName);
        $moduleName = $modulePathinfo['filename'];
        // 检查插件目录是否存在
        if (! ModuleFacade::isLocal($moduleName)) {
            $this->error(ModuleFacade::getError());
        }
        
        // 上传临时文件全路径地址
        $filename = $files->getPathname();
        $zip = new PclZip($filename);
        $status = $zip->extract(PCLZIP_OPT_PATH, env('lake_module_path') . $moduleName);
        if (! $status) {
            $this->error(__('模块解压失败！'));
        }
        
        $this->success(__('模块上传成功，可以进入模块管理进行安装！'), url('all'));
    }
    
    /**
     * 设置插件页面
     *
     * @create 2019-7-24
     * @author deatil
     */
    public function config()
    {
        if ($this->request->isGet()) {
            $moduleId = $this->request->param('module/s');
            if (empty($moduleId)) {
                $this->error(__('请选择需要操作的模块！'));
            }
            
            // 获取插件信息
            $module = ModuleModel::where([
                'module' => $moduleId,
                'status' => 1,
            ])->find();
            if (empty($module)) {
                $this->error(__('该模块没有安装或者被禁用！'));
            }
            
            $setting = json_decode($module['setting'], true);
            $settingData = $module['setting_data'];
            
            // 载入插件配置数组
            if (!empty($settingData)) {
                $settingData = json_decode($settingData, true);
                if (!empty($settingData)) {
                    foreach ($setting as $key => $value) {
                        if ($value['type'] != 'group') {
                            $setting[$key]['value'] = isset($settingData[$key]) ? $settingData[$key] : '';
                        } else {
                            foreach ($value['options'] as $gourp => $options) {
                                foreach ($options['options'] as $gkey => $gvalue) {
                                    $setting[$key]['options'][$gourp]['options'][$gkey]['value'] = $settingData[$gkey];
                                }
                            }
                        }
                    }
                }
            }
            
            $module['setting'] = $setting;
            $this->assign('data', $module);
            
            return $this->fetch();
        } else {
            $moduleId = $this->request->param('module/s');
            if (empty($moduleId)) {
                $this->error(__('请选择需要操作的模块！'));
            }
            
            // 获取模块信息
            $module = ModuleModel::where([
                'module' => $moduleId,
                'status' => 1,
            ])->find();
            if (empty($module)) {
                $this->error(__('该模块没有安装或者被禁用！'));
            }
            
            $config = $this->request->param('config/a');
            $flag = ModuleModel::where([
                'module' => $moduleId,
            ])->data([
                'setting_data' => json_encode($config),
            ])->update();
            
            if ($flag === false) {
                $this->error(__('保存失败'));
            }
            
            $this->success(__('保存成功'));
        }
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
            $this->error(__("请求错误！"));
        }
        
        $module = $this->request->param('module/s');
        $data = ModuleModel::where([
            "module" => $module,
        ])->find();
        if (empty($data)) {
            $this->error(__('信息不存在！'));
        }
        
        $data['need_module'] = json_decode($data['need_module'], true);
        
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
            $this->error(__('访问错误！'));
        }
        
        $module = $this->request->param('module/s');
        if (empty($module)) {
            $this->error(__('模块ID错误！'));
        }
        
        $status = ModuleFacade::enable($module);
        
        if ($status === false) {
            $error = ModuleFacade::getError();
            $this->error($error);
        }
        
        $this->success(__('启用成功！'));
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
            $this->error(__('访问错误！'));
        }
        
        $module = $this->request->param('module/s');
        if (empty($module)) {
            $this->error(__('模块ID错误！'));
        }
        
        $status = ModuleFacade::disable($module);
        
        if ($status === false) {
            $error = ModuleFacade::getError();
            $this->error($error);
        }
        
        $this->success(__('禁用成功！'));
    }
    
}
