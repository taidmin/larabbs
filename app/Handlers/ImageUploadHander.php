<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/3/2
 * Time: 21:41
 */

namespace App\Handlers;


use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ImageUploadHander
{
    // 只允许以下后缀名的的图片文件上传
    protected $allowed_ext = ["png", "jpg", "gif", "jpeg"];

    public function save($file, $folder, $file_prefix, $max_width = false)
    {
        // 构建存储文件夹规则 值如：uploads/images/avatars/201709/21/
        // 文件夹切割能让查找效率更高
        $folder_name = "uploads/images/$folder/".date('Ym/d',time());

        // 文件具体存储的物理路径， public_path() 获取的是 public 文件夹的物理路径
        $upload_path = public_path().'/'.$folder_name;

        // 获取文件的后缀名，因图片从裁剪版里面黏贴时后缀名为空，所以此处确保后缀一直存在
        $extension = strtolower($file->getClientOriginalExtension()) ?: 'png';

        // 拼接文件名
        $filename = $file_prefix. '_' .time(). '_' . Str::random(10).'.'.$extension;

        // 如果上传的不是图片则终止操作
        if(! in_array($extension,$this->allowed_ext)){
            return false;
        }

        // 将图片移到我们的目标存储路径中
        $file->move($upload_path, $filename);

        // 如果限制的图片的宽度，就进行图片裁切
        if($max_width && $extension != 'gif'){
            // 图片裁切
            $this->reduceSize($upload_path.'/'.$filename, $max_width);
        }

        return [
            'path' => config('app.url')."/$folder_name/$filename",
        ];
    }

    public function reduceSize($file_path, $max_width)
    {
        // 先实例化，传参是图片的物理路径
        $image = Image::make($file_path);

        // 进行大小的调整
        $image->resize($max_width, null, function($constraint){
            // 设定宽度 为 $max_width,高度等比例缩放
            $constraint->aspectRatio();

            // 防止裁图时图片尺寸过大
            $constraint->upsize();
        });

        // 对图片修改后进行保存
        $image->save();
    }
}
