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
            // 增加被转账员工余额
            Db::name('admin_user')->where('id', $data['user_id'])->setInc('yue', $data['money']);
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

    public function confirmTransfer($data = [])
    {
        $info = $this->where('id', $data['id'])->find();
        Db::startTrans();
        try {
            Db::name('transfer_log')->where('id',$data['id'])->update($data);
            // 增加被转账员工余额
            Db::name('admin_user')->where('id', $info['user_id'])->setInc('yue', $info['money']);
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
            ->where($where)
            ->limit($limit, 8)
            ->order('tl.id desc')
            ->field('tl.*,au.name')
            ->select();
        return $res;
    }

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