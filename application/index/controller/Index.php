<?php
namespace app\index\controller;

use app\common\controller\Base;

class Index extends Base
{
    public function index()
    {
        return $this->fetch();
    }

    public function color()
    {
        return $this->fetch('color');
    }
}
