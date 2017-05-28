<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/29
 * Time: 22:18
 */

namespace app\admin\controller;


use app\admin\model\Member;
use app\admin\model\RecoverPrice;

class User extends Base
{
	/**
	 * 用户列表
	 * @return mixed
	 */
	public function index()
	{
		$list = Member::getInstance()->getList();
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function edit($id = '')
	{

	}

    /**
     * 禁用
     * @return \think\response\Json
     */
	public function down()
	{
		if(request()->isPost()) {
			$post = input("post.");
			$data['id'] = $post['id'];
			$data['status'] = '0';
			$res = Member::getInstance()->editMember($data);
			if ($res > 0) {
				return json(['code' => 1, 'data' => '', 'msg' => '操作成功!']);
			}
			return json(['code' => 0, 'data' => '', 'msg' => '操作失败!请稍后再试!']);
		} else {
			$this->redirect('/admin/admin/user');
		}
	}

    /**
     * 签约价格
     * @return mixed
     */
	public function price()
	{
        $list = RecoverPrice::getInstance()->getMemberPriceList();
        dump($list);exit;
        $this->assign('list', $list);
        return $this->fetch();
	}
}