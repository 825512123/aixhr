<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/10
 * Time: 18:42
 */

namespace app\common\model;


use think\Db;

class WithdrawLog extends Base
{

	private static $_instance;

	public static function getInstance()
	{
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function useWithdraw($data)
	{
		$data['member_id'] = session('member_id');
		$data['create_time'] = time();
		Db::startTrans();
		try{
			Db::name('member')->where(['id' => session('member_id')])->setDec('yue', $data['money']);
			Db::name('withdraw_log')->insert($data);
			// 提交事务
			Db::commit();
			$res = true;
		} catch (\Exception $e) {
			// 回滚事务
			Db::rollback();
			$res = false;
		}
		return $res;
	}

	public function getWithdrawList($where, $limit)
	{
		$res = Db::name('withdraw_log')
			->where($where)
			->limit($limit, 8)
			->order('id desc')
			->select();
		return $res;
	}
}