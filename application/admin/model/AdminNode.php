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

class AdminNode extends Base
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
	 * 获取目录
	 * @return false|\PDOStatement|string|\think\Collection
	 */
	public function getMenu()
	{
		$nodeStr = session('menu');//超级管理员没有节点数组
		$where = empty($nodeStr) ? 'is_menu = 1' : 'is_menu = 1 and id in('.$nodeStr.')';
		$res = Db::name('admin_node')
			->where($where)
			->where('status = 1')
			->order('sort')
			->select();

        return $res;
	}

	/**
	 * 获取菜单信息
	 * @param $where
	 * @return array|false|\PDOStatement|string|\think\Model
	 */
	public function getInfo($where)
	{
		return Db::name('admin_node')->where($where)->find();
	}

	/**
	 * 获取菜单列表
	 * @param array $where
	 * @return false|\PDOStatement|string|\think\Collection
	 */
	public function getList($where = [])
	{
		$res = Db::name('admin_node')
			->where($where)
			->where('status = 1')
			->order('sort')
			->select();
		return $res;
	}

	/**
	 * 编辑/新增节点
	 * @param $data
	 * @return $this|false|int
	 */
	public function editNode($data)
	{
		$data['update_time'] = time();
		if (isset($data['id'])) {
			$res = $this->allowField(true)->where('id', $data['id'])->update($data);
		} else {
			$data['create_time'] = $data['update_time'];
			$res = $this->allowField(true)->data($data)->save();
		}
		return $res;
	}

	/**
	 * 获取节点数据
	 * @param $id
	 * @return string
	 */
	public function getNodeInfo($rule)
	{
		$result = $this->field('id,node_name,pid')->select();
		dump($result);exit;
		$str = "";

		if(!empty($rule)){
			$rule = explode(',', $rule);
		}
		foreach($result as $key=>$vo){
			$str .= '{ "id": "' . $vo['id'] . '", "pId":"' . $vo['pid'] . '", "name":"' . $vo['node_name'].'"';

			if(!empty($rule) && in_array($vo['id'], $rule)){
				$str .= ' ,"checked":1';
			}

			$str .= '},';

		}

		return "[" . substr($str, 0, -1) . "]";
	}
}