<?php

namespace app\admin\model;

/**
 * 用户组模型类
 *
 * @create 2019-7-9
 * @author deatil
 */
class AuthGroup extends ModelBase
{
    // 设置当前模型对应的数据表名称
    protected $name = 'lakeadmin_auth_group';
    
    // 设置主键名
    protected $pk = 'id';

    protected $resultSetType = 'collection';
    
    // 时间字段取出后的默认时间格式
    protected $dateFormat = false;
    
    const TYPE_ADMIN = 1;
    
    /**
     * 组的权限列表
     *
     * @create 2020-8-19
     * @author deatil
     */
    public function rules()
    {
        return $this->belongsToMany(AuthRule::class, AuthRuleAccess::class, 'rule_id', 'group_id');
    }
    
    /**
     * 组的管理员列表
     *
     * @create 2020-8-19
     * @author deatil
     */
    public function admins()
    {
        return $this->belongsToMany(Admin::class, AuthGroupAccess::class, 'admin_id', 'group_id');
    }
    
    /**
     * 组的扩展权限列表
     *
     * @create 2020-8-19
     * @author deatil
     */
    public function extendRule()
    {
        return $this->hasOne(AuthRuleExtend::class, 'group_id', 'id');
    }

    /**
     * 返回用户组列表
     * 默认返回正常状态的管理员用户组列表
     * @param array $where   查询条件,供where()方法使用
     */
    public function getGroups($where = [])
    {
        $map = [
            'status' => 1, 
            'type' => self::TYPE_ADMIN, 
            'module' => 'admin'
        ];
        
        $data = $this->where($map)
            ->where($where)
            ->order('listorder ASC')
            ->select()
            ->toArray();
        return $data;
    }

    /**
     * 根据角色Id获取角色名
     * @param int $Groupid 角色id
     * @return string 返回角色名
     */
    public function getRoleIdName($Groupid)
    {
        return $this->where([
            'id' => $Groupid,
        ])->value('title');
    }

    /**
     * 通过递归的方式获取该角色下的全部子角色
     * @param type $id
     * @return string
     */
    public function getArrchildid($id)
    {
        if (empty($this->roleList)) {
            $this->roleList = $this->order([
                "id" => "desc",
            ])->column('*', 'id');
        }
        $arrchildid = $id;
        if (is_array($this->roleList)) {
            foreach ($this->roleList as $k => $cat) {
                if ($cat['parentid'] && $k != $id && $cat['parentid'] == $id) {
                    $arrchildid .= ',' . $this->getArrchildid($k);
                }
            }
        }
        return $arrchildid;
    }

    /**
     * 删除角色
     * @param int $Groupid 角色ID
     * @return boolean
     */
    public function groupDelete($Groupid)
    {
        if (empty($Groupid) || $Groupid == 1) {
            $this->error = '超级管理员角色不能被删除！';
            return false;
        }
        //角色信息
        $info = $this->where([
            'id' => $Groupid,
        ])->find();
        if (empty($info)) {
            $this->error = '该角色不存在！';
            return false;
        }
        //子角色列表
        $child = explode(',', $this->getArrchildid($Groupid));
        if (count($child) > 1) {
            $this->error = '该角色下有子角色，请删除子角色才可以删除！';
            return false;
        }
        
        $status = $this->where(['id' => $Groupid])->delete();
        if ($status !== false) {
            AuthRuleAccess::where([
                'module' => 'admin',
                'group_id' => $Groupid,
            ])->delete();
        }
        
        return $status !== false ? true : false;
    }
}
