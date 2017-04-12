<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/2
 * Time: 16:03
 */

namespace app\admin\controller;


use app\admin\model\AdminAdmin;
use app\admin\model\AdminUser;

class Admin extends Base
{
	/**
	 * 企业列表
	 * @return mixed
	 */
	public function index()
	{
		$list = AdminAdmin::getInstance()->getList();
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function add()
	{
		if(request()->isPost()) {
			$post = input("post.");
			if(AdminAdmin::getInstance()->addData($post)) {
				return json(['code' => 1, 'data' => '', 'msg' => '提交成功!']);
			} else {
				return json(['code' => 0, 'data' => '', 'msg' => '提交失败!请稍后再试!']);
			}
		} else {
			$list = AdminUser::getInstance()->getList(['status' => 1]);
			$this->assign('list', $list);
			return $this->fetch();
		}
	}

	public function admin_add()
	{
		$list = AdminAdmin::getInstance()->getList(['status' => 1]);
		$userList = AdminUser::getInstance()->getList(['status' => 1]);
		$this->assign('list', $list);
		$this->assign('userList', $userList);
		return $this->fetch('admin-add');
	}

	/**
	 * 员工列表
	 * @return mixed
	 */
	public function user()
	{
		$list = AdminUser::getInstance()->getList();
		$this->assign('list', $list);
		return $this->fetch();
	}

}