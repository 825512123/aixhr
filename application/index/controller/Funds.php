<?php
/**
 * 资金管理
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/10
 * Time: 15:28
 */

namespace app\index\controller;


use app\common\controller\Base;
use app\common\model\TransferLog;
use app\common\model\WithdrawLog;

class Funds extends Base
{

	/**
	 * 提现申请
	 * @return mixed|\think\response\Json
	 */
	public function withdraw()
	{
		if(request()->isPost()) {
			$post = input("post.");
			$data['money'] = (float)$post['money'];
			$member_info = session('member_info');
			if($post['money'] > $member_info['yue']) {
				return json(['code' => 0, 'data' => '', 'msg' => '提现金额大于余额！']);
			}
			$data['type'] = $this->getWithdrawType($post['type']);
			$data['info'] = $this->getWithdrawInfo($post['type'], $member_info);
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

	/**
	 * 返回提现类型
	 * @param $type
	 * @return int
	 */
	public function getWithdrawType($type)
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

	/**
	 * 返回提现信息
	 * @param $type
	 * @param $member_info
	 * @return string
	 */
	public function getWithdrawInfo($type, $member_info)
	{
		switch ($type)
		{
			case 'wechat':
				return '账号：'.$member_info['wechat']; break;
			case 'alipay':
				return '账号：'.$member_info['alipay']; break;
			case 'bank':
				return $member_info['actual_name'].'<br/>'.$member_info['bank_name'].'<br/>'.$member_info['bank']; break;
			default:
				return '其他提现类型';
		}
	}

	/**
	 * 提现列表
	 * @return mixed|\think\response\Json
	 */
	public function withdrawList()
	{
		if(request()->isPost()) {
			$post = input("post.");
			$where['status'] = $post['status'];
			$where['member_id'] = session('member_id');
			$res = WithdrawLog::getInstance()->getWithdrawList($where, $post['limit']);
			if ($res > 0){
				$sum = WithdrawLog::getInstance()->where($where)->count('id');
				return json(['code' => 1, 'data' => $res, 'sum' => $sum, 'msg' => '成功']);
			} else {
				return json(['code' => 0, 'data' => '', 'sum' => 0, 'msg' => '失败']);
			}
		} else {
			return $this->fetch('/user/withdrawList');
		}
	}

	/**
	 * 提现详情
	 * @return mixed|\think\response\Json
	 */
	public function withdrawInfo()
	{
		if(request()->isPost()) {
			$post = input("post.");
			$where['id'] = $post['withdraw_id'];
			$where['member_id'] = session('member_id');
			$res = WithdrawLog::getInstance()->getWithdrawInfo($where);
			if ($res > 0){
				return json(['code' => 1, 'data' => $res, 'msg' => '成功']);
			} else {
				return json(['code' => 0, 'data' => '', 'msg' => '失败']);
			}
		} else {
			return $this->fetch('/user/withdrawInfo');
		}
	}

    /**
     * 转账
     * @return \think\response\Json
     */
	public function transfer()
    {
        $post = input("post.");
        $post['aid'] = session('aid');
        $post['send_id'] = session('user_id');
        if (TransferLog::getInstance()->transfer($post)){
            $this->refreshSessionUser($post['send_id']);
            $user = json_encode(session('user_info'));
            return json(['code' => 1, 'data' => $user, 'msg' => '成功']);
        } else {
            return json(['code' => 0, 'data' => '', 'msg' => '失败']);
        }
    }

    /**
     * 员工确认转账
     * @return \think\response\Json
     */
    public function confirmTransfer()
    {
        $post = input("post.");
        $post['status'] = 1;
        $post['end_time'] = time();
        if (TransferLog::getInstance()->confirmTransfer($post)) {
            $this->refreshSessionUser(session('user_id'));
            $user = json_encode(session('user_info'));
            return json(['code' => 1, 'data' => $user, 'msg' => '收账成功!']);
        } else {
            return json(['code' => 0, 'data' => '', 'msg' => '失败!请稍后再试!']);
        }
    }

    /**
     * 转账列表
     * @return mixed|\think\response\Json
     */
    public function transferList()
    {
        if(request()->isPost()) {
            $post = input("post.");
            $where['tl.status'] = $post['status'];
            $where['tl.send_id'] = session('user_id');
            $res = TransferLog::getInstance()->getTransferList($where, $post['limit']);
            if ($res > 0){
                $sum = TransferLog::getInstance()->where(['status'=>$post['status'],'send_id'=>$where['tl.send_id']])->count('id');
                return json(['code' => 1, 'data' => $res, 'sum' => $sum, 'msg' => '成功']);
            } else {
                return json(['code' => 0, 'data' => '', 'sum' => 0, 'msg' => '失败']);
            }
        } else {
            return $this->fetch('/user/transferList');
        }
    }
}