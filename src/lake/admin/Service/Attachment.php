<?php

namespace Lake\Admin\Service;

use think\Image;
use think\facade\Filesystem;

use Lake\Admin\Model\Attachment as AttachmentModel;

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
            'driver' => AttachmentModel::getFilesystemDefaultDisk(),
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
                $filename = app()->getRuntimePath() . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . md5($vo) . $fileExt;
                if (lake_http_down($vo, $filename) !== false) {
                    $file_info['md5'] = hash_file('md5', $filename);
                    if ($file_exists = AttachmentModel::get(['md5' => $file_info['md5']])) {
                        unlink($filename);
                        $localpath = AttachmentModel::objectUrl($file_exists['path']);
                    } else {
                        $file_info['sha1'] = hash_file('sha1', $filename);
                        $file_info['size'] = filesize($filename);
                        $file_info['mime'] = mime_content_type($filename);
                        $file_info['name'] = $vo;
                        $file_info['ext'] = ltrim($fileExt, ".");

                        $fpath = 'images' . DIRECTORY_SEPARATOR . date('Ymd');
                        $savePath = $fpath;
                        if (!is_dir($savePath)) {
                            mkdir($savePath, 0755, true);
                        }
                        $fname = DIRECTORY_SEPARATOR . md5(microtime(true)) . $fileExt;
                        
                        $file_path = str_replace(DIRECTORY_SEPARATOR, '/', $fpath . $fname);
                        
                        $new_path = AttachmentModel::putStream($file_path, $filename);
                        
                        if ($new_path) {
                            $file_info['path'] = $new_path;
                            
                            AttachmentModel::create($file_info);
                            unlink($filename);
                            
                            $localpath = $file_info['path'];
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
     * @param string $watermarkImg 水印图片实际地址
     * @param string $watermarkPos 水印位置
     * @param string $watermarkAlpha 水印透明度
     */
    public function createWater(
        $file = '', 
        $watermarkImg = '', 
        $watermarkPos = '', 
        $watermarkAlpha = ''
    ) {
        if (is_file($watermarkImg)) {
            // 读取图片
            $image = Image::open($file);
            // 添加水印
            $watermarkPos = $watermarkPos == '' ? config('app.upload_thumb_water_position') : $watermarkPos;
            $watermarkAlpha = $watermarkAlpha == '' ? config('app.upload_thumb_water_alpha') : $watermarkAlpha;
            $image->water($watermarkImg, $watermarkPos, $watermarkAlpha);
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
                    $paths[$key] = ($type == 0 ? $value['uri'] : $value['path']);
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
            
            if ($type == 0) {
                return $data['uri'];
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
        $filePath = AttachmentModel::where('id', $id)
            ->field('path,thumb')
            ->find();
        if (! isset($filePath['path'])) {
            throw new \Exception("文件数据库记录已不存在~");
        }
        
        try {
            AttachmentModel::filesystem()->delete($filePath['path']);
        } catch(\Exception $e) {
        }
        
        $status = AttachmentModel::where('id', $id)->delete();
        if ($status === false) {
            throw new \Exception("删除" . $filePath['path'] . "失败");
        }
    }

}

