<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/24
 * Time: 10:52
 */

namespace app\admin\controller;


use app\admin\model\AdminNode;
use app\admin\model\AdminUser;
use think\auth\Auth;
use think\Controller;
use think\Request;

class Base extends Controller
{
	public function __construct(Request $request = null)
	{
		parent::__construct($request);
		if(!session('user_id')) {
			$this->redirect('login/index');
		}
		$user_info = session('user_info');
		$data = AdminNode::getInstance()->getMenu();
		$c = $request->controller();
		$a = $request->action();
		$menu = $this->prepareMenu($data);
		$title = AdminNode::getInstance()->getInfo(['action_name' => $a,'control_name' => $c]);
		$theme = AdminNode::getInstance()->getInfo(['id' => $title['pid']]);
		$this->assign('user_info', $user_info);// 管理员信息
		$this->assign('role_id', session('role_id'));//角色
		$this->assign('theme', $theme['node_name']);//父级菜单名
		$this->assign('title', $title['node_name']);//子级菜单名
		$this->assign('menu', $menu);

		/*$user_id = session('user_id');
		$aid = session('aid');
		$auth = Auth::instance();
		if($auth->cache()) {

		}*/
	}

	public function prepareMenu($data, $rule=[])
	{
		$parent = []; //父类
		$child = [];  //子类
		if(!empty($rule)){
			$rule = explode(',', $rule);
		}

		foreach ($data as $key => $vo) {
			$vo['checked'] = (!empty($rule) && in_array($vo['id'], $rule)) ? 1 : 0;
			if ($vo['pid'] == 0) {
				$vo['href'] = '#';
				$parent[] = $vo;
			} else {
				$vo['href'] = url($vo['control_name'] . '/' . $vo['action_name']);
				$child[] = $vo;
			}
		}

		foreach ($parent as $key => $vo) {
			foreach ($child as $k => $v) {
				if ($v['pid'] == $vo['id']) {
					if ($v['group']) {
						$parent[$key]['child'][$v['group']][] = $v;
					} else {
						$parent[$key]['child'][] = $v;
					}
				}
			}
		}

		return $parent;
	}
}