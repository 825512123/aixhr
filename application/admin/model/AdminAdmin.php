<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/2
 * Time: 16:05
 */

namespace app\admin\model;


use app\common\model\Base;
use think\Db;
class AdminAdmin extends Base
{
	private static $_instance;

	public static function getInstance()
	{
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function getList($where = [])
	{
		$res = Db::name('admin_admin')
			->where($where)
			->order('id desc')
			->select();
		return $res;
	}

	public function getInfo($where = [])
	{
		$res = Db::name('admin_admin')
			->where($where)
			->find();
		return $res;
	}

	public function addData($data)
	{
		$data = clearArray($data);//清除数组空键值对
		if(rename(ROOT_PATH."public/images/temporary/picture/".$data['url'], ROOT_PATH."public/images/upload/picture/".$data['url'])) {
			rename(ROOT_PATH."public/images/temporary/thumb/".$data['url'], ROOT_PATH."public/images/upload/thumb/".$data['url']);
			$data['url'] = '/images/upload/picture/'.$data['url'];
		}
		$data['create_time'] = time();
		return Db::name('admin_admin')->insert($data);
	}
}