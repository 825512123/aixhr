<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/22
 * Time: 18:24
 */
namespace app\common\controller;
use app\common\model\Member;
use think\Controller;
use think\Db;

class Base extends Controller
{
    public function refreshSessionMember($where)
    {
        $user = Member::getInstance()->getMemberByWhere($where);
        $member_id = $user['id'];
        session('member_id', $member_id);
        session('member_info', $user);
    }

    public function get_task_id()
    {
        $time = (int)date('ymd');
        $res = Db::name('config')->where(['name' => 'task_id', 'create_date' => $time])->find();
        if ($res) {
            if(Db::name('config')->where(['create_date' => $time])->setInc('value')) {
                return ($res['value'] + 1);
            }
        } else {
            $data['value'] = (int)($time.'0001');
            $data['create_date'] = $time;
            Db::name('config')->where(['name' => 'task_id'])->update($data);
            return $data['value'];
        }
    }
}