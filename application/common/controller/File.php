<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-12-21 0021
 * Time: 18:01:47
 */

namespace app\common\controller;


use think\Image;

class File
{
    /**
     * 检验并创建所需路径（创建传入路径并创建临时文件夹路径）
     * @param $dir
     * @return array 返回临时图片原图（无时间文件夹） 和 压缩路径（含时间文件夹）
     */
    public function checkPath($dir)
    {
        $date_time = date('Ymd');
        if (!file_exists($dir."../")) { mkdir($dir, 0775); }
        // 临时文件夹
        if (!file_exists($dir."../temporary/")) { mkdir($dir."../temporary/", 0775); }
        // 临时原图文件夹
        if (!file_exists($dir . "../temporary/picture/")) { mkdir($dir . "../temporary/picture/", 0775); }
        $temporaryPictureDir = $dir . "../temporary/picture/";
        if (!file_exists($temporaryPictureDir)) { mkdir($temporaryPictureDir, 0775); }
        // 临时缩略图文件夹
        if (!file_exists($dir . "../temporary/thumb/")) { mkdir($dir . "../temporary/thumb/", 0775); }
        $temporaryThumbDir = $dir . "../temporary/thumb/" . $date_time . '/';
        if (!file_exists($temporaryThumbDir)) { mkdir($temporaryThumbDir, 0775); }
        // 永久文件夹
        if (!file_exists($dir)) { mkdir($dir, 0775); }
        if (!file_exists($dir . "picture/")) { mkdir($dir . "picture/", 0775); }
        $imagePictureDir = $dir."picture/" . $date_time . '/';
        if (!file_exists($imagePictureDir)) { mkdir($imagePictureDir, 0775); }
        if (!file_exists($dir . "thumb/")) { mkdir($dir . "thumb/", 0775); }
        $imageDir = $dir."thumb/" . $date_time . "/";
        if (!file_exists($imageDir)) { mkdir($imageDir, 0775); }

        return ['picture_dir' => $temporaryPictureDir, 'thumb_dir' => $temporaryThumbDir];
    }

    /**
     * 图片上传
     * @param $dir
     * @return array
     */
    public function upload($dir)
    {
        $path = $this->checkPath($dir);// 文件路径处理
        // 图片保存至临时文件夹的原图与压缩图路径
        if (request()->isPost()) {
            $file = request()->file('file');
            if(!$file->checkSize(5*1024*1024)) { return ['code' => -1, 'msg' => '文件大小超过了最大值!', 'data' => '']; }
            $info = $file->move($path['picture_dir']);
            if($info){
                $image = Image::open($path['picture_dir'].$info->getSaveName());
                $image->thumb(110,110,Image::THUMB_SCALING)->save($path['thumb_dir'].$info->getFilename());
                // $info->getExtension() 输出 jpg;
                // $info->getSaveName() 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg;
                // $info->getFilename() 输出 42a79759f284b767dfcb2a0197904287.jpg;'/images/temporary/thumb/'.
                $saveName = str_replace('\\', '/',$info->getSaveName());
                return ['code' => 1, 'msg' => '上传成功', 'data' => $saveName];
            }else{
                // 上传失败获取错误信息
                return ['code' => -1, 'msg' => $file->getError(), 'data' => ''];
            }
        }
        return ['code' => -1, 'msg' => '上传失败!~', 'data' => ''];
    }

    public function imagePath($str)
    {
        $paths = explode(',', $str);
        return clearArray($paths);
    }

    /**
     * 获取图片路径（无后缀名）
     * @param $dir
     * @return string
     */
    public function getImagePath($dir)
    {
        $date_time = date('Ymd');
        if (!file_exists($dir)) {
            mkdir($dir, 0775);
        }
        if (!file_exists($dir . "upload/")) {
            mkdir($dir . "upload/", 0775);
        }
        // 原图文件夹
        if (!file_exists($dir . "upload/picture/")) {
            mkdir($dir . "upload/picture/", 0775);
        }
        if (!file_exists($dir . "upload/picture/" . $date_time . "/")) {
            mkdir($dir . "upload/picture/" . $date_time . '/', 0775);
        }
        // 缩略图文件夹
        if (!file_exists($dir . "upload/thumb/")) {
            mkdir($dir . "upload/thumb/", 0775);
        }
        if (!file_exists($dir . "upload/thumb/" . $date_time . "/")) {
            mkdir($dir . "upload/thumb/" . $date_time . '/', 0775);
        }
        $time = time();
        $rand = rand(1, 100000000);
        return $dir . "upload/picture/" . $date_time . '/' . $time . $rand;
    }

    /**
     * 缩略图相片
     * @param $data
     * @return array
     */
    public function thumbPhoto($data)
    {
        //$image = new Image();
        $folder = __DIR__ . "/../..";
        $url = "/images/upload/thumb/" . $data['path'];
        $fileInfo = getimagesize($folder . "/images/upload/picture/" . $data['path']);
        //$thumbInfo = $this->thumbRation($fileInfo[0], $fileInfo[1]);//缩略图尺寸
        //$thumbInfo = $this->pictureRatio($fileInfo[0], $fileInfo[1], 267, 200);//缩略图尺寸
        //$image->open($folder . "/images/upload/picture/" . $data['path']);
        //另存固定宽度是200的压缩图片
        //$image->thumb($thumbInfo[0], $thumbInfo[1], $image::IMAGE_THUMB_FIXED)->save($folder . $url);
        //$image->thumb(200, 200, $image::IMAGE_THUMB_CENTER)->save($folder . $url);// 居中裁剪
        $picInfo = $this->pictureRatio($fileInfo[0], $fileInfo[1]);//原图缩略尺寸
        $pic = new Image();
        $pic->open($folder . "/images/upload/picture/" . $data['path']);
        $pic->thumb($picInfo[0], $picInfo[1], $pic::IMAGE_THUMB_FIXED)->save($folder . "/images/upload/picture/" . $data['path']);//原图压缩
//        $pic->water(__DIR__."/../../wechat/web/images/logo_water.png")->save($folder . "/images/upload/picture/" . $data['path']);
        $pic->thumb(200, 200, $pic::IMAGE_THUMB_FIXED)->save($folder . $url);// 居中裁剪
//        $pic->water(__DIR__."/../../wechat/web/images/logo_water.png")->save($folder . $url);
        return ['status' => 1, 'info' => '上传成功', 'pic_path' => "/images/upload/picture/" . $data['path'], 'thumb_path' => $url, 'time' => $data['time']];
    }

    /**
     * 后台压缩图片处理
     * @param $tempFile 原文件
     * @param $extension 后缀名
     */
    public function backThumbImage($tempFile, $extension)
    {
        $imagePath = $this->getImagePath(__DIR__."/../../images/");
        $targetFile = $imagePath . '.' . $extension;
        // 上传图片
        if(move_uploaded_file($tempFile, $targetFile)) {
            $fileInfo = getimagesize($targetFile);

            // 重命名加上图片长宽
            $picInfo = $this->pictureRatio($fileInfo[0], $fileInfo[1]); //  等比例
            $picturePath = $imagePath . '_' . $picInfo[0] . '_' . $picInfo[1] . '.' . $extension;
            if (rename($targetFile, $picturePath)) {
                //$thumbInfo = $this->thumbRation($fileInfo[0], $fileInfo[1]);//缩略图尺寸
                //$thumbInfo = $this->pictureRatio($fileInfo[0], $fileInfo[1], 267, 200);//缩略图尺寸
                // 固定压缩图片长宽200
                //$image = new Image();
                $thumbPath = str_replace('picture', 'thumb', $picturePath);
                //$image->open($picturePath);
                //$image->thumb($thumbInfo[0], $thumbInfo[1], $image::IMAGE_THUMB_FIXED)->save($thumbPath);
                //$image->thumb(200, 200, $image::IMAGE_THUMB_CENTER)->save($thumbPath);// 居中裁剪
                $pic = new Image();
                $pic->open($picturePath);
                $pic->thumb($picInfo[0], $picInfo[1], $pic::IMAGE_THUMB_FIXED)->save($picturePath);//原图压缩
//                $pic->water(__DIR__."/../../wechat/web/images/logo_water.png")->save($picturePath);
                $pic->thumb(200, 200, $pic::IMAGE_THUMB_FIXED)->save($thumbPath);// 居中裁剪
//                $pic->water(__DIR__."/../../wechat/web/images/logo_water.png")->save($thumbPath);

            }
        }
        $thumbArr = explode('/../..', $thumbPath);
        echo $thumbArr[1];// 返回压缩图片路径
    }

    /**
     * 返回原图等比例压缩宽高（最大不超过）
     * @param $pic_width
     * @param $pic_height
     * @param int $maxwidth 480
     * @param int $maxheight 720
     * @return mixed
     */
    public function pictureRatio($pic_width, $pic_height, $maxwidth = 480, $maxheight = 720)
    {
        $ratio            = 1;
        $resizewidth_tag  = false;
        $resizeheight_tag = false;
        if($pic_width < $maxwidth && $pic_height < $maxheight) {
            $pic[0] = $pic_width;
            $pic[1] = $pic_height;
            if($pic_height > $pic_width) {
                $pic[2] = $pic[0];
            } else {
                $pic[2] = $pic[1];
            }
            return $pic;
        }
        if($maxwidth && $pic_width > $maxwidth) {
            $widthratio = $maxwidth/$pic_width;
            $resizewidth_tag = true;
        }
        if($maxheight && $pic_height > $maxheight) {
            $heightratio = $maxheight/$pic_height;
            $resizeheight_tag = true;
        }

        if($resizewidth_tag && $resizeheight_tag) {
            if($widthratio<$heightratio) {
                $ratio = $widthratio;
            } else {
                $ratio = $heightratio;
            }
        }

        if($resizewidth_tag && !$resizeheight_tag) {
            $ratio = $widthratio;
        }
        if(!$resizewidth_tag && $resizeheight_tag) {
            $ratio = $heightratio;
        }

        $pic[0] = floor($ratio*$pic_width);
        $pic[1] = floor($ratio*$pic_height);
        if($pic_height > $pic_width) {
            $pic[2] = $pic[0];
        } else {
            $pic[2] = $pic[1];
        }
        return $pic;
    }

    /**
     * 缩略图比例计算（最小不小于）
     * @param $pic_width
     * @param $pic_height
     * @return mixed
     */
    public function thumbRation($pic_width, $pic_height)
    {
        $maxwidth = 210;
        $maxheight = 210;
        $resizewidth_tag = false;
        $resizeheight_tag = false;
        if($pic_width < $maxwidth || $pic_height < $maxheight) {
            $pic[0] = $pic_width;
            $pic[1] = $pic_height;
            return $pic;
        }
        if($maxwidth && $pic_width > $maxwidth) {
            $widthratio = $maxwidth/$pic_width;
            $resizewidth_tag = true;
        }
        if($maxheight && $pic_height > $maxheight) {
            $heightratio = $maxheight/$pic_height;
            $resizeheight_tag = true;
        }

        if($resizewidth_tag && $resizeheight_tag) {
            if($widthratio<$heightratio) {
                $ratio = $heightratio;
            } else {
                $ratio = $widthratio;
            }
        }

        $pic[0] = floor($ratio*$pic_width);
        $pic[1] = floor($ratio*$pic_height);
        return $pic;
    }
}