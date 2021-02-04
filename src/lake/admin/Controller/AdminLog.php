<?php

namespace Lake\Admin\Controller;

use Lake\Admin\Model\AdminLog as AdminlogModel;

/**
 * 管理日志
 *
 * @create 2019-8-4
 * @author deatil
 */
class AdminLog extends Base
{
    /**
     * 日志首页
     *
     * @create 2019-8-4
     * @author deatil
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $limit = $this->request->param('limit/d', 20);
            $page = $this->request->param('page/d', 1);
            
            $map = $this->buildparams();
            
            $method = $this->request->param('method/s', '');
            if (!empty($method)) {
                $map[] = ['method', '=', $method];
            }
            
            $data = AdminlogModel::where($map)
                ->page($page, $limit)
                ->order('create_time desc')
                ->select()
                ->toArray();
            
            $total = AdminlogModel::where($map)
                ->order('id DESC')
                ->count();
            
            $result = [
                "code" => 0, 
                "count" => $total, 
                "data" => $data,
            ];
            
            return $this->json($result);
        } else {
            return $this->fetch();
        }
    }
    
    /**
     * 详情
     *
     * @create 2019-7-28
     * @author deatil
     */
    public function view()
    {
        if (!$this->request->isGet()) {
            $this->error(__('访问错误！'));
        }
        
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error(__('信息ID错误！'));
        }
        
        $data = AdminlogModel::where([
                "id" => $id,
            ])
            ->find();
        if (empty($data)) {
            $this->error(__('信息不存在！'));
        }
        
        $this->assign("data", $data);
        return $this->fetch();
    }
    
    /**
     * 删除一个月前的操作日志
     *
     * @create 2019-8-4
     * @author deatil
     */
    public function clear()
    {
        if (!$this->request->isPost()) {
            $this->error(__('请求错误！'));
        }
        
        $status = AdminlogModel::deleteAMonthago();
        if ($status === false) {
            $this->error(__("删除日志失败！"));
        }
        
        $this->success(__("删除日志成功！"));
    }
}
