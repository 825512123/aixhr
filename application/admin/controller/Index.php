<?php
namespace app\admin\controller;

class Index extends Base
{
    public function index()
    {
        return $this->fetch();
    }

    public function login()
    {
	    $this->view->engine->layout(false);
	    return $this->fetch();
    }
}
