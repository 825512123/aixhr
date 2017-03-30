<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/30
 * Time: 11:22
 */
namespace app\admin\model;
use app\common\model\Base;
use think\Db;
class Member extends Base
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
		$res = Db::name('member')
			->where($where)
			->order('id desc')
			->select();
		return $res;
	}
}