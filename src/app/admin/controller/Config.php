<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Validate;

use app\admin\model\Config as ConfigModel;
use app\admin\module\Module as ModuleService;

/**
 * 系统配置
 *
 * @create 2019-7-31
 * @author deatil
 */
class Config extends Base
{
    public $banfie;
	
	/**
	 * 框架构造函数
	 *
	 * @create 2019-8-4
	 * @author deatil
	 */
    protected function initialize()
    {
        parent::initialize();
		
        // 允许使用的字段列表
        $this->banfie = [
			"text", 
			"checkbox", 
			"textarea", 
			"radio", 
			"number", 
			"datetime", 
			"image", 
			"images", 
			"array", 
			"switch", 
			"select", 
			"Ueditor", 
			"file", 
			"files", 
			"color"
		];
    }

	/**
	 * 配置首页
	 *
	 * @create 2019-7-31
	 * @author deatil
	 */
    public function index($group = 'system')
    {
        if ($this->request->isAjax()) {
            $_list = Db::view('config', 'id,name,title,type,listorder,status,update_time')
                ->where('group', $group)
                ->view('field_type', 'title as ftitle', 'field_type.name=config.type', 'LEFT')
                ->order('listorder,id desc')
                ->select();
            $result = [
				"code" => 0, 
				"data" => $_list
			];
            return json($result);
        } else {
            $this->assign([
                'groupArray' => config('config_group'),
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

			$search_field = $this->request->param('search_field/s', '', 'trim');
			$keyword = $this->request->param('keyword/s', '', 'trim');
			
			$map = [];
			if (!empty($search_field) && !empty($keyword)) {
				$search_field = 'c.'.$search_field;
				$map[] = [$search_field, 'like', "%$keyword%"];
			}
			
            $data = Db::name('config')
				->alias('c')
				->leftJoin('field_type ft ', 'ft.name=c.type')
				->field('
					c.*,
					ft.title as ftitle
				')
                ->page($page, $limit)
				->where($map)
                ->order('c.group ASC, c.listorder ASC, c.name ASC, c.id DESC')
                ->select();
            $total = Db::name('config')
				->alias('c')
				->where($map)
				->count();
            
			$result = [
				"code" => 0, 
				"count" => $total, 
				"data" => $data,
			];
            return json($result);
        } else {
            $this->assign([
                'groupArray' => config('config_group'),
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
            $fieldRule = Db::name('field_type')
				->column('vrule,pattern', 'name');
			
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
						])->setField('value', $data[$name]);
					}
				}
			}
			
            cache('lake_admin_config', null);
            return $this->success('设置更新成功');
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
                    $value['options'] = parse_attr($value['options']);
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
                'groupArray' => config('config_group'),
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
            
			$data['status'] = isset($data['status']) ? intval($data['status']) : 1;
            
			$result = $this->validate($data, 'Config');
			if (false === $result) {
                return $this->error($result);
            }
			
			$data['id'] = md5(mt_rand(100000, 999999).microtime().mt_rand(100000, 999999));
			$status = ConfigModel::create($data);
			if (false === $status) {
                $this->error('配置添加失败！');
            }
			
			cache('lake_admin_config', null); //清空缓存配置
			$this->success('配置添加成功~');
        } else {
            $fieldType = Db::name('field_type')
				->where('name', 'in', $this->banfie)
				->order('listorder')
				->column('name,title,ifoption,ifstring');
			
	        $group = $this->request->param('group');
			
			// 模块列表
			$modules = (new ModuleService())->getAll();
			
            $this->assign([
				'modules' => $modules,
                'groupArray' => config('config_group'),
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
            $result = $this->validate($data, 'Config');
            if (false === $result) {
                return $this->error($result);
            }
			
            if (!isset($data['id']) || empty($data['id'])) {
                return $this->error('配置ID不能为空！');
            }
			$id = $data['id'];
			unset($data['id']);
			
			$status = ConfigModel::where([
				'id' => $id,
			])->update($data);
            if ($status === false) {
                $this->error('配置编辑失败！');
            }
			
			cache('lake_admin_config', null); //清空缓存配置
			$this->success('配置编辑成功~');
        } else {
            $id = $this->request->param('id');
            if (empty($id) || strlen($id) != 32) {
				$this->error('参数错误！');
            }
			
            $fieldType = Db::name('field_type')
				->where('name', 'in', $this->banfie)
				->order('listorder')
				->column('name,title,ifoption,ifstring');
            $info = ConfigModel::get($id);
			
			// 模块列表
			$modules = (new ModuleService())->getAll();
			
            $this->assign([
				'modules' => $modules,
                'groupArray' => config('config_group'),
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
			$this->error('请求错误！');
		}
		
        $id = $this->request->param('id');
		if (empty($id) || strlen($id) != 32) {
            $this->error('参数错误！');
        }
		
		$re = ConfigModel::where([
			'id' => $id,
		])->delete();
        if ($re === false) {
            $this->error('删除失败！');
        }
		
		cache('lake_admin_config', null); //清空缓存配置
		$this->success('删除成功');
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
			$this->error('请求错误！');
		}
		
        $id = $this->request->param('id');
		if (empty($id) || strlen($id) != 32) {
			$this->error('参数不能为空！');
		}
		
        $listorder = $this->request->param('value/d', 0);
        if (empty($id)) {
			$listorder = 100;
		}
		
        $rs = ConfigModel::update([
			'listorder' => $listorder,
		], [
			'id' => $id,
		], true);
		
        if ($rs === false) {
            $this->error("排序失败！");
        }
		
		cache('lake_admin_config', null); //清空缓存配置
		$this->success("排序成功！");
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
			$this->error('请求错误！');
		}
		
        $id = $this->request->param('id');
		if (empty($id) || strlen($id) != 32) {
			$this->error('参数不能为空！');
		}
		
        $status = $this->request->param('status/d');
		if ($status != 1) {
			$status = 0;
		}
		
		$status = ConfigModel::update([
			'status' => $status,
		], [
			'id' => $id,
		]);
        if ($status === false) {
            $this->error('操作失败！');
        }
		
		cache('lake_admin_config', null); //清空缓存配置
		$this->success('操作成功！');
    }

}
