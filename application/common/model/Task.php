<?php
/**
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

    public function addTask($data)
    {
        $data = clearArray($data);//清除数组空键值对
        $data['update_time'] = time();
        return Db::name('order')->insert($data);
    }

    public function getOrderList($where, $limit)
    {
    	$res = Db::name('task')->alias('t')
		    ->join('member m', 't.member_id=m.id')
		    ->where($where)
		    ->limit($limit, 8)
		    ->field('*,t.id as task_id,t.status as task_status')
		    ->order('t.id desc')
		    ->select();
    	return $res;
    }

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
}