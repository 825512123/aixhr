<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-11-1 0001
 * Time: 15:55:57
 */

namespace app\common\model;

use think\Db;

class Member extends Base
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
     * 编辑佣兵
     * @param array $data
     * @return false|int|string
     * @throws \think\Exception
     */
    public function editMember($data = [])
    {
        $data = clearArray($data);//清除数组空键值对
        $data['update_time'] = time();
        if (isset($data['id'])) {
            if (isset($data['password'])) $data['password'] = md5(md5($data['password']) . config('data_auth_key'));
            $res = $this->allowField(true)->where('id', $data['id'])->update($data);
        } else {
            $data['username'] = isset($data['username']) ? $data['username'] : $data['mobile'];
            $data['password'] = isset($data['password']) ? md5(md5($data['password']) . config('data_auth_key')) : md5(md5('123456') . config('data_auth_key'));
            $data['create_time'] = $data['update_time'];
            $res = $this->allowField(true)->data($data)->save();
        }
        return $res;
    }

    /**
     * 在已有member的情况下新增npc
     * @param array $npc_data
     * @return bool|false|int|string
     */
    public function addNpc($npc_data = [])
    {
        $npc_data = clearArray($npc_data);//清除数组空键值对
        $npc_data['create_npc_time'] = time();
        Db::startTrans();
        try{
            Db::name('member_npc')->insert($npc_data);
            Db::name('member')->where('id',$npc_data['member_id'])->update(['role' => 1]);
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
     * 根据id返回 佣兵数据
     * @param $id
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getMember($id)
    {
        /*$res = Db::name('member')->alias('m')
            ->join('member_npc n', 'n.member_id = m.id', 'LEFT')
            ->where('m.id', $id)
            ->find();*/
        $res = Db::name('member')
            ->where('id', $id)
            ->find();
        return $res;
    }

    /**
     * 根据条件获取佣兵数据
     * @param $where
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getMemberByWhere($where)
    {
        $res = Db::name('member')->where($where)->find();
        return $res;
    }
}