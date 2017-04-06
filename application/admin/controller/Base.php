<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/24
 * Time: 10:52
 */

namespace app\admin\controller;


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
}