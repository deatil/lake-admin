<?php

namespace app\admin\service;

use think\Image;

use app\admin\model\Attachment as AttachmentModel;

/**
 * 附件处理
 *
 * @create 2019-8-5
 * @author deatil
 */
class Attachment
{
    public $type = '';
    public $typeId = 0;

    private $uploadUrl = '';
    private $uploadPath = '';

    public function __construct()
    {
        $this->uploadUrl = config('app.upload_url');
        $this->uploadPath = config('app.upload_path');
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
    
    /**
     * 创建缩略图
     * @param string $file 目标文件，可以是文件对象或文件路径
     * @param string $dir 保存目录，即目标文件所在的目录名
     * @param string $saveName 缩略图名
     * @param string $thumbSize 尺寸
     * @param string $thumbType 裁剪类型
     * @return string 缩略图路径
     */
    public function createThumb(
        $file = '', 
        $filename = '', 
        $saveName = '', 
        $thumbSize = '', 
        $thumbType = ''
    ) {
        // 获取要生成的缩略图最大宽度和高度
        $thumbSize = $thumbSize == '' ? config('app.upload_image_thumb') : $thumbSize;
        list($thumbMaxWidth, $thumbMaxHeight) = explode(',', $thumbSize);
        // 读取图片
        $image = Image::open($file);
        // 生成缩略图
        $thumbType = $thumbType == '' ? config('app.upload_image_thumb_type') : $thumbType;
        $image->thumb($thumbMaxWidth, $thumbMaxHeight, $thumbType);

        if (!is_dir($filename)) {
            mkdir($filename, 0766, true);
        }
        $image->save($filename . $saveName);
        return $filename;
    }

    /**
     * 添加水印
     * @param string $file 要添加水印的文件路径
     * @param string $watermarkImg 水印图片id
     * @param string $watermarkPos 水印位置
     * @param string $watermarkAlpha 水印透明度
     */
    public function createWater(
        $file = '', 
        $watermarkImg = '', 
        $watermarkPos = '', 
        $watermarkAlpha = ''
    ) {
        $uploadPath = config('app.upload_path');
        $path = $this->getFilePath($watermarkImg, 1);
        $thumbWaterPic = realpath($uploadPath . '/' . $path);
        if (is_file($thumbWaterPic)) {
            // 读取图片
            $image = Image::open($file);
            // 添加水印
            $watermarkPos = $watermarkPos == '' ? config('app.upload_thumb_water_position')['key'] : $watermarkPos;
            $watermarkAlpha = $watermarkAlpha == '' ? config('app.upload_thumb_water_alpha') : $watermarkAlpha;
            $image->water($thumbWaterPic, $watermarkPos, $watermarkAlpha);
            // 保存水印图片，覆盖原图
            $image->save($file);
        }
    }

    /**
     * 根据附件id获取路径
     * @param  string|array $id 附件id
     * @param  int $type 类型：0-补全目录，1-直接返回数据库记录的地址
     * @return string|array 路径
     */
    public function getFilePath($id = '', $type = 0)
    {
        $uploadPath = '';
        
        if ((strpos($id, ',') !== false) || is_array($id)) {
            if (!is_array($id)) {
                $ids = explode(',', $id);
            } else {
                $ids = $id;
            }
            
            $dataList = AttachmentModel::where([
                    ['id', 'in', $ids],
                ])
                ->field('path,driver,thumb')
                ->select()
                ->toArray();
            $paths = [];
            if (!empty($dataList)) {
                foreach ($dataList as $key => $value) {
                    if ($value['driver'] == 'local') {
                        $paths[$key] = ($type == 0 ? $uploadPath : '') . $value['path'];
                    } else {
                        $paths[$key] = $value['path'];
                    }
                }
            }
            
            return $paths;
        } else {
            $data = AttachmentModel::where([
                    ['id', '=', $id],
                ])
                ->field('path,driver,thumb')
                ->find();
            if (empty($data)) {
                return '';
            }
            
            if ($data['driver'] == 'local') {
                return ($type == 0 ? $uploadPath : '') . $data['path'];
            } else {
                return $data['path'];
            }
        }
    }

    /**
     * 根据附件id获取名称
     * @param  string $id 附件id
     * @return string     名称
     */
    public function getFileName($id = '')
    {
        return AttachmentModel::where('id', $id)->value('name');
    }

    /**
     * 根据附件id删除附件
     *
     * @create 2019-10-22
     * @author deatil
     */
    public function deleteFile($id)
    {
        $filePath = AttachmentModel::where('id', $id)->field('path,thumb')->find();
        if (!isset($filePath['path'])) {
            throw new \Exception("文件数据库记录已不存在~");
        }
        
        $realPath = realpath('.' . $filePath['path']);
        if (!is_file($realPath) || !unlink($realPath)) {
            throw new \Exception("删除" . $filePath['path'] . "失败");
        }
        
        $status = AttachmentModel::where('id', $id)->delete();
        if ($status === false) {
            throw new \Exception("删除" . $filePath['path'] . "失败");
        }
    }

}

