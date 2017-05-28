<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/24
 * Time: 12:17
 */

namespace app\admin\model;


use app\common\model\Base;
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
     * 获取用户特殊价格
     * @param array $where
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getMemberPrice($where = [])
    {
        $res = Db::name('member_price')
            ->where($where)
            ->field('recover_type_id as id,price')
            ->select();
        return $res;
    }

    public function getInfo($where = [])
    {
        return $this->where($where)->find();
    }

    public function edit($data = [])
    {
        if ((isset($data['id']) && $data['id'] > 0)) {
            $res = $this->allowField(true)->where('id', $data['id'])->update($data);
        } else {
            $data['create_time'] = time();
            $res = $this->allowField(true)->data($data)->save();
        }
        return $res;
    }

    public function getMemberPriceList()
    {
        return Db::name('member_price')->select();
    }
}