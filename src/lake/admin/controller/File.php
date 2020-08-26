<?php

namespace lake\admin\controller;

use lake\admin\service\Attachment as AttachmentService;
use lake\admin\service\Upload as UploadService;
use lake\admin\facade\Admin as AdminFacade;

/**
 * 文件上传
 *
 * @create 2020-8-26
 * @author deatil
 */
class File extends Base
{
    /**
     * 附件上传
     *
     * @create 2020-8-26
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
        $UploadService = (new UploadService);
        
        $admin_id = AdminFacade::getLoginUserInfo('id');
        return $UploadService->setTypeInfo('admin', $admin_id)
            ->save($dir, $from, $module, $thumb, $thumbsize, $thumbtype, $watermark, $sizelimit, $extlimit);
    }

    /**
     * html代码远程图片本地化
     *
     * @create 2020-8-26
     * @author deatil
     */
    public function getUrlFile()
    {
        $AttachmentService = (new AttachmentService);
        
        $admin_id = AdminFacade::getLoginUserInfo('id');
        return $AttachmentService->setTypeInfo('admin', $admin_id)
            ->getUrlFile();
    }

}
