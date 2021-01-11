<?php

namespace Lake\Admin\Controller;

use think\facade\Event;

use Lake\Admin\Model\Attachment as AttachmentModel;
use Lake\Admin\Service\Attachment as AttachmentService;

/**
 * 附件管理
 *
 * @create 2019-8-4
 * @author deatil
 */
class Attachments extends Base
{
    /**
     * 附件列表页
     *
     * @create 2019-7-18
     * @author deatil
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $limit = $this->request->param('limit/d', 10);
            $page = $this->request->param('page/d', 1);
            $map = $this->buildparams();
            
            $list = AttachmentModel::where($map)
                ->page($page, $limit)
                ->order('create_time desc')
                ->select()
                ->toArray();
            if (! empty($list)) {
                foreach ($list as $k => &$v) {
                    $v['path'] = AttachmentModel::objectUrl($v['path']);
                }
                unset($v);
            }
            
            $total = AttachmentModel::where($map)
                ->order('create_time desc')
                ->count();
            $result = [
                "code" => 0, 
                "count" => $total, 
                "data" => $list,
            ];
            
            Event::trigger('AttachmentsIndexAjax', $result);
            
            return $this->json($result);
        } else {
            return $this->fetch();
        }
    }
    
    /**
     * 附件详情
     *
     * @create 2019-7-18
     * @author deatil
     */
    public function view($id)
    {
        if (! $this->request->isGet()) {
            $this->error(__('访问错误！'));
        }
        
        if (empty($id)) {
            $this->error(__('请选择需要查看的附件！'));
        }
        
        $data = AttachmentModel::where([
            'id' => $id,
        ])->find();
    
        $data['path'] = AttachmentModel::objectUrl($data['path']);
        
        Event::trigger('AttachmentsView', $data);
        
        $this->assign('data', $data);
        
        return $this->fetch();
    }
    
    /**
     * 附件删除
     *
     * @create 2019-7-18
     * @author deatil
     */
    public function delete()
    {
        if (! $this->request->isPost()) {
            $this->error(__('请求错误！'));
        }
        
        $ids = $this->request->param('ids/a', null);
        if (empty($ids)) {
            $this->error(__('请选择需要删除的附件！'));
        }
        
        if (! is_array($ids)) {
            $ids = [0 => $ids];
        }
        
        Event::trigger('AttachmentsDelete', $ids);
        
        foreach ($ids as $id) {
            try {
                (new AttachmentService)->deleteFile($id);
            } catch (\Exception $ex) {
                $this->error($ex->getMessage());
            }
        }
        
        $this->success(__('文件删除成功！'));
    }

}
