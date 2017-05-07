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

	/**
	 * 获取企业列表
	 * @param array $where
	 * @return false|\PDOStatement|string|\think\Collection
	 */
	public function getList($where = [])
	{
		$res = Db::name('admin_admin')
			->where($where)
			->order('id desc')
			->select();
		return $res;
	}

	/**
	 * 获取企业信息
	 * @param array $where
	 * @return array|false|\PDOStatement|string|\think\Model
	 */
	public function getInfo($where = [])
	{
		$res = Db::name('admin_admin')
			->where($where)
			->find();
		return $res;
	}

	/**
	 * 新增/编辑企业
	 * @param $data
	 * @return $this|false|int
	 */
	public function editAdmin($data)
	{
		if(isset($data['url']) && rename(ROOT_PATH . "public/images/temporary/picture/" . $data['url'], ROOT_PATH . "public/images/upload/picture/" . $data['url'])) {
			rename(ROOT_PATH . "public/images/temporary/thumb/" . $data['url'], ROOT_PATH . "public/images/upload/thumb/" . $data['url']);
			$data['url'] = '/images/upload/picture/' . $data['url'];
		}
		$data['update_time'] = time();
		if ((isset($data['id']) && $data['id'] > 0)) {
			$res = $this->allowField(true)->where('id', $data['id'])->update($data);
		} else {
			$data['create_time'] = $data['update_time'];
			$res = $this->allowField(true)->data($data)->save();
		}
		return $res;
	}
}