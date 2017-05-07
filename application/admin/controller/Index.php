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

	/**
	 * 退出登录
	 * @return \think\response\Json
	 */
	public function loginOut()
	{
		session('role_id',null);
		session('user_id', null);
		session('menu', null);
		session('aid', null);
		session('user_info', null);
		return json(['code' => 1, 'msg' => '退出成功']);
	}
}
