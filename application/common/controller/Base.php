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

class Base extends Controller
{
    public function refreshSessionMember($where)
    {
        $user = Member::getInstance()->getMemberByWhere($where);
        $member_id = $user['id'];
        session('member_id', $member_id);
        session('member_info', $user);
    }
}