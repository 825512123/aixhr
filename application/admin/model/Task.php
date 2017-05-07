<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/29
 * Time: 8:51
 */

namespace app\admin\model;


use app\common\model\Base;
use think\Db;

class Task extends Base
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
	 * 获取订单列表
	 * @param array $where
	 * @return false|\PDOStatement|string|\think\Collection
	 */
	public function getList($where = [])
	{
		if(session('role_id') != 1) {$where['t.aid'] = session('aid');}
		$res = Db::name('task')->alias('t')
			->join('admin_user au', 'au.id=t.user_id', 'LEFT')
			->join('member m', 'm.id=t.member_id')
			->where($where)
			->field('t.*,m.username as member_name,m.mobile as member_mobile,au.name as user_name,au.mobile as user_mobile')
			->order('id desc')
			->select();
		return $res;
	}

	/**
	 * 编辑订单
	 * @param array $data
	 * @return $this
	 */
	public function editTask($data = [])
	{
		return $this->allowField(true)->where('id', $data['id'])->update($data);
	}

	/**
	 * 获取单条订单信息
	 * @param array $where
	 * @return array|false|\PDOStatement|string|\think\Model
	 */
	public function getTask($where = [])
	{
		$res = Db::name('task')->alias('t')
			->join('admin_user au', 'au.id=t.user_id', 'LEFT')
			->join('member m', 'm.id=t.member_id')
			->where($where)
			->field('t.*,m.username as member_name,m.mobile as member_mobile,au.name as user_name,au.mobile as user_mobile')
			->find();
		return $res;
	}

	public function getFind($where = [])
	{
		return $this->where($where)->find();
	}
}