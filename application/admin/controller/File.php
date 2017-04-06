<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/5
 * Time: 12:04
 */

namespace app\admin\controller;


class File extends Base
{
	/**
	 * 上传并压缩临时图片
	 * @return array
	 */
	public function uploadThumb()
	{
		$file = new \app\common\controller\File();
		$res = $file->upload(ROOT_PATH."public/images/upload/");
		return json($res);
	}

	/**
	 * 上传并压缩永久图片
	 * @return array
	 */
	public function photoThumb()
	{
		$file = new \app\common\controller\File();
		$res = $file->upload(ROOT_PATH."public/images/photo/");
		return json($res);
	}

	/** 删除临时文件
	 * @return \think\response\Json
	 */
	public function deleteTemporary()
	{
		if (request()->isPost()) {
			$file = new \app\common\controller\File();
			$images = $file->imagePath(input("param.image_path"));
			foreach ($images as $key => $val) {
				if(file_exists(ROOT_PATH."public/images/temporary/picture/".$val)) {
					unlink(ROOT_PATH."public/images/temporary/picture/".$val);
				}
				if(file_exists(ROOT_PATH."public/images/temporary/thumb/".$val)) {
					unlink(ROOT_PATH."public/images/temporary/thumb/".$val);
				}
			}
			return json(['code' => 1, 'msg' => '删除成功', 'data' => '']);
		}
		return json(['code' => -1, 'msg' => '未传值', 'data' => '']);
	}
}