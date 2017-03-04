<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/26
 * Time: 14:37
 */

namespace app\index\controller;


use app\common\controller\Base;
use app\common\model\Task;

class Order extends Base
{
    public function price()
    {
        return $this->fetch();
    }

    public function recover()
    {
        return $this->fetch();
    }

    public function add()
    {
        $post = input("post.");
        $data = clearArray($post);//清除数组空键值对
        $data['task_id'] = $this->get_task_id();
        $data['member_id'] = session('member_id');
        $data['create_time'] = time();
        $res = Task::getInstance()->insert($data);
        if ($res > 0){
            return json(['code' => 1, 'data' => '', 'msg' => '呼叫小黄人成功！']);
        } else {
            return json(['code' => 0, 'data' => '', 'msg' => '呼叫小黄人失败，请稍后再试！']);
        }
    }
}