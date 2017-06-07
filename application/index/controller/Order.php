<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/26
 * Time: 14:37
 */

namespace app\index\controller;


use app\admin\controller\Login;
use app\admin\model\AdminUser;
use app\common\controller\Base;
use app\common\model\Member;
use app\common\model\RecoverPrice;
use app\common\model\Task;
use think\Db;

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
		if(session('aid') > 0) {
			return $this->fetch('userOrderList');
		} else {
			return $this->fetch();
		}
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
	 * 客户订单列表
	 * @return mixed|\think\response\Json
	 */
	public function orderList()
	{
		if(session('aid') > 0) {
			return $this->fetch('userOrderList');
		} elseif (request()->isPost()) {
			$post = input("post.");
            $where['t.member_id'] = session('member_id');
            if ($post['status'] == 2) {
                $where['t.status'] = 2;
                $where['t.recover_date'] = ['gt', time()];
            } else {
                $where['t.status'] = 1;
            }
			$res = Task::getInstance()->getOrderList($where, $post['limit']);
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
	 * 员工订单列表
	 * @return mixed|\think\response\Json
	 */
	public function userOrderList()
	{
        if(session('aid') <= 0) {
            return $this->fetch('orderList');
        } elseif (request()->isPost()) {
			$post = input("post.");
			$res = Task::getInstance()->getUserOrderList($post);
			if ($res) {
				$sum = Task::getInstance()->getUserOrderSum($post);
				return json(['code' => 1, 'data' => $res, 'sum' => $sum, 'msg' => '成功']);
			} else {
				return json(['code' => 0, 'data' => '', 'sum' => 0, 'msg' => '失败']);
			}
		} else {
			return $this->fetch('userOrderList');
		}
	}

    /**
     * 今日订单
     * @return mixed|\think\response\Json
     */
    public function dayOrder()
    {
        $taskModel = Task::getInstance();
        if (request()->isPost()) {
            $post = input("post.");
            //$post['day'] = '2017-05-31';
            $today = $post['day'] ? $post['day'] : date('Y-m-d');
            $startToday = strtotime($today);//今天起始时间戳
            $endToday = $startToday + 86399;//今天结束时间戳86399
            if ($post['status'] == 2) {
                $data['t.status'] = 2;
                $where['status'] = 2;
            } else {
                $data['t.status'] = 1;
                $where['status'] = 1;
            }
            $data['t.recover_date'] = ['between', [$startToday, $endToday]];
            $res = $taskModel->getDayOrderList($data);
            if ($res > 0) {
                $where['recover_date'] = ['between', [$startToday, $endToday]];
                $sum = $taskModel->getDayOrderSum($where);
                $count = $this->getDayCount($where);
                return json(['code' => 1, 'data' => $res, 'sum' => $sum, 'count'=> $count, 'msg' => '成功']);
            } else {
                return json(['code' => 0, 'data' => '', 'sum' => 0, 'count'=> '','msg' => '失败']);
            }
        } else {
            $today = date('Y-m-d');
            $startToday = strtotime($today);//今天起始时间戳
            $endToday = $startToday + 86399;//今天结束时间戳86399
            $where['recover_date'] = ['between', [$startToday, $endToday]];
            $count = $this->getDayCount($where);
            $this->assign('count', $count);
            $this->assign('title', $today);
            return $this->fetch('dayOrder');
        }
    }

    private function getDayCount($where = [])
    {
        $where['status'] = 1;
        $data = Task::getInstance()->where($where)->field('id,task_money,number')->select();
        $count = ['count' => count($data), 'sum' => 0, 'number' => 0, 'price' => 0];
        if ($data) {
            $sum = 0;
            foreach ($data as $v) {
                $sum += ($v['task_money']*100);
                $count['number'] += $v['number'];
            }
            $count['price'] = ($sum/$count['number'])/100;
            $count['sum'] = $sum/100;
        }
        return $count;
    }

	/**
	 * 订单详情
	 * @return mixed|\think\response\Json
	 */
	public function orderInfo()
	{
        if(session('aid') > 0) {
            return $this->fetch('userOrderInfo');
        } elseif (request()->isPost()) {
			$post = input("post.");
			$where['t.id'] = $post['task_id'];
			//$where['t.member_id'] = session('member_id');
			$res = Task::getInstance()->getOrderInfo($where);
			if ($res) {
                return json(['code' => 1, 'data' => $res, 'price' => [], 'memberPrice' => [], 'msg' => '成功']);
			} else {
				return json(['code' => 0, 'data' => '', 'msg' => '失败']);
			}
		} else {
			return $this->fetch('orderInfo');
		}
	}

    /**
     * 员工订单详情
     * @return mixed|\think\response\Json
     */
	public function userOrderInfo()
    {
        if(session('aid') <= 0) {
            return $this->fetch('orderInfo');
        } elseif (request()->isPost()) {
            $post = input("post.");
            $where['t.id'] = $post['task_id'];
            $res = Task::getInstance()->getOrderInfo($where);
            if ($res) {
                if($res['task_status'] == 2) {
                    $price = $this->getMemberPice($res['member_id']);
                    return json(['code' => 1, 'data' => $res, 'price' => $price, 'msg' => '成功']);
                } else {
                    return json(['code' => 1, 'data' => $res, 'price' => [], 'msg' => '成功']);
                }
            } else {
                return json(['code' => 0, 'data' => '', 'msg' => '失败']);
            }
        } else {
            return $this->fetch('userOrderInfo');
        }
    }

    /**
     * 获取用户价格
     * @param $id
     * @return false|\PDOStatement|string|\think\Collection|\think\response\Json
     */
    public function getMemberPice($id)
    {
        $memberPrice = RecoverPrice::getInstance()->getMemberPriceList(['member_id' => $id]);
        $price = RecoverPrice::getInstance()->getList();
        if($memberPrice) {
            foreach ($price as $key => $val) {
                foreach ($memberPrice as $k=>$v) {
                    if($val['id'] == $v['id']) {
                        $price[$key]['price'] = $v['price'];
                    }
                }
            }
        }
        if (request()->isPost()) {
            return json(['code' => 1, 'data' => $price, 'msg' => '成功']);
        } else {
            return $price;
        }
    }

    /**
     * 提交订单数据
     * @return \think\response\Json
     */
    public function editOrder()
    {
        $post = input("post.");
        $sum = (($post['task_price']*100) * ($post['number']*100))/10000;
        $sum = round($sum, 2);
        if($sum == $post['task_money']) {
            if(Task::getInstance()->editTask($post)) {
                $data = Task::getInstance()->getOrderInfo(['t.id' => $post['id']]);
                return json(['code' => 1, 'data' => $data, 'msg' => '提交成功!等待用户确认!']);
            } else {
                return json(['code' => 0, 'data' => '', 'msg' => '失败!请稍后再试!']);
            }
        } else {
            return json(['code' => 2, 'data' => '', 'msg' => '价格有误,请重新提交!']);
        }
    }

    /**
     * 用户确认订单
     * @return \think\response\Json
     */
    public function confirmOrder()
    {
        $post = input("post.");
        $post['status'] = 1;
        $post['end_time'] = time();
        if (Task::getInstance()->confirmOrder($post)) {
            return json(['code' => 1, 'data' => $post, 'msg' => '提交成功!']);
        } else {
            return json(['code' => 0, 'data' => '', 'msg' => '失败!请稍后再试!']);
        }
    }

    /**
     * 员工新增订单
     * @return mixed|\think\response\Json
     */
    public function addOrder()
    {
        $this->checkRole();
        if(request()->isPost()) {
            $post = input("post.");
            $post['aid'] = session('aid');
            $post['status'] = 1;
            $post['user_id'] = session('user_id');
            $post['end_time'] = time();
            $post['recover_date'] = $post['end_time'];
            $post['create_time'] = $post['end_time'];
            $sum = (($post['task_price']*100) * ($post['number']*100))/10000;
            $sum = round($sum, 2);
            if($post['task_money'] == $sum) {
                $member = Member::getInstance()->getMember($post['member_id']);
                $post['order_id'] = $this->get_task_id();
                $post['task_city'] = $member['address_city'];
                $post['task_address'] = $member['address_input'];
                if (Task::getInstance()->addOrder($post)){
                    $this->refreshSessionUser($post['user_id']);
                    $user = json_encode(session('user_info'));
                    return json(['code' => 1, 'data' => $user, 'msg' => '新增订单完成!']);
                } else {
                    return json(['code' => 0, 'data' => '', 'msg' => '失败!请稍后再试!']);
                }
            }
        } else {
            $memberList = Member::getInstance()->getMemberList();
            $this->assign('memberList', $memberList);
            return $this->fetch('addOrder');
        }
    }

    /**
     * 公司订单
     * @return mixed|\think\response\Json
     */
    public function adminOrder()
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
            return $this->fetch('adminOrder');
        }
    }
}