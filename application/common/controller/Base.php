<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/22
 * Time: 18:24
 */
namespace app\common\controller;
use app\admin\model\AdminUser;
use app\common\model\Member;
use think\Controller;
use think\Db;
use think\Request;

class Base extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        if(session('aid')) {
            $this->assign('is_admin', 1);
        } else {
            $this->assign('is_admin', 0);
        }
    }

    /**
	 * 刷新用户session
	 * @param $where
	 */
    public function refreshSessionMember($where)
    {
        $user = Member::getInstance()->getMemberByWhere($where);
        $member_id = $user['id'];
        session('member_id', $member_id);
        session('member_info', $user);
    }

    /**
     * 刷新员工session
     * @param $id
     */
    public function refreshSessionUser($id)
    {
        $user = AdminUser::getInstance()->getInfo(['au.id' => $id]);
        session('menu',$user['rule']);
        session('role_id',$user['role_id']);
        session('user_id', $user['id']);
        session('user_info', $user);
        session('aid', $user['aid']);
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

    public function checkRole()
    {
        if(!session('aid')) {
            $this->assign('is_admin', 0);
            return $this->fetch('user/index');
        } else {
            $this->assign('is_admin', 1);
        }
    }
}