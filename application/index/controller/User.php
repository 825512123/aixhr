<?php
/**
 * 用户类
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/23
 * Time: 15:11
 */

namespace app\index\controller;


use app\admin\model\AdminUser;
use app\common\controller\Base;
use app\common\controller\MessageApi;
use app\common\model\IncomeLog;
use app\common\model\Member;
use app\common\model\Task;
use app\common\model\TransferLog;
use think\Request;

class User extends Base
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

    public function index()
    {
        return $this->fetch('index');
    }

    /**
     * 检查手机号是否存在
     * @return \think\response\Json
     */
    public function checkMobile()
    {
        $post = input("post.");
        $res = Member::getInstance()->getMemberByWhere(['mobile' => $post['mobile']]);
        if ($res > 0){
            return json(['code' => 1, 'data' => '', 'msg' => '手机号已注册,请直接登录']);
        }
        return json(['code' => 0, 'data' => '', 'msg' => '该手机号可注册']);
    }

    /**
     * 发送验证码
     * @return \think\response\Json
     */
    public function sendCode()
    {
        $post = input("post.");
        $res = Member::getInstance()->getMemberByWhere(['mobile' => $post['mobile']]);
        if ($res > 0){
            return json(['code' => 1, 'data' => '', 'msg' => '手机号已注册,请直接登录']);
        } else {
            $message = new MessageApi();
            if($message->sendCode($post['mobile'])) {
                return json(['code' => 0, 'data' => '', 'msg' => '验证码已发送']);
            } else {
                return json(['code' => 1, 'data' => '', 'msg' => '验证码发送失败']);
            }
        }
    }

    /**
     * 重设密码验证码
     * @return \think\response\Json
     */
    public function sendCodePass()
    {
        $post = input("post.");
        $res = Member::getInstance()->getMemberByWhere(['mobile' => $post['mobile']]);
        if ($res > 0){
            $message = new MessageApi();
            if($message->sendCode($post['mobile'])) {
                return json(['code' => 0, 'data' => $res, 'msg' => '验证码已发送']);
            } else {
                return json(['code' => 1, 'data' => '', 'msg' => '验证码发送失败']);
            }
        } else {
            return json(['code' => 1, 'data' => '', 'msg' => '手机号未注册,请注册登录']);
        }
    }

	/**
	 * 判断验证码
	 * @return \think\response\Json
	 */
    public function checkCode()
    {
        $post = input("post.");
        if(session('reg_code') && intval($post['code']) == intval(session('reg_code'))) {
            return json(['code' => 0, 'data' => '', 'msg' => '验证码正确']);
        } else {
            return json(['code' => 1, 'data' => '', 'msg' => '验证码错误']);
        }
    }

	/**
	 * 余额
	 * @return mixed
	 */
    public function money()
    {
        if(session('aid')) {
            $user_id = session('user_id');
            $userList = AdminUser::getInstance()->where('id','neq',$user_id)->select();
            $transferList = TransferLog::getInstance()->getCollectionList(['tl.user_id' => $user_id,'tl.status' => 2]);
            $this->assign('userList', $userList);
            $this->assign('transferList', $transferList);
        }
    	return $this->fetch();
    }

	/**
	 * 积分
	 * @return mixed
	 */
    public function integral()
    {
    	return $this->fetch();
    }

	/**
	 * 统计
	 * @return mixed
	 */
    public function count()
    {
    	$where['member_id'] = session('member_id');
    	$where['status'] = 1;
    	$sum_order = Task::getInstance()->where($where)->count('id');
    	//$sum_order = Task::getInstance()->getOrderSumByMember($where);
	    $this->assign('sum_order', $sum_order);
    	return $this->fetch();
    }

    /**
     * 收入列表
     * @return mixed|\think\response\Json
     */
    public function incomeList()
    {
	    if(request()->isPost()) {
		    $post = input("post.");
		    $where['member_id'] = session('member_id');
		    $res = IncomeLog::getInstance()->getIncomeList($where, $post['limit']);
		    if ($res > 0){
			    $sum = IncomeLog::getInstance()->where($where)->count('id');
			    return json(['code' => 1, 'data' => $res, 'sum' => $sum, 'msg' => '成功']);
		    } else {
			    return json(['code' => 0, 'data' => '', 'sum' => 0, 'msg' => '失败']);
		    }
	    } else {
		    return $this->fetch('incomeList');
	    }
    }

	/**
	 * 编辑用户
	 * @return \think\response\Json
	 */
    public function editMember()
    {
	    $post = input("post.");
	    $post['id'] = session('member_id');
	    $res = Member::getInstance()->editMember($post);
	    if($res) {
	    	$this->refreshSessionMember(['id' => $post['id']]);
		    $user = json_encode(session('member_info'));
		    return json(['code' => 1, 'data' => $user, 'msg' => '操作成功！']);
	    } else {
		    return json(['code' => 0, 'data' => '', 'msg' => '操作失败，请稍候再试！']);
	    }
    }

    /**
     * 新增用户
     * @return mixed|\think\response\Json
     */
    public function addUser()
    {
        $this->checkRole();
        if(request()->isPost()) {
            $post = input("post.");
            $post['aid'] = session('aid');
            $post['user_id'] = session('user_id');
            if (Member::getInstance()->editMember($post)){
                return json(['code' => 1, 'data' => '', 'msg' => '客户添加成功!']);
            } else {
                return json(['code' => 0, 'data' => '', 'sum' => 0, 'msg' => '失败!请稍后再试!']);
            }
        } else {
            return $this->fetch();
        }
    }

    /**
     * 客户列表
     * @return mixed|\think\response\Json
     */
    public function memberList()
    {
        $this->checkRole();
        if (request()->isPost()) {
            $post = input("post.");
            $res = Member::getInstance()->getMemberList($post);
            if ($res > 0) {
                $sum = Member::getInstance()->getMemberSum($post);
                return json(['code' => 1, 'data' => $res, 'sum' => $sum, 'msg' => '成功']);
            } else {
                return json(['code' => 0, 'data' => '', 'sum' => 0, 'msg' => '失败']);
            }
        } else {
            return $this->fetch();
        }
    }

}