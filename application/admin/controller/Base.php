<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/24
 * Time: 10:52
 */

namespace app\admin\controller;


use app\admin\model\AdminUser;
use think\auth\Auth;
use think\Controller;
use think\Request;

class Base extends Controller
{
	public function __construct(Request $request = null)
	{
		parent::__construct($request);
		/*$user_id = session('user_id');
		$aid = session('aid');
		$auth = Auth::instance();
		if($auth->cache()) {

		}*/
	}

	/**
	 * 刷新员工session
	 * @param $where
	 */
	public function refreshSessionUser($where)
	{
		$user = AdminUser::getInstance()->getInfo($where);
		session('user_id', $user['id']);
		session('user_info', $user);
		session('aid', $user['aid']);
	}
}