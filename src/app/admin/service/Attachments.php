<?php

namespace app\admin\service;

use think\facade\Db;

use app\admin\model\Attachment as AttachmentModel;

/**
 * 附件上传处理类
 *
 * @create 2019-7-13
 * @author deatil
 */
class Attachments
{
    public $type = '';
    public $typeId = 0;

    private $uploadUrl = '';
    private $uploadPath = '';

    public function __construct()
    {
        $this->uploadUrl = config('app.upload_url');
        $this->uploadPath = config('app.upload_path');
        
        $this->AttachmentModel = new AttachmentModel;
    }
    
    /**
     * 设置类型数据
     *
     * @create 2019-7-18
     * @author deatil
     */
    public function setTypeInfo($type, $typeId)
    {
        $this->type = $type;
        $this->typeId = $typeId;
        
        return $this;
    }

    /**
     * html代码远程图片本地化
     * @param string $content html代码
     * @param string $type 文件类型
     */
    public function getUrlFile()
    {
        $content = $this->request->post('content');
        $type = $this->request->post('type');
        $urls = [];
        preg_match_all("/(src|SRC)=[\"|'| ]{0,}((http|https):\/\/(.*)\.(gif|jpg|jpeg|bmp|png|tiff))/isU", $content, $urls);
        $urls = array_unique($urls[2]);

        $file_info = [
            'module' => 'admin',
            'type' => $this->type,
            'type_id' => $this->typeId,
            'thumb' => '',
        ];
        foreach ($urls as $vo) {
            $vo = trim(urldecode($vo));
            $host = parse_url($vo, PHP_URL_HOST);
            if ($host != $_SERVER['HTTP_HOST']) {
                //当前域名下的文件不下载
                $fileExt = strrchr($vo, '.');
                if (!in_array($fileExt, ['.jpg', '.gif', '.png', '.bmp', '.jpeg', '.tiff'])) {
                    exit($content);
                }
                $filename = $this->uploadPath . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . md5($vo) . $fileExt;
                if (lake_http_down($vo, $filename) !== false) {
                    $file_info['md5'] = hash_file('md5', $filename);
                    if ($file_exists = AttachmentModel::get(['md5' => $file_info['md5']])) {
                        unlink($filename);
                        $localpath = $this->uploadUrl . $file_exists['path'];
                    } else {
                        $file_info['sha1'] = hash_file('sha1', $filename);
                        $file_info['size'] = filesize($filename);
                        $file_info['mime'] = mime_content_type($filename);

                        $fpath = $type . DIRECTORY_SEPARATOR . date('Ymd');
                        $savePath = $this->uploadPath . DIRECTORY_SEPARATOR . $fpath;
                        if (!is_dir($savePath)) {
                            mkdir($savePath, 0755, true);
                        }
                        $fname = DIRECTORY_SEPARATOR . md5(microtime(true)) . $fileExt;
                        $file_info['name'] = $vo;
                        $file_info['path'] = str_replace(DIRECTORY_SEPARATOR, '/', $fpath . $fname);
                        $file_info['ext'] = ltrim($fileExt, ".");

                        if (rename($filename, $savePath . $fname)) {
                            AttachmentModel::create($file_info);
                            $localpath = $this->uploadUrl . $file_info['path'];
                        }
                    }
                    $content = str_replace($vo, $localpath, $content);
                }
            }
        }
        exit($content);
    }

}
