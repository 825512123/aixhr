<?php
namespace app\admin\controller;

use app\admin\model\AdminUser;

class Index extends Base
{
    public function index()
    {
        return $this->fetch();
    }

    public function login()
    {
	    $this->view->engine->layout(false);
	    return $this->fetch();
    }

    public function userLogin($username, $password)
    {
	    $arr = explode('@',$username);
	    if(isset($arr[1])) {
	    	$where['aid'] = $arr[1];
	    	$where['mobile'] = $arr[0];
	    	$adminUser = AdminUser::getInstance()->getInfo($where);
		    if(empty($adminUser)) {
			    return json(['code' => -1, 'data' => '', 'msg' => '用户不存在！']);
		    }
		    if(md5(md5($password) . config('data_auth_key')) != $adminUser['password']){
			    return json(['code' => -2, 'data' => "", 'msg' => '密码错误！']);
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
