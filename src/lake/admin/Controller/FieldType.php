<?php

namespace Lake\Admin\Controller;

use Lake\Admin\Model\Admin as AdminUserModel;
use Lake\Admin\Model\AuthGroup as AuthGroupModel;
use Lake\Admin\Model\FieldType as FieldTypeModel;

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
            
            $data = FieldTypeModel::where($map)
                ->page($page, $limit)
                ->order('listorder ASC')
                ->select()
                ->toArray();
            
            $total = FieldTypeModel::where($map)
                ->order('listorder ASC')
                ->count();
        
            $result = [
                "code" => 0, 
                "count" => $total, 
                "data" => $data
            ];
            
            return $this->json($result);
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
            
            $result = $this->validate($data, 'Lake\\Admin\\Validate\\FieldType.insert');
            if (true !== $result) {
                return $this->error($result);
            }
            
            if (isset($data['ifoption']) && $data['ifoption'] == 1) {
                $data['ifoption'] = 1;
            } else {
                $data['ifoption'] = 0;
            }
            
            if (isset($data['ifstring']) && $data['ifstring'] == 1) {
                $data['ifstring'] = 1;
            } else {
                $data['ifstring'] = 0;
            }
            
            $data['is_system'] = 0;
            
            $rs = FieldTypeModel::create($data);
       
            if ($rs === false) {
                $this->error(__("添加失败！"));
            }
            
            $this->success(__("添加成功！"));

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
            $result = $this->validate($data, 'Lake\\Admin\\Validate\\FieldType.update');
            if (true !== $result) {
                return $this->error($result);
            }
            
            if (isset($data['ifoption']) && $data['ifoption'] == 1) {
                $data['ifoption'] = 1;
            } else {
                $data['ifoption'] = 0;
            }
            
            if (isset($data['ifstring']) && $data['ifstring'] == 1) {
                $data['ifstring'] = 1;
            } else {
                $data['ifstring'] = 0;
            }
            
            $id = $data['id'];
            unset($data['id']);
            $rs = FieldTypeModel::where([
                'id' => $id,
            ])->update($data);
            
            if ($rs === false) {
                $this->error(__("修改失败！"));
            }
            
            $this->success(__("修改成功！"));
        } else {
            $id = $this->request->param('id');
            
            $data = FieldTypeModel::where([
                "id" => $id,
            ])->find();
            if (empty($data)) {
                $this->error(__('信息不存在！'));
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
            $this->error(__('请求错误！'));
        }
        
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error(__('参数不能为空！'));
        }
        
        $data = FieldTypeModel::where([
            "id" => $id,
        ])->find();
        if (empty($data)) {
            $this->error(__('信息不存在！'));
        }
        
        if ($data['is_system'] == 1) {
            $this->error(__('系统字段类型不能被删除！'));
        }
        
        $rs = FieldTypeModel::where([
                'id' => $id, 
            ])
            ->delete();
        
        if ($rs === false) {
            $this->error(__("删除失败！"));
        }
        
        $this->success(__("删除成功！"));
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
            $this->error(__('请求错误！'));
        }
        
        $id = $this->request->param('id', '');
        if (empty($id)) {
            $this->error(__('参数不能为空！'));
        }
        
        $listorder = $this->request->param('value/d', 100);
        
        $rs = FieldTypeModel::where([
                'id' => $id, 
            ])
            ->update([
                'listorder' => $listorder,
            ]);
        
        if ($rs === false) {
            $this->error(__("排序失败！"));
        }
        
        $this->success(__("排序成功！"));
    }
    
}
