<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/12
 * Time: 0:32
 */

namespace app\common\model;


use think\Db;

class RecoverPrice extends Base
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
     * 获取回收类型及价格
     * @param array $where
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getList($where = [])
    {
        $where['status'] = 1;
        $res = Db::name('recover_price')
            ->where($where)
            ->order('pid')
            ->select();
        return $res;
    }

    /**
     * 获取用户特殊价格列表
     * @param array $where
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getMemberPriceList($where = [])
    {
        $res = Db::name('member_price')
            ->where($where)
            ->field('recover_type_id as id,price')
            ->select();
        return $res;
    }

    /**
     * 获取用户特殊价格
     * @param array $where
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getMemberPrice($where = [])
    {
        $res = Db::name('member_price')->alias('mp')
            ->join('recover_price rp', 'rp.id = mp.recover_type_id')
            ->where($where)
            ->field('mp.recover_type_id as id,mp.price,rp.name')
            ->select();
        return $res;
    }

    public function editMemberPrice($data = [])
    {
        $res = Db::name('member_price')
            ->where(['member_id' => $data['member_id'],'recover_type_id' => $data['recover_type_id']])
            ->update($data);
        return $res;
    }

    public function addMemberPrice($data = [])
    {
        return Db::name('member_price')->insert($data);
    }

    public function getMemberPriceFind($where = [])
    {
        return Db::name('member_price')->where($where)->find();
    }
}