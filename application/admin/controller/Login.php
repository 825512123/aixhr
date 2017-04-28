<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/25
 * Time: 18:11
 */

namespace app\admin\controller;


use app\admin\model\AdminRole;
use app\admin\model\AdminUser;
use think\Controller;
use think\Request;

class Login extends Controller
{
	public function __construct(Request $request = null)
	{
		parent::__construct($request);
		$user_info = session('user_info');
		if($user_info['status']) {
			$this->redirect('/admin');
		}
	}

	/**
	 * 刷新员工session
	 * @param $where
	 */
	public function refreshSessionUser($where)
	{
		$user = AdminUser::getInstance()->getInfo($where);
		$role = AdminRole::getInstance()->getInfo(['id' => $user['role_id']]);
		session('menu',$role['rule']);
		session('user_id', $user['id']);
		session('user_info', $user);
		session('aid', $user['aid']);
	}

	public function index()
	{
		$this->view->engine->layout(false);
		return $this->fetch('index/login');
	}

	public function doLogin($username, $password)
	{
		$arr = explode('@',$username);
		if(isset($arr[1])) {
			$where['aid'] = $arr[1];
			$where['mobile'] = $arr[0];
			$adminUser = AdminUser::getInstance()->getInfo($where);
			if(empty($adminUser)) {
				return json(['code' => -1, 'data' => '', 'msg' => '用户不存在！']);
			}
			$pass = md5(md5($password) . config('data_auth_key'));
			if(md5(md5($password) . config('data_auth_key')) != $adminUser['password']){
				return json(['code' => -2, 'data' => $pass, 'msg' => '密码错误！']);
			}
			if(1 != $adminUser['status']){
				return json(['code' => -6, 'data' => '', 'msg' => '该账号已被禁用！']);
			}

			$data['id'] = $adminUser['id'];
			$data['last_login_time'] = time();
			$data['last_login_ip'] = ip2long($_SERVER["REMOTE_ADDR"]);
			AdminUser::getInstance()->editUser($data);

			$this->refreshSessionUser(['id' => $adminUser['id']]);
			$user = json_encode(session('user_info'));

			return json(['code' => 1, 'member_info' => $user, 'member_id' => session('user_id'), 'aid' => session('aid'), 'msg' => '登录成功']);

		} else {
			return json(['code' => -1, 'data' => '', 'msg' => '用户不存在！']);
		}
	}

}