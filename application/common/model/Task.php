<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/3
 * Time: 18:19
 */

namespace app\common\model;


use think\Db;

class Task extends Base
{

    private static $_instance;

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function addTask($data)
    {
        $data = clearArray($data);//清除数组空键值对
        $data['update_time'] = time();
        return Db::name('order')->insert($data);
    }
}