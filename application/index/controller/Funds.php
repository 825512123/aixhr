<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/10
 * Time: 15:28
 */

namespace app\index\controller;


use app\common\controller\Base;
use app\common\model\WithdrawLog;

class Funds extends Base
{

	public function withdraw()
	{
		if(request()->isPost()) {
			$post = input("post.");
			$data['money'] = (float)$post['money'];
			$member_info = session('member_info');
			if($post['money'] > $member_info['yue']) {
				return json(['code' => 0, 'data' => '', 'msg' => '提现金额大于余额！']);
			}
			$data['type'] = $this->checkWithdrawType($post['type']);
			if(!$post) {return json(['code' => 0, 'data' => '', 'msg' => '参数错误！']);}
			$res = WithdrawLog::getInstance()->useWithdraw($data);
			if ($res > 0){
				$this->refreshSessionMember(['id' => session('member_id')]);
				$user = json_encode(session('member_info'));
				return json(['code' => 1, 'data' => $user, 'msg' => '申请成功！']);
			} else {
				return json(['code' => 0, 'data' => '', 'msg' => '申请失败，请稍后再试']);
			}
		} else {
			return $this->fetch('/user/withdraw');
		}
	}

	public function checkWithdrawType($type)
	{
		switch ($type)
		{
			case 'wechat':
				return 1; break;
			case 'alipay':
				return 2; break;
			case 'bank':
				return 3; break;
			default:
				return 0;
		}
	}

}