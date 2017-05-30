<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/22
 * Time: 15:55
 */

namespace app\common\model;


use think\Db;

class TransferLog extends Base
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
     * 转账
     * @param array $data
     * @return bool
     */
    public function transfer($data = [])
    {
        $data['status'] = 2;
        $data['create_time'] = time();
        Db::startTrans();
        try {
            Db::name('transfer_log')->insert($data);
            // 扣去员工余额
            Db::name('admin_user')->where('id', $data['send_id'])->setDec('yue', $data['money']);
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
     * 确认收账
     * @param array $data
     * @return bool
     */
    public function confirmTransfer($data = [])
    {
        $info = $this->where('id', $data['id'])->find();
        Db::startTrans();
        try {
            Db::name('transfer_log')->where('id',$data['id'])->update($data);
            // 增加被转账员工余额
            Db::name('admin_user')->where('id', $info['user_id'])->setInc('yue', $info['money']);
            $time = time();
            $admin_income[] = ['aid' => $info['aid'], 'user_id' => $info['user_id'], 'money' => $info['money'], 'create_time' => $time,'type' => 3,'order_id' => $info['id']];
            $admin_income[] = ['aid' => $info['aid'], 'user_id' => $info['send_id'], 'money' => $info['money'], 'create_time' => $time,'type' => 2,'order_id' => $info['id']];
            Db::name('admin_income')->insertAll($admin_income);// 录入员工收支表
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
     * 获取转账列表
     * @param $where
     * @param $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getTransferList($where, $limit)
    {
        $res = Db::name('transfer_log')->alias('tl')
            ->join('admin_user au', 'tl.user_id=au.id')
            ->join('admin_user s', 'tl.send_id=s.id')
            ->where($where)
            ->limit($limit, 8)
            ->order('tl.id desc')
            ->field('tl.*,au.name,s.name as send_name')
            ->select();
        return $res;
    }

    /**
     * 获取单条转账信息
     * @param array $where
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getTransfer($where = [])
    {
        $res = Db::name('transfer_log')->alias('tl')
            ->join('admin_user au', 'tl.user_id=au.id')
            ->join('admin_user s', 'tl.send_id=s.id')
            ->where($where)
            ->field('tl.*,au.name,s.name as send_name')
            ->find();
        return $res;
    }

    /**
     * 获取收账列表
     * @param $where
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getCollectionList($where)
    {
        $res = Db::name('transfer_log')->alias('tl')
            ->join('admin_user au', 'tl.send_id=au.id')
            ->where($where)
            ->order('tl.id desc')
            ->field('tl.*,au.name')
            ->select();
        return $res;
    }
}