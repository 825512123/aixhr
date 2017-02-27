<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/26
 * Time: 14:37
 */

namespace app\index\controller;


use app\common\controller\Base;

class Task extends Base
{
    public function price()
    {
        return $this->fetch('price');
    }
}