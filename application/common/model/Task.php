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
	 * 任务/订单数据提交
	 * @param $data
	 * @return int|string
	 */
    public function editTask($data = [])
    {
        return $this->allowField(true)->where('id', $data['id'])->update($data);
    }
	/**
	 * 获取订单/任务列表
	 * @param $data
	 * @return false|\PDOStatement|string|\think\Collection
	 */
    public function getOrderList($data = [])
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
            ->join('recover_price rp', 't.recover_type=rp.id', 'LEFT')
		    ->where($where)
		    ->limit($data['limit'], 8)
		    ->field('t.*,rp.name as recover_name')
		    ->order('t.id desc')
		    ->select();
    	return $res;
    }

	/**
	 * 获取员工订单/任务列表
	 * @param $data
	 * @return false|\PDOStatement|string|\think\Collection
	 */
    public function getUserOrderList($data = [])
    {
	    $where['t.user_id'] = session('user_id');
	    if($data['status'] == 2) {
		    $where['t.status'] = 2;
		    $where['t.recover_date'] = ['gt', time()];
	    } elseif($data['status'] == 1) {
	    	$where['t.status'] = 1;
	    }

	    $res = Db::name('task')->alias('t')
            ->join('recover_price rp', 't.recover_type=rp.id', 'LEFT')
		    ->where($where)
		    ->limit($data['limit'], 8)
		    ->field('t.*,rp.name as recover_name')
		    ->order('t.id desc')
		    ->select();
    	return $res;
    }

	/**
	 * 获取订单/任务信息
	 * @param $where
	 * @return array|false|\PDOStatement|string|\think\Model
	 */
    public function getOrderInfo($where = [])
    {
    	$res = Db::name('task')->alias('t')
		    ->join('member m', 't.member_id=m.id')
		    ->join('admin_user au', 't.user_id=au.id', 'LEFT')
		    ->join('recover_price rp', 't.recover_type=rp.id', 'LEFT')
		    ->where($where)
		    ->field('t.*,au.name as username,au.mobile as user_mobile,m.username as member_name,m.mobile as member_mobile,t.id as task_id,t.status as task_status,t.create_time as create_task_time,rp.name as recover_name')
		    ->find();
    	return $res;
    }

    public function getOrderSum($data = [])
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

	public function getUserOrderSum($data = [])
	{
		$where['user_id'] = session('user_id');
		if($data['status'] == 2) {
			$where['status'] = 2;
			$where['recover_date'] = ['gt', time()];
		}
		$res = Db::name('task')->where($where)
			->count('id');
		return $res;
	}

	public function confirmOrder($data = [])
    {
        $info = $this->allowField(true)->where('id',$data['id'])->find();
        Db::startTrans();
        try{
            Db::name('task')->where('id',$data['id'])->update($data);
            if($info['task_type'] == 1) {
                Db::name('member')->where('id',$info['member_id'])->setInc('yue',$info['task_money']);
            } else {
                Db::name('member')->where('id',$info['member_id'])->setInc('money',$info['task_money']);
            }
            // 提交事务
            Db::commit();
            $res = true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $res = false;
        }
        return $res;
    }

}