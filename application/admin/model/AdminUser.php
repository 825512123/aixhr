<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/2
 * Time: 16:14
 */

namespace app\admin\model;

use app\common\model\Base;
use think\Db;
class AdminUser extends Base
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
		$res = Db::name('admin_user')->alias('au')
			->join('admin_admin aa', 'au.aid=aa.id')
			->join('admin_role ar', 'au.role_id=ar.id')
			->where($where)
			->field('au.*,aa.name as adminName,ar.rolename')
			->order('id desc,au.status desc')
			->select();
		return $res;
	}

	public function getInfo($where = [])
	{
		return Db::name('admin_user')->where($where)->find();
	}

	public function editUser($data)
	{
		$data = clearArray($data);//清除数组空键值对
		$data['update_time'] = time();
		if (isset($data['id'])) {
			if (isset($data['password'])) $data['password'] = md5(md5($data['password']) . config('data_auth_key'));
			$res = $this->allowField(true)->where('id', $data['id'])->update($data);
		} else {
			$res = $this->allowField(true)->data($data)->save();
		}
		return $res;
	}
}