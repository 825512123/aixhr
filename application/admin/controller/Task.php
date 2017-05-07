<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/24
 * Time: 10:52
 */

namespace app\admin\controller;


use app\admin\model\AdminUser;
use app\common\controller\FebookMessage;

class Task extends Base
{
	/**
	 * 订单首页
	 * @param string $type
	 * @return mixed
	 */
	public function index($type = 'undone')
	{
		// 获取回收员列表
		$recyclers = AdminUser::getInstance()->getRoleList(['au.status' => 1,'au.role_id' => 9]);
		$data = $this->getList($type);
		$this->assign('type', $type);
		$this->assign('list', $data);
		$this->assign('recyclers', $recyclers);
		return $this->fetch();
	}

	/**
	 * 获取订单列表
	 * @param string $type
	 * @return false|\PDOStatement|string|\think\Collection
	 */
	private function getList($type = 'undone')
	{
		$taskModel = \app\admin\model\Task::getInstance();
		switch ($type)
		{
			case 'undone':// 未完成
				$where['t.status'] = 2;
				$where['t.recover_date'] = ['gt', time()];
				$data = $taskModel->getList($where);
				break;
			case 'end':// 已完成
				$where['t.status'] = 1;
				$data = $taskModel->getList($where);
				break;
			case 'expired':// 已过期
				$where['t.status'] = 2;
				$where['t.recover_date'] = ['lt', time()];
				$data = $taskModel->getList($where);
				break;
			default:
				$where['t.status'] = 0;
				$data = $taskModel->getList($where);
		}
		return $data;
	}

	/**
	 * 分配回收员
	 * @return \think\response\Json
	 */
	public function sendTask()
	{
		if(request()->isPost()) {
			$post = input("post.");
			$res = \app\admin\model\Task::getInstance()->editTask($post);
			if ($res > 0) {
				$this->sendTaskMessage($post['id']);
				return json(['code' => 1, 'data' => '', 'msg' => '操作成功!']);
			}
			return json(['code' => 0, 'data' => '', 'msg' => '操作失败!请稍后再试!']);
		} else {
			$this->redirect('/admin/');
		}
	}

	/**
	 * 取消订单
	 * @return \think\response\Json
	 */
	public function cancel()
	{
		if(request()->isPost()) {
			$post = input("post.");
			$data['id'] = $post['id'];
			$data['status'] = 0;
			$res = \app\admin\model\Task::getInstance()->editTask($data);
			if ($res > 0) {
				return json(['code' => 1, 'data' => '', 'msg' => '操作成功!']);
			}
			return json(['code' => 0, 'data' => '', 'msg' => '操作失败!请稍后再试!']);
		} else {
			$this->redirect('/admin/');
		}
	}

	private function sendTaskMessage($id)
	{
		$data = \app\admin\model\Task::getInstance()->getTask(['t.id' => $id]);
		$member_content = '【小黄人】尊敬的用户：您好！您召唤小黄人（';
		$member_content .= $data['user_name'].$data['user_mobile'];
		$member_content .= '）成功！并将于';
		$member_content .= $data['task_date'].$data['task_time'];
		$member_content .= '上门为您服务。感谢您对小黄人的大力支持，如有投诉建议请拨打：0817-3307787';

		$user_content = '【小黄人】尊敬的小黄人：用户（';
		$user_content .= $data['member_name'].$data['member_mobile'];
		$user_content .= '）召唤你召唤你于';
		$user_content .= $data['task_date'].$data['task_time'];
		$user_content .= '至';
		$user_content .= $data['task_city'].$data['task_address'];
		$user_content .= '上门服务。请提前做好准备，辛苦了伟大的小黄人！';
		$febook = new FebookMessage();
		$febook->sendFontMessage($data['member_mobile'], $member_content);
		$febook->sendFontMessage($data['user_mobile'], $user_content);
	}
}