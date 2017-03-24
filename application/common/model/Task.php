<?php
/**
 * 订单/任务表
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/3
 * Time: 18:19
 */

namespace app\common\model;


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
	 * 新增任务/订单
	 * @param $data
	 * @return int|string
	 */
    public function addTask($data)
    {
        $data = clearArray($data);//清除数组空键值对
        $data['update_time'] = time();
        return Db::name('order')->insert($data);
    }

	/**
	 * 获取订单/任务列表
	 * @param $where
	 * @param $limit
	 * @return false|\PDOStatement|string|\think\Collection
	 */
    public function getOrderList($data)
    {
	    $where['t.member_id'] = session('member_id');
	    if($data['status'] == 2) {
		    $where['t.status'] = 2;
		    $where['t.recover_date'] = ['gt', time()];
	    } elseif($data['status'] == 1) {
	    	$where['t.status'] = 1;
	    }
	    /*if($data['status'] == 1) {//完成状态或时间大于预约时间
		    $res = $res->where(function ($query){
			    $query->where('t.status', 1)
				    ->whereOr('t.recover_date', '<', time());
		    });
	    }*/
	    $res = Db::name('task')->alias('t')
		    ->join('member m', 't.member_id=m.id')
		    ->where($where)
		    ->limit($data['limit'], 8)
		    ->field('*,t.id as task_id,t.status as task_status')
		    ->order('t.id desc')
		    ->select();
    	return $res;
    }

	/**
	 * 获取订单/任务信息
	 * @param $where
	 * @return array|false|\PDOStatement|string|\think\Model
	 */
    public function getOrderInfo($where)
    {
    	$res = Db::name('task')->alias('t')
		    ->join('member m', 't.user_id=m.id', 'LEFT')
		    ->join('recover_type rt', 't.recover_type=rt.id', 'LEFT')
		    ->where($where)
		    ->field('*,t.id as task_id,t.status as task_status,t.create_time as create_task_time,rt.name as recover_name')
		    ->find();
    	return $res;
    }

    public function getOrderSum($data)
    {
	    $where['member_id'] = session('member_id');
	    if($data['status'] == 2) {
		    $where['status'] = 2;
		    $where['recover_date'] = ['gt', time()];
	    }
	    $res = Db::name('task')->where($where)
		    ->count('id');
	    return $res;
    }

}