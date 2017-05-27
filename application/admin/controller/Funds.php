<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 12:21
 */

namespace app\admin\controller;


use app\admin\model\WithdrawLog;

class Funds extends Base
{
    public function withdraw($type = 'undone')
    {
        // 获取回收员列表
        $data = $this->getWithdrawList($type);
        $this->assign('type', $type);
        $this->assign('list', $data);
        return $this->fetch();
    }

    /**
     * 获取订单列表
     * @param string $type
     * @return false|\PDOStatement|string|\think\Collection
     */
    private function getWithdrawList($type = 'undone')
    {
        $model = WithdrawLog::getInstance();
        switch ($type)
        {
            case 'undone':// 未完成
                $where['t.status'] = 2;
                $data = $model->getList($where);
                break;
            case 'end':// 已完成
                $where['t.status'] = 1;
                $data = $model->getList($where);
                break;
            case 'expired':// 已过期
                $where['t.status'] = 2;
                $where['t.recover_date'] = ['lt', time()];
                $data = $model->getList($where);
                break;
            default:
                $where['t.status'] = 0;
                $data = $model->getList($where);
        }
        return $data;
    }

    /**
     * 确认提现
     * @return \think\response\Json
     */
    public function confirmWithdraw()
    {
        $post = input("post.");
        $post['run_time'] = time();
        $res = WithdrawLog::getInstance()->confirm($post);
        if ($res > 0) {
            return json(['code' => 1, 'data' => '', 'msg' => '操作成功!']);
        }
        return json(['code' => 0, 'data' => '', 'msg' => '操作失败!请稍后再试!']);
    }

    /**
     * 确认提现
     * @return \think\response\Json
     */
    public function cancelWithdraw()
    {
        $post = input("post.");
        $post['run_time'] = time();
        $res = WithdrawLog::getInstance()->cancel($post);
        if ($res > 0) {
            return json(['code' => 1, 'data' => '', 'msg' => '操作成功!']);
        }
        return json(['code' => 0, 'data' => '', 'msg' => '操作失败!请稍后再试!']);
    }
}