<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/24
 * Time: 14:54
 */

namespace app\common\model;


use think\Db;

class IncomeLog extends Base
{
	private static $_instance;

	public static function getInstance()
	{
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function getIncomeList($where, $limit)
	{
		$res = Db::name('income_log')
			->where($where)
			->limit($limit, 8)
			->order('id desc')
			->select();
		return $res;
	}
}