<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/26
 * Time: 14:37
 */

namespace app\index\controller;


use app\common\controller\Base;
use app\common\model\Task;

class Order extends Base
{
	/**
	 * 价格一览
	 * @return mixed
	 */
	public function price()
	{
		return $this->fetch();
	}

	/**
	 * 预约回收
	 * @return mixed
	 */
	public function recover()
	{
		return $this->fetch();
	}

	/**
	 * 新增回收单
	 * @return \think\response\Json
	 */
	public function add()
	{
		$post = input("post.");
		$day = $post['day'];
		unset($post['day']);
		$data = clearArray($post);//清除数组空键值对
		$today = date('Y-m-d');
		//$startToday = strtotime($today);//今天起始时间戳
		$data['recover_date'] = strtotime($today) + 86399 + (86400 * $day);
		$data['order_id'] = $this->get_task_id();
		$data['member_id'] = session('member_id');
		$data['create_time'] = time();
		$res = Task::getInstance()->insert($data);
		if ($res > 0) {
			return json(['code' => 1, 'data' => '', 'msg' => '呼叫小黄人成功！']);
		} else {
			return json(['code' => 0, 'data' => '', 'msg' => '呼叫小黄人失败，请稍后再试！']);
		}
	}

	/**
	 * 订单列表
	 * @return mixed|\think\response\Json
	 */
	public function orderList()
	{
		if (request()->isPost()) {
			$post = input("post.");
			$res = Task::getInstance()->getOrderList($post);
			if ($res > 0) {
				$sum = Task::getInstance()->getOrderSum($post);
				return json(['code' => 1, 'data' => $res, 'sum' => $sum, 'msg' => '成功']);
			} else {
				return json(['code' => 0, 'data' => '', 'sum' => 0, 'msg' => '失败']);
			}
		} else {
			return $this->fetch('orderList');
		}
	}

	/**
	 * 订单详情
	 * @return mixed|\think\response\Json
	 */
	public function orderInfo()
	{
		if (request()->isPost()) {
			$post = input("post.");
			$where['t.id'] = $post['task_id'];
			$where['t.member_id'] = session('member_id');
			$res = Task::getInstance()->getOrderInfo($where);
			if ($res > 0) {
				return json(['code' => 1, 'data' => $res, 'msg' => '成功']);
			} else {
				return json(['code' => 0, 'data' => '', 'msg' => '失败']);
			}
		} else {
			return $this->fetch();
		}
	}
}