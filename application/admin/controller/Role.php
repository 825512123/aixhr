<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/15
 * Time: 16:22
 */

namespace app\admin\controller;


use app\admin\model\AdminNode;
use app\admin\model\AdminRole;

class Role extends Base
{
	public function index()
	{
		$role = AdminRole::getInstance()->getList();
		$this->assign('list', $role);
		return $this->fetch();
	}

	/**
	 * 新增/编辑角色
	 * @param string $id
	 * @return mixed|\think\response\Json
	 */
	public function edit($id = '')
	{
		if(request()->isPost()) {
			$post = input("post.");
			if(AdminRole::getInstance()->editRole($post)) {
				return json(['code' => 1, 'data' => '', 'msg' => '成功!']);
			} else {
				return json(['code' => 0, 'data' => '', 'msg' => '失败!']);
			}
		}
		if($id) {
			$data = AdminRole::getInstance()->getInfo(['id' => $id]);
			$this->assign('pageTitle', '编辑角色');
		} else {
			$data['id'] = 0;
			$data['rolename'] = '';
			$data['status'] = 1;
			$this->assign('pageTitle', '新增角色');
		}
		$this->assign('info', $data);
		return $this->fetch();
	}

	/**
	 * 禁用角色
	 * @return mixed
	 */
	public function down()
	{
		if(request()->isPost()) {
			$post = input("post.");
			$post['status'] = '0';
			$res = AdminRole::getInstance()->editRole($post);
			if ($res > 0) {
				return json(['code' => 1, 'data' => '', 'msg' => '操作成功!']);
			}
			return json(['code' => 0, 'data' => '', 'msg' => '操作失败!请稍后再试!']);
		} else {
			$this->redirect('/admin/role');
		}
	}

	/**
	 * 启用角色
	 * @return \think\response\Json
	 */
	public function up()
	{
		if(request()->isPost()) {
			$post = input("post.");
			$post['status'] = 1;
			$res = AdminRole::getInstance()->editRole($post);
			if ($res > 0) {
				return json(['code' => 1, 'data' => '', 'msg' => '操作成功!']);
			}
			return json(['code' => 0, 'data' => '', 'msg' => '操作失败!请稍后再试!']);
		} else {
			$this->redirect('/admin/role');
		}
	}

	/**
	 * 角色授权
	 * @return mixed
	 */
	public function access($id = '')
	{
		if(!$id) {$this->redirect('/admin/role');}
		if(request()->isPost()) {
			$role = AdminRole::getInstance()->getInfo(['id' => $id]);
			$data = AdminNode::getInstance()->getNodeInfo($role['rule']);
			return json(['code' => 0, 'data' => $data, 'msg' => '操作失败!请稍后再试!']);
		} else {
			$role = AdminRole::getInstance()->getInfo(['id' => $id]);
			$data = AdminNode::getInstance()->getList();
			$data = $this->prepareMenu($data,$role['rule']);
			$this->assign('node', $data);
			return $this->fetch();
		}
	}

	/**
	 * 节点首页
	 * @return mixed
	 */
	public function node($id = '')
	{
		if($id) {
			$where = ['pid' => $id];
		} else {
			$where = ['pid' => 0];
		}
		$role = AdminNode::getInstance()->getList($where);
		$this->assign('list', $role);
		return $this->fetch();
	}

	public function node_add()
	{
		return $this->fetch();
	}

	/**
	 * 编辑/新增节点
	 * @return mixed
	 */
	public function node_edit($id = '')
	{
		if(request()->isPost()) {
			$post = input("post.");
			if(AdminNode::getInstance()->editNode($post)) {
				return json(['code' => 1, 'data' => '', 'msg' => '成功!']);
			} else {
				return json(['code' => 0, 'data' => '', 'msg' => '失败!']);
			}
		}
		if($id) {
			$data = AdminNode::getInstance()->getInfo(['id' => $id]);
			$this->assign('pageTitle', '编辑节点');
		} else {
			$data['id'] = 0;
			$data['pid'] = 0;
			$data['node_name'] = '';
			$data['icon'] = '';
			$data['module_name'] = '';
			$data['control_name'] = '';
			$data['action_name'] = '';
			$data['is_menu'] = 1;
			$data['status'] = 1;
			$this->assign('pageTitle', '新增节点');
		}
		$list = AdminNode::getInstance()->getList(['is_menu' => 1, 'pid' => 0]);
		$this->assign('list', $list);
		$this->assign('info', $data);
		return $this->fetch();
	}

	/**
	 * 删除节点
	 * @return \think\response\Json
	 */
	public function node_del()
	{
		$post = input("post.");
		$post['status'] = '0';
		$res = AdminNode::getInstance()->editNode($post);
		if ($res > 0){
			return json(['code' => 1, 'data' => '', 'msg' => '操作成功!']);
		}
		return json(['code' => 0, 'data' => '', 'msg' => '操作失败!请稍后再试!']);
	}
}