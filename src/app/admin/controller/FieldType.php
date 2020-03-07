<?php

namespace app\admin\controller;

use think\Db;

use app\admin\model\Admin as AdminUserModel;
use app\admin\model\AuthGroup as AuthGroupModel;

/**
 * 字段类型表
 *
 * @create 2019-7-10
 * @author deatil
 */
class FieldType extends Base
{
    // 字段类型列表
    protected $types = [
        'int', 
        'varchar', 
        'text', 
        'date', 
        
        'tinyint', 
        'smallint', 
        'mediumint', 
        'int', 
        'bigint', 
        'decimal', 
        'float', 
        'double', 
        'real', 
        'bit', 
        'boolean', 
        'serial', 
        
        'date', 
        'datetime', 
        'timestamp', 
        'time', 
        'year', 
        
        'char', 
        'varchar', 
        'tinytext', 
        'text', 
        'mediumtext', 
        'longtext', 
        'binary', 
        'varbinary', 
        'tinyblob', 
        'mediumblob', 
        'blob', 
        'longblob', 
        'enum', 
        'set', 
        
        'geometry', 
        'point', 
        'linestring', 
        'polygon', 
        'multipoint', 
        'multilinestring', 
        'multipolygon', 
        'geometrycollection', 
    ];
    
    // 验证规则列表
    protected $vrules = [
        'isRequire',
        'isMust',
        'isNumber',
        'isInteger',
        'isFloat',
        'isBoolean',
        'isBool',
        'isEmail',
        'isMobile',
        'isArray',
        'isAccepted',
        'isDate',
        'isFile',
        'isImage',
        'isAlpha',
        'isAlphaNum',
        'isAlphaDash',
        'isActiveUrl',
        'isChs',
        'isChsAlpha',
        'isChsAlphaNum',
        'isChsDash',
        'isUrl',
        'isIp',
        'isDateFormat',
        'isIn',
        'isNotIn',
        'isBetween',
        'isNotBetween',
        'isLength',
        'isMax',
        'isMin',
        'isAfter',
        'isBefore',
        'isAfterWith',
        'isBeforeWith',
        'isExpire',
        'isAllowIp',
        'isDenyIp',
        'isConfirm',
        'isDifferent',
        'isEgt',
        'isGt',
        'isElt',
        'isLt',
        'isEq',
        'isUnique',
        'isRegex',
        'isMethod',
        'isToken',
        'isFileSize',
        'isFileExt',
        'isFileMime',
    ];
    
    /**
     * 框架构造函数
     *
     * @create 2019-8-4
     * @author deatil
     */
    protected function initialize()
    {
        parent::initialize();
    }

    /**
     * 列表
     *
     * @create 2019-7-10
     * @author deatil
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $limit = $this->request->param('limit/d', 10);
            $page = $this->request->param('page/d', 10);
            
            $map = $this->buildparams();
            
            $data = Db::name('field_type')
                ->where($map)
                ->page($page, $limit)
                ->order('listorder ASC')
                ->select();
            
            $total = Db::name('field_type')
                ->where($map)
                ->order('listorder ASC')
                ->count();
        
            $result = [
                "code" => 0, 
                "count" => $total, 
                "data" => $data
            ];
            
            return json($result);
        }
        return $this->fetch();
    }

    /**
     * 添加
     *
     * @create 2019-7-10
     * @author deatil
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post('');
            $result = $this->validate($data, 'FieldType.insert');
            if (true !== $result) {
                return $this->error($result);
            }
            
            if (!isset($data['ifoption']) || $data['ifoption'] == 0) {
                $data['ifoption'] = 0;
            } else {
                $data['ifoption'] = 1;
            }
            
            if (!isset($data['ifstring']) || $data['ifstring'] == 0) {
                $data['ifstring'] = 0;
            } else {
                $data['ifstring'] = 1;
            }
            
            $data['is_system'] = 0;
            $data['id'] = md5(mt_rand(100000, 999999).microtime().mt_rand(10000, 999999));
            
            $rs = Db::name('field_type')->data($data)->insert();
       
            if ($rs === false) {
                $this->error("添加失败！");
            }
            
            $this->success("添加成功！");

        } else {
            // 类型列表
            $this->assign("types", $this->types);
            
            // 验证规则列表
            $this->assign("vrules", $this->vrules);
            
            return $this->fetch();
        }
    }

    /**
     * 编辑
     *
     * @create 2019-7-10
     * @author deatil
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post('');
            $result = $this->validate($data, 'FieldType.update');
            if (true !== $result) {
                return $this->error($result);
            }
            
            if (!isset($data['ifoption']) || $data['ifoption'] == 0) {
                $data['ifoption'] = 0;
            } else {
                $data['ifoption'] = 1;
            }
            
            if (!isset($data['ifstring']) || $data['ifstring'] == 0) {
                $data['ifstring'] = 0;
            } else {
                $data['ifstring'] = 1;
            }
            
            $rs = Db::name('field_type')
                ->update($data);
            
            if ($rs === false) {
                $this->error("修改失败！");
            }
            
            $this->success("修改成功！");
        } else {
            $id = $this->request->param('id');
            $data = Db::name('field_type')->where([
                "id" => $id,
            ])->find();
            if (empty($data)) {
                $this->error('信息不存在！');
            }
            
            $this->assign("data", $data);
            
            // 类型列表
            $this->assign("types", $this->types);
            
            // 验证规则列表
            $this->assign("vrules", $this->vrules);
            
            return $this->fetch();
        }
    }

    /**
     * 删除
     *
     * @create 2019-7-10
     * @author deatil
     */
    public function del()
    {
        if (!$this->request->isPost()) {
            $this->error('请求错误！');
        }
        
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('参数不能为空！');
        }
        
        $data = Db::name('field_type')->where([
            "id" => $id,
        ])->find();
        if (empty($data)) {
            $this->error('信息不存在！');
        }
        
        if ($data['is_system'] == 1) {
            $this->error('系统字段类型不能被删除！');
        }
        
        $rs = Db::name('field_type')
            ->where([
                'id' => $id, 
            ])
            ->delete();
        
        if ($rs === false) {
            $this->error("删除失败！");
        }
        
        $this->success("删除成功！");
    }

    /**
     * 排序
     *
     * @create 2019-7-10
     * @author deatil
     */
    public function listorder()
    {
        if (!$this->request->isPost()) {
            $this->error('请求错误！');
        }
        
        $id = $this->request->param('id', '');
        if (empty($id)) {
            $this->error('参数不能为空！');
        }
        
        $listorder = $this->request->param('value/d', 100);
        
        $rs = Db::name('field_type')
            ->where([
                'id' => $id, 
            ])
            ->update([
                'listorder' => $listorder,
            ]);
        
        if ($rs === false) {
            $this->error("排序失败！");
        }
        
        $this->success("排序成功！");
    }

}
