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
     * @param array $where
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getOrderList($where = [], $limit = 0)
    {
        $res = Db::name('task')->alias('t')
            ->join('recover_price rp', 't.recover_type=rp.id', 'LEFT')
            ->where($where)
            ->limit($limit, 8)
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
        if ($data['status'] == 2) {
            $where['t.status'] = 2;
            $where['t.recover_date'] = ['gt', time()];
        } elseif ($data['status'] == 1) {
            $where['t.status'] = 1;
        }

        $res = Db::name('task')->alias('t')
            ->join('member m', 't.member_id=m.id')
            ->where($where)
            ->limit($data['limit'], 8)
            ->field('t.*,m.username')
            ->order('t.id desc')
            ->select();
        return $res;
    }

    /**
     * 日订单
     * @param array $where
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getDayOrderList($where = [])
    {
        $res = Db::name('task')->alias('t')
            ->join('admin_user au', 't.user_id=au.id','LEFT')
            ->join('member m', 't.member_id=m.id')
            ->where($where)
            ->field('t.*,m.username member_name,au.name username')
            ->order('t.id desc')
            ->select();
        return $res;
    }

    public function getDayOrderSum($where = [])
    {
        $res = Db::name('task')->where($where)
            ->count('id');
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
        if ($data['status'] == 2) {
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
        if ($data['status'] == 2) {
            $where['status'] = 2;
            $where['recover_date'] = ['gt', time()];
        }
        $res = Db::name('task')->where($where)
            ->count('id');
        return $res;
    }

    /**
     * 客户确认收货
     * @param array $data
     * @return bool
     */
    public function confirmOrder($data = [])
    {
        $info = $this->where('id', $data['id'])->find();
        Db::startTrans();
        try {
            Db::name('task')->where('id', $data['id'])->update($data);
            if ($info['task_type'] == 1) {
                $income = ['aid' => $info['aid'], 'member_id' => $info['member_id'], 'money' => $info['task_money'], 'create_time' => time(),'type' => 1,'order_id' => $info['id']];
                Db::name('income_log')->insert($income);// 录入收支表
                Db::name('member')->where('id', $info['member_id'])->setInc('yue', $info['task_money']);
            } else {
                Db::name('member')->where('id', $info['member_id'])->setInc('money', $info['task_money']);
                // 扣去员工余额
                Db::name('admin_user')->where('id', $info['user_id'])->setDec('yue', $info['task_money']);
                $admin_income = ['aid' => $info['aid'], 'user_id' => $info['user_id'], 'money' => $info['task_money'], 'create_time' => time(),'type' => 1,'order_id' => $info['id']];
                Db::name('admin_income')->insert($admin_income);// 录入员工收支表
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

    /**
     * 员工新增订单
     * @param array $data
     * @return bool
     */
    public function addOrder($data = [])
    {
        Db::startTrans();
        try {
            Db::name('task')->insert($data);
            // 扣去员工余额
            Db::name('admin_user')->where('id', $data['user_id'])->setDec('yue', $data['task_money']);
            if ($data['task_type'] == 1) {
                // 增加客户余额
                Db::name('member')->where('id', $data['member_id'])->setInc('yue', $data['task_money']);
                $income = ['aid' => $data['aid'], 'member_id' => $data['member_id'], 'money' => $data['task_money'], 'create_time' => time(),'type' => 1,'order_id' => $data['order_id']];
                Db::name('income_log')->insert($income);// 录入收支表
            } else {
                // 增加客户已提现
                Db::name('member')->where('id', $data['member_id'])->setInc('money', $data['task_money']);
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