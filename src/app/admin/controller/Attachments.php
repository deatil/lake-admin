<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Hook;

use app\admin\model\Attachment as AttachmentModel;

use app\admin\service\Attachment as AttachmentService;
use app\admin\service\Upload as UploadService;
use app\admin\service\Admin as AdminService;

/**
 * 附件管理
 *
 * @create 2019-8-4
 * @author deatil
 */
class Attachments extends Base
{
    protected $AttachmentModel;

    private $uploadUrl = '';
    
    private $uploadPath = '';

    /**
     * 框架构造函数
     *
     * @create 2019-8-4
     * @author deatil
     */
    protected function initialize()
    {
        parent::initialize();
        
        $this->AttachmentModel = new AttachmentModel;
        $this->uploadUrl = config('upload_url');
        $this->uploadPath = config('upload_path');
    }

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
            $page = $this->request->param('page/d', 10);
            $map = $this->buildparams();
            
            $list = AttachmentModel::where($map)
                ->page($page, $limit)
                ->order('create_time desc')
                ->select();
            if (!empty($list)) {
                foreach ($list as $k => &$v) {
                    $v['path'] = $v['driver'] == 'local' ? $this->uploadUrl . $v['path'] : $v['path'];
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
            
            Hook::listen('AttachmentsIndexAjax', $result);
            
            return json($result);
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
        if (!$this->request->isGet()) {
            $this->error('访问错误！');
        }
        
        if (empty($id)) {
            $this->error('请选择需要查看的附件！');
        }
        
        $data = AttachmentModel::where([
            'id' => $id,
        ])->find();
    
        $data['path'] = ($data['driver'] == 'local') ? $this->uploadUrl . $data['path'] : $data['path'];
        
        Hook::listen('AttachmentsView', $data);
        
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
        if (!$this->request->isPost()) {
            $this->error('请求错误！');
        }
        
        $ids = $this->request->param('ids/a', null);
        if (empty($ids)) {
            $this->error('请选择需要删除的附件！');
        }
        
        if (!is_array($ids)) {
            $ids = [0 => $ids];
        }
        
        Hook::listen('AttachmentsDelete', $ids);
        
        foreach ($ids as $id) {
            try {
                $this->AttachmentModel->deleteFile($id);
            } catch (\Exception $ex) {
                $this->error($ex->getMessage());
            }
        }
        
        $this->success('文件删除成功！');
    }

    /**
     * 附件上传
     *
     * @create 2019-7-18
     * @author deatil
     */
    public function upload(
        $dir = '', 
        $from = '', 
        $module = '', 
        $thumb = 0, 
        $thumbsize = '', 
        $thumbtype = '', 
        $watermark = 1, 
        $sizelimit = -1, 
        $extlimit = ''
    ) {
        $UploadService = new UploadService();
        
        $admin_id = AdminService::instance()->id;
        return $UploadService->setTypeInfo('admin', $admin_id)
            ->save($dir, $from, $module, $thumb, $thumbsize, $thumbtype, $watermark, $sizelimit, $extlimit);
    }

    /**
     * html代码远程图片本地化
     *
     * @create 2019-7-18
     * @author deatil
     */
    public function getUrlFile()
    {
        $AttachmentService = new AttachmentService();
        
        $admin_id = AdminService::instance()->id;
        return $AttachmentService->setTypeInfo('admin', $admin_id)
            ->getUrlFile();
    }

}
