<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/23
 * Time: 15:11
 */

namespace app\index\controller;


use app\common\controller\Base;

class User extends Base
{

    public function index()
    {
        return $this->fetch('index');
    }
}