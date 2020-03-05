<?php

namespace app\admin\model;

use think\Model;

use think\Image;

/**
 * 附件模型
 *
 * @create 2019-8-5
 * @author deatil
 */
class Attachment extends Model
{
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $insert = ['status' => 1];

    public function getSizeAttr($value)
    {
        return format_bytes($value);
    }

    /**
     * 创建缩略图
     * @param string $file 目标文件，可以是文件对象或文件路径
     * @param string $dir 保存目录，即目标文件所在的目录名
     * @param string $save_name 缩略图名
     * @param string $thumb_size 尺寸
     * @param string $thumb_type 裁剪类型
     * @return string 缩略图路径
     */
    public function createThumb($file = '', $filename = '', $save_name = '', $thumb_size = '', $thumb_type = '')
    {
        // 获取要生成的缩略图最大宽度和高度
        $thumb_size = $thumb_size == '' ? config('upload_image_thumb') : $thumb_size;
        list($thumb_max_width, $thumb_max_height) = explode(',', $thumb_size);
        // 读取图片
        $image = Image::open($file);
        // 生成缩略图
        $thumb_type = $thumb_type == '' ? config('upload_image_thumb_type') : $thumb_type;
        $image->thumb($thumb_max_width, $thumb_max_height, $thumb_type);

        if (!is_dir($filename)) {
            mkdir($filename, 0766, true);
        }
        $image->save($filename . $save_name);
        return $filename;
    }

    /**
     * 添加水印
     * @param string $file 要添加水印的文件路径
     * @param string $watermark_img 水印图片id
     * @param string $watermark_pos 水印位置
     * @param string $watermark_alpha 水印透明度
     */
    public function createWater($file = '', $watermark_img = '', $watermark_pos = '', $watermark_alpha = '')
    {
        $uploadPath = config('upload_path');
        $path = $this->getFilePath($watermark_img, 1);
        $thumb_water_pic = realpath($uploadPath . '/' . $path);
        if (is_file($thumb_water_pic)) {
            // 读取图片
            $image = Image::open($file);
            // 添加水印
            $watermark_pos = $watermark_pos == '' ? config('upload_thumb_water_position')['key'] : $watermark_pos;
            $watermark_alpha = $watermark_alpha == '' ? config('upload_thumb_water_alpha') : $watermark_alpha;
            $image->water($thumb_water_pic, $watermark_pos, $watermark_alpha);
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
        $uploadPath = config('public_url') . 'uploads/';
		
        if ((strpos($id, ',') !== false) || is_array($id)) {
            if (!is_array($id)) {
				$ids = explode(',', $id);
			} else {
				$ids = $id;
			}
			
            $data_list = $this->where([
					['id', 'in', $ids],
				])
				->field('path,driver,thumb')
				->select();
            $paths = [];
			if (!empty($data_list)) {
				foreach ($data_list as $key => $value) {
					if ($value['driver'] == 'local') {
						$paths[$key] = ($type == 0 ? $uploadPath : '') . $value['path'];
					} else {
						$paths[$key] = $value['path'];
					}
				}
			}
			
            return $paths;
        } else {
            $data = $this->where([
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
        return $this->where('id', $id)->value('name');
    }

    /**
     * 根据附件id删除附件
	 *
	 * @create 2019-10-22
	 * @author deatil
     */
    public function deleteFile($id)
    {
        $path = config('upload_path');

        $file_path = self::where('id', $id)->field('path,thumb')->find();
        if (!isset($file_path['path'])) {
            throw new \Exception("文件数据库记录已不存在~");
        }
		
		$real_path = realpath($path . '/' . $file_path['path']);
		if (!is_file($real_path) || !unlink($real_path)) {
			throw new \Exception("删除" . $real_path . "失败");
		}
		
		$status = self::where('id', $id)->delete();
		if ($status === false) {
			throw new \Exception("删除" . $real_path . "失败");
		}
    }

}

