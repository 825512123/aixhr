<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/17
 * Time: 12:40
 */

namespace app\admin\model;


use app\common\model\Base;
use think\Db;

class AdminRole extends Base
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
	 * 获取角色列表
	 * @return false|\PDOStatement|string|\think\Collection
	 */
	public function getList($where = [])
	{
		$res = Db::name('admin_role')
			->where($where)
			->order('id desc')
			->select();
		return $res;
	}

	/**
	 * 获取单条信息
	 * @param array $where
	 * @return array|false|\PDOStatement|string|\think\Model
	 */
	public function getInfo($where = [])
	{
		return Db::name('admin_role')->where($where)->find();
	}

	/**
	 * 编辑/新增角色
	 * @param $data
	 * @return $this|false|int
	 */
	public function editRole($data)
	{
		$data['update_time'] = time();
		if (isset($data['id']) && $data['id'] > 0) {
			$res = $this->allowField(true)->where('id', $data['id'])->update($data);
		} else {
			$data['create_time'] = $data['update_time'];
			$res = $this->allowField(true)->data($data)->save();
		}
		return $res;
	}
}