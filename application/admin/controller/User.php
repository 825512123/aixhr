<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/29
 * Time: 22:18
 */

namespace app\admin\controller;


use app\admin\model\Member;

class User extends Base
{
	public function index()
	{
		$list = Member::getInstance()->getList();
		$this->assign('list', $list);
		return $this->fetch();
	}
}