<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/30
 * Time: 11:22
 */
namespace app\admin\model;
use app\common\model\Base;
use think\Db;
class Member extends Base
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
		if(session('role_id') != 1) {$where['aid'] = session('aid');}
		$res = Db::name('member')
			->where($where)
			->order('id desc')
			->select();
		return $res;
	}

	public function editMember($data)
	{
		$data['update_time'] = time();
		if ((isset($data['id']) && $data['id'] > 0)) {
			if (isset($data['password'])) $data['password'] = md5(md5($data['password']) . config('data_auth_key'));
			$res = $this->allowField(true)->where('id', $data['id'])->update($data);
		} else {
			$res = $this->allowField(true)->data($data)->save();
		}
		return $res;
	}
}