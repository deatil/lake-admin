<?php

namespace app\admin\model;

use app\admin\facade\Password as PasswordFacade;

/**
 * 管理员
 *
 * @create 2019-7-9
 * @author deatil
 */
class Admin extends ModelBase
{
    // 设置当前模型对应的完整数据表名称
    protected $name = 'lakeadmin_admin';
    
    // 设置主键名
    protected $pk = 'id';
    
    // 时间字段取出后的默认时间格式
    protected $dateFormat = false;
    
    // 插入数据自动
    protected $insert = [
        'status' => 1,
    ];
    
    /**
     * 设置ID信息
     *
     * @create 2019-12-29
     * @author deatil
     */
    protected function setIdAttr($value) {
        return md5(microtime().mt_rand(100000, 999999));
    }
    
    /**
     * 获取格式化时间
     *
     * @create 2019-12-29
     * @author deatil
     */
    public function getLastLoginTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    /**
     * 获取格式化IP
     *
     * @create 2019-12-29
     * @author deatil
     */
    public function getLastLoginIpAttr($value)
    {
        $value = intval($value);
        return long2ip($value);
    }
    
    /**
     * 管理员的分组列表
     *
     * @create 2020-8-19
     * @author deatil
     */
    public function groups()
    {
        return $this->belongsToMany(AuthGroup::class, AuthGroupAccess::class, 'group_id', 'admin_id');
    }
    
    /**
     * 管理员的日志列表
     *
     * @create 2020-8-19
     * @author deatil
     */
    public function logs()
    {
        return $this->hasMany(AdminLog::class, 'admin_id', 'id');
    }
    
    /**
     * 管理员的附件列表
     * @param string $type 关联类型
     * @return array
     *
     * @create 2020-8-19
     * @author deatil
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, [
            'type',
            'type_id', 
        ], 'admin');
    }

    /**
     * 创建管理员
     * @param array $data
     * @return boolean
     */
    public function createManager($data)
    {
        if (empty($data)) {
            $this->error = '没有数据！';
            return false;
        }
        
        $data['add_time'] = time();
        $data['add_ip'] = request()->ip(1);
        
        $id = $this->save($data);
        if ($id === false) {
            $this->error = '入库失败！';
            return false;
        }
        
        if (isset($data['roleid']) && !empty($data['roleid'])) {
            $roles = explode(',', $data['roleid']);
            unset($data['roleid']);
            
            $groupAccess = [];
            foreach ($roles as $role) {
                $groupAccess[] = [
                    'module' => 'admin',
                    'admin_id' => $this->id,
                    'group_id' => $role,
                ];
            }
            AuthGroupAccess::insertAll($groupAccess);
        }
        
        return $id;
    }

    /**
     * 编辑管理员
     * @param array $data [修改数据]
     * @return boolean
     */
    public function editManager($data)
    {
        if (empty($data) 
            || !isset($data['id']) 
            || !is_array($data)
        ) {
            $this->error = '没有修改的数据！';
            return false;
        }
        $info = $this->where([
            'id' => $data['id']
        ])->find();
        if (empty($info)) {
            $this->error = '该管理员不存在！';
            return false;
        }
        
        // 密码为空，表示不修改密码
        if (!isset($data['password']) || empty($data['password'])) {
            unset($data['password']);
            unset($data['encrypt']);
        } else {
            // 对密码进行处理
            $data['password'] = md5(trim($data['password']));
            $passwordinfo = $this->encryptPassword($data['password']); 
            $data['encrypt'] = $passwordinfo['encrypt'];
            $data['password'] = $passwordinfo['password'];
        }
        
        if (isset($data['roleid']) && !empty($data['roleid'])) {
            $roleid = $data['roleid'];
        }
        unset($data['roleid']);
        
        /*
        $status = $this->allowField(true)
            ->isUpdate(true)
            ->save($data, [
                'id' => $data['id'],
            ]);
        */
        
        $status = $this
            ->where([
                'id' => $data['id'],
            ])
            ->update($data);
        if ($status === false) {
            $this->error = '管理员信息更新失败！';
            return false;
        }
        
        if (isset($roleid) && !empty($roleid)) {
            $roles = explode(',', $roleid);
            
            AuthGroupAccess::where([
                'module' => 'admin',
                'admin_id' => $data['id'],
            ])->delete();
            
            $groupAccess = [];
            foreach ($roles as $role) {
                $groupAccess[] = [
                    'module' => 'admin',
                    'admin_id' => $data['id'],
                    'group_id' => $role,
                ];
            }
            AuthGroupAccess::insertAll($groupAccess);
        }
        
        return true;
    }

    /**
     * 删除管理员
     * @param type $id
     * @return boolean
     */
    public function deleteManager($id)
    {
        $id = trim($id);
        if (empty($id)) {
            $this->error = '请指定需要删除的用户ID！';
            return false;
        }
        if ($id == 1) {
            $this->error = '禁止对超级管理员执行该操作！';
            return false;
        }
        
        $status = $this->where([
            'id' => $id,
        ])->delete();
        
        if (false !== $status) {
            AuthGroupAccess::where([
                'module' => 'admin',
                'admin_id' => $id,
            ])->delete();
            
            return true;
        } else {
            $this->error = '删除失败！';
            return false;
        }
    }
    
    /**
     * 管理员密码加密
     * @param $password
     * @param $encrypt //传入加密串，在修改密码时做认证
     * @return array/password
     */
    protected function encryptPassword($password, $encrypt = '')
    {
        $pwd = PasswordFacade::setSalt(config("app.admin_salt"))->encrypt($password, $encrypt);
        return $pwd;
    }
    
    /**
     * 获取错误信息
     * @access public
     * @return mixed
     *
     * @create 2019-7-9
     * @author deatil
     */
    public function getError()
    {
        return $this->error;
    }
    
}
