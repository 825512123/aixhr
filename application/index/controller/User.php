<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/23
 * Time: 15:11
 */

namespace app\index\controller;


use app\common\controller\Base;
use app\common\controller\MessageApi;
use app\common\model\Member;

class User extends Base
{

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

    public function checkCode()
    {
        $post = input("post.");
        if(session('reg_code') && intval($post['code']) == intval(session('reg_code'))) {
            return json(['code' => 0, 'data' => '', 'msg' => '验证码正确']);
        } else {
            return json(['code' => 1, 'data' => '', 'msg' => '验证码错误']);
        }
    }

    public function editMember()
    {
	    $post = input("post.");
	    $post['id'] = session('member_id');
	    $res = Member::getInstance()->editMember($post);
	    if($res) {
	    	$this->refreshSessionMember(['id' => $post['id']]);
		    return json(['code' => 1, 'data' => session('member_info'), 'msg' => '操作成功！']);
	    } else {
		    return json(['code' => 0, 'data' => '', 'msg' => '操作失败，请稍候再试！']);
	    }
    }

    public function repassword()
    {

    }
}