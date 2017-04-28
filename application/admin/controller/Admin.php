<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/2
 * Time: 16:03
 */

namespace app\admin\controller;


use app\admin\model\AdminAdmin;
use app\admin\model\AdminRole;
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
			$list = AdminUser::getInstance()->getList(['aa.status' => 1]);
			$this->assign('list', $list);
			return $this->fetch();
		}
	}

	public function admin_add($id = '')
	{
		if(request()->isPost()) {
			$post = input("post.");
			$data = clearArray($post);//清除数组空键值对
			$data = $this->getUserData($data);
			if(AdminUser::getInstance()->editUser($data)) {
				$this->success('添加成功！', 'admin/admin/user');
			}
		} else {
			$user = [];
			if($id > 0) {
				$user = AdminUser::getInstance()->getInfo(['id' => $id]);
			}
			$this->assign('user', $user);
			$list = AdminAdmin::getInstance()->getList(['status' => 1]);
			$roleList = AdminRole::getInstance()->getList(['status' => 1]);
			$userList = AdminUser::getInstance()->getList(['aa.status' => 1]);
			$this->assign('list', $list);
			$this->assign('roleList', $roleList);
			$this->assign('userList', $userList);
			return $this->fetch('admin-add');
		}
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

	/**
	 * 处理并返回员工数据
	 * @param $data
	 * @return mixed
	 */
	public function getUserData($data)
	{
		if(!isset($data['name']) || !isset($data['mobile']) || !isset($data['idcard']) || !isset($data['address'])) {
			$this->error('姓名、电话、身份证号、地址均不能为空！');
		}
		if(isset($data['joinday'])) {
			$data['joinday'] = adminLTEToTime($data['joinday']);
		} else {
			$data['joinday'] = time();
		}
		if(isset($data['birthday'])) {$data['birthday'] = adminLTEToTime($data['birthday']);}
		$password = substr($data['mobile'], -6);
		$data['password'] = md5(md5($password) . config('data_auth_key'));
		$data['create_time'] = time();
		return $data;
	}
}