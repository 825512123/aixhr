<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 12:59
 */

namespace app\admin\model;


use app\common\model\Base;
use think\Db;

class WithdrawLog extends Base
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
     * 获取列表
     * @param array $where
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getList($where = [])
    {
        if(session('role_id') != 1) {$where['t.aid'] = session('aid');}
        $res = Db::name('withdraw_log')->alias('t')
            ->join('admin_user au', 'au.id=t.user_id', 'LEFT')
            ->join('member m', 'm.id=t.member_id')
            ->where($where)
            ->field('t.*,m.username as member_name,m.mobile as member_mobile,au.name as user_name,au.mobile as user_mobile')
            ->order('id desc')
            ->select();
        return $res;
    }

    public function confirm($data = [])
    {
        $data['user_id'] = session('user_id');
        $info = $this->where('id', $data['id'])->find();
        Db::startTrans();
        try {
            Db::name('withdraw_log')->where('id',$data['id'])->update($data);
            // 录入用户收支表
            $income = ['aid' => $info['aid'], 'member_id' => $info['member_id'], 'money' => $info['money'], 'create_time' => $data['run_time'],'type' => 2,'order_id' => $info['id']];
            Db::name('income_log')->insert($income);
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

    public function cancel($data = [])
    {
        $data['user_id'] = session('user_id');
        $info = $this->where('id', $data['id'])->find();
        Db::startTrans();
        try {
            Db::name('withdraw_log')->where('id',$data['id'])->update($data);
            // 返还用户余额
            Db::name('member')->where('id', $info['member_id'])->setInc('yue', $info['money']);
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