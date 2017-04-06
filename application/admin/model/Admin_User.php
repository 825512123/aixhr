<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/2
 * Time: 16:14
 */

namespace app\admin\model;

use app\common\model\Base;
use think\Db;
class Admin_User extends Base
{
	private static $_instance;

	public static function getInstance()
	{
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function getList($where = [])
	{
		$res = Db::name('admin_user')
			->where($where)
			->order('id desc')
			->select();
		return $res;
	}
}