<?php

namespace Lake\Admin\Controller;

use think\facade\Validate;

use Lake\Admin\Facade\Module as ModuleFacade;
use Lake\Admin\Model\Config as ConfigModel;
use Lake\Admin\Model\FieldType as FieldTypeModel;

/**
 * 系统配置
 *
 * @create 2019-7-31
 * @author deatil
 */
class Config extends Base
{
    /**
     * 配置首页
     *
     * @create 2019-7-31
     * @author deatil
     */
    public function index($group = 'system')
    {
        if ($this->request->isAjax()) {
            $list = ConfigModel::field('id,name,title,type,listorder,status,is_system,update_time')
                ->withJoin(['fieldType'])
                ->where('group', $group)
                ->order('listorder ASC, create_time DESC')
                ->select()
                ->visible([
                    'fieldType' => [
                        'title',
                    ]
                ])
                ->toArray();
                
            return $this->json([
                "code" => 0, 
                "data" => $list
            ]);
        } else {
            $this->assign([
                'groupArray' => config('app.config_group'),
                'group' => $group,
            ]);
            return $this->fetch();
        }
    }
    
    /**
     * 全部配置
     *
     * @create 2019-7-27
     * @author deatil
     */
    public function all()
    {
        if ($this->request->isAjax()) {
            $limit = $this->request->param('limit/d', 20);
            $page = $this->request->param('page/d', 1);
            
            $searchField = $this->request->param('search_field/s', '', 'trim');
            $keyword = $this->request->param('keyword/s', '', 'trim');
            
            $map = [];
            if (!empty($searchField) && !empty($keyword)) {
                $searchField = $searchField;
                $map[] = [$searchField, 'like', "%$keyword%"];
            }
            
            $data = ConfigModel::field('id,name,title,group,type,listorder,status,is_system,update_time')
                ->withJoin(['fieldType'])
                ->page($page, $limit)
                ->where($map)
                ->order('group ASC, listorder ASC, name ASC, id DESC')
                ->select()
                ->visible([
                    'fieldType' => [
                        'title',
                    ]
                ])
                ->toArray();
            
            $total = ConfigModel::where($map)
                ->count();
            
            $result = [
                "code" => 0, 
                "count" => $total, 
                "data" => $data,
            ];
            return $this->json($result);
        } else {
            $this->assign([
                'groupArray' => config('app.config_group'),
                'group' => 'all',
            ]);
            return $this->fetch();
        }
    }
    
    /**
     * 配置设置
     *
     * @create 2019-7-31
     * @author deatil
     */
    public function setting($group = 'system')
    {
        if ($this->request->isPost()) {
            $data = $this->request->post('modelField/a');
            
            // 字段规则
            $fieldRule = FieldTypeModel::column('vrule,pattern', 'name');
            
            // 查询该分组下所有的配置项名和类型
            $items = ConfigModel::where([
                'group' => $group,
                'is_show' => 1,
                'status' => 1,
            ])->column('name,type,title');
            
            if (!empty($items)) {
                foreach ($items as $item) {
                    $name = $item['name'];
                    $type = $item['type'];
                    $title = $item['title'];
                    //查看是否赋值
                    if (!isset($data[$name])) {
                        switch ($type) {
                            // 开关
                            case 'switch':
                                $data[$name] = 0;
                                break;
                            case 'checkbox':
                                $data[$name] = '';
                                break;
                        }
                    } else {
                        // 如果值是数组则转换成字符串，适用于复选框等类型
                        if (is_array($data[$name])) {
                            $data[$name] = implode(',', $data[$name]);
                        }
                        switch ($type) {
                            // 开关
                            case 'switch':
                                $data[$name] = 1;
                                break;
                        }
                    }
                    
                    // 数据格式验证
                    if (isset($fieldRule[$type]['vrule']) 
                        && !empty($fieldRule[$type]['vrule'])
                        && !empty($data[$name]) 
                    ) {
                        if (!empty($fieldRule[$type]['pattern'])) {
                            if (!call_user_func_array(['Validate', $fieldRule[$type]['vrule']], [
                                $data[$name],
                                $fieldRule[$type]['pattern']
                            ])) {
                                return $this->error("'" . $title . "'格式错误~");
                            }
                        } else {
                            if (!call_user_func_array(['Validate', $fieldRule[$type]['vrule']], [
                                $data[$name]
                            ])) {
                                return $this->error("'" . $title . "'格式错误~");
                            }
                        }
                    }
                    
                    if (isset($data[$name])) {
                        ConfigModel::where([
                            'name' => $name,
                        ])->update([
                            'value' => $data[$name],
                        ]);
                    }
                }
            }
            
            cache('lake_admin_config', null);
            return $this->success(__('设置更新成功'));
        } else {
            $configList = ConfigModel::where('group', $group)
                ->where([
                    'is_show' => 1,
                    'status' => 1,
                ])
                ->order('listorder,id desc')
                ->column('name,title,remark,type,value,options');
            foreach ($configList as &$value) {
                if ($value['options'] != '') {
                    $value['options'] = json_decode($value['options'], true);
                }
                if ($value['type'] == 'checkbox') {
                    $value['value'] = empty($value['value']) ? [] : explode(',', $value['value']);
                }
                if ($value['type'] == 'datetime') {
                    $value['value'] = empty($value['value']) ? date('Y-m-d H:i:s') : $value['value'];
                }
                if ($value['type'] == 'Ueditor') {
                    $value['value'] = htmlspecialchars_decode($value['value']);
                }
                $value['fieldArr'] = 'modelField';
            }
            $this->assign([
                'groupArray' => config('app.config_group'),
                'fieldList' => $configList,
                'group' => $group,
            ]);

            return $this->fetch();
        }

    }

    /**
     * 新增配置
     *
     * @create 2019-7-31
     * @author deatil
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            
            if (isset($data['status']) 
                && $data['status'] == 1) {
                $data['status'] = 1;
            } else {
                $data['status'] = 0;
            }
            
            $result = $this->validate($data, 'Lake\\Admin\\Validate\\Config');
            if (false === $result) {
                return $this->error($result);
            }
            
            $status = ConfigModel::create($data);
            if (false === $status) {
                $this->error(__('配置添加失败！'));
            }
            
            cache('lake_admin_config', null); //清空缓存配置
            $this->success(__('配置添加成功！'));
        } else {
            $fieldType = FieldTypeModel::order('listorder')
                ->column('name,title,ifoption,ifstring');
            
            $group = $this->request->param('group');
            
            // 模块列表
            $modules = ModuleFacade::getAll();
            
            $this->assign([
                'modules' => $modules,
                'groupArray' => config('app.config_group'),
                'fieldType' => $fieldType,
                'group' => $group,
            ]);
    
            return $this->fetch();
        }
    }
    
    /**
     * 编辑配置
     *
     * @create 2019-7-31
     * @author deatil
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $result = $this->validate($data, 'Lake\\Admin\\Validate\\Config');
            if (false === $result) {
                return $this->error($result);
            }
            
            if (!isset($data['id']) || empty($data['id'])) {
                return $this->error(__('配置ID不能为空！'));
            }
            
            $id = $data['id'];
            unset($data['id']);
            
            $info = ConfigModel::where([
                'id' => $id,
            ])->find();
            if (empty($info)) {
                $this->error(__('信息不存在！'));
            }
            
            if ($info['is_system'] == 1) {
                unset($data['name'], $data['module']);
            }
            
            $status = ConfigModel::where([
                'id' => $id,
            ])->update($data);
            if ($status === false) {
                $this->error(__('配置编辑失败！'));
            }
            
            cache('lake_admin_config', null); //清空缓存配置
            $this->success(__('配置编辑成功！'));
        } else {
            $id = $this->request->param('id');
            if (empty($id) || strlen($id) != 32) {
                $this->error(__('参数错误！'));
            }
            
            $fieldType = FieldTypeModel::order('listorder')
                ->column('name,title,ifoption,ifstring');
            
            $info = ConfigModel::where([
                'id' => $id,
            ])->find();
            if (empty($info)) {
                $this->error(__('信息不存在！'));
            }
            
            // 模块列表
            $modules = ModuleFacade::getAll();
            
            $this->assign([
                'modules' => $modules,
                'groupArray' => config('app.config_group'),
                'fieldType' => $fieldType,
                'info' => $info,
            ]);
            
            return $this->fetch();
        }
    }
    
    /**
     * 删除配置
     *
     * @create 2019-7-31
     * @author deatil
     */
    public function del()
    {
        if (!$this->request->isPost()) {
            $this->error(__('请求错误！'));
        }
        
        $id = $this->request->param('id');
        if (empty($id) || strlen($id) != 32) {
            $this->error(__('参数错误！'));
        }
        
        $info = ConfigModel::where([
            'id' => $id,
        ])->find();
        if (empty($info)) {
            $this->error(__('信息不存在！'));
        }
        
        if ($info['is_system'] == 1) {
            $this->error(__('系统默认配置不可操作！'));
        }
        
        $re = ConfigModel::where([
            'id' => $id,
        ])->delete();
        if ($re === false) {
            $this->error(__('删除失败！'));
        }
        
        cache('lake_admin_config', null); //清空缓存配置
        $this->success(__('删除成功'));
    }
    
    /**
     * 排序
     *
     * @create 2019-7-31
     * @author deatil
     */
    public function listorder()
    {
        if (!$this->request->isPost()) {
            $this->error(__('请求错误！'));
        }
        
        $id = $this->request->param('id');
        if (empty($id) || strlen($id) != 32) {
            $this->error(__('参数不能为空！'));
        }
        
        $listorder = $this->request->param('value/d', 0);
        if (empty($listorder)) {
            $listorder = 100;
        }
        
        $rs = ConfigModel::where([
            'id' => $id,
        ])->update([
            'listorder' => $listorder,
        ]);
        
        if ($rs === false) {
            $this->error(__("排序失败！"));
        }
        
        cache('lake_admin_config', null); //清空缓存配置
        $this->success(__("排序成功！"));
    }
    
    /**
     * 设置配置状态
     *
     * @create 2019-7-31
     * @author deatil
     */
    public function setstate($id, $status)
    {
        if (!$this->request->isPost()) {
            $this->error(__('请求错误！'));
        }
        
        $id = $this->request->param('id');
        if (empty($id) || strlen($id) != 32) {
            $this->error(__('参数不能为空！'));
        }
        
        $status = $this->request->param('status/d');
        if ($status != 1) {
            $status = 0;
        }
        
        $rs = ConfigModel::where([
            'id' => $id,
        ])->update([
            'status' => $status,
        ]);
        if ($rs === false) {
            $this->error(__('操作失败！'));
        }
        
        cache('lake_admin_config', null); //清空缓存配置
        $this->success(__('操作成功！'));
    }
    
}
