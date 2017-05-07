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

	/**
	 * 获取员工信息列表
	 * @param array $where
	 * @return false|\PDOStatement|string|\think\Collection
	 */
	public function getList($where = [])
	{
		if(session('role_id') != 1) {$where['au.aid'] = session('aid');}
		$res = Db::name('admin_user')->alias('au')
			->join('admin_admin aa', 'au.aid=aa.id')
			->join('admin_role ar', 'au.role_id=ar.id')
			->where($where)
			->field('au.*,aa.name as adminName,ar.rolename')
			->order('id desc,au.status desc')
			->select();
		return $res;
	}

	/**
	 * 获取当前角色员工
	 * @param array $where
	 * @return false|\PDOStatement|string|\think\Collection
	 */
	public function getRoleList($where = [])
	{
		$where['au.aid'] = session('aid');
		$res = Db::name('admin_user')->alias('au')
			->join('admin_admin aa', 'au.aid=aa.id')
			->where($where)
			->field('au.*,aa.name adminName')
			->order('au.id desc')
			->select();
		return $res;
	}

	/**
	 * 获取单条员工信息
	 * @param array $where
	 * @return array|false|\PDOStatement|string|\think\Model
	 */
	public function getInfo($where = [])
	{
		$res = Db::name('admin_user')->alias('au')
			->join('admin_admin aa', 'au.aid=aa.id')
			->join('admin_role ar', 'au.role_id=ar.id')
			->where($where)
			->field('au.*,ar.rolename,ar.rule,aa.name adminName')
			->find();
		return $res;
	}

	/**
	 * 获取基本信息
	 * @param array $where
	 * @return array|false|\PDOStatement|string|\think\Model
	 */
	public function getFind($where = [])
	{
		return $this->where($where)->find();
	}

	public function editUser($data)
	{
		$data['update_time'] = time();
		if ((isset($data['id']) && $data['id'] > 0)) {
			$res = $this->allowField(true)->where('id', $data['id'])->update($data);
		} else {
			$res = $this->allowField(true)->data($data)->save();
		}
		return $res;
	}
}