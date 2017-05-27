<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/2
 * Time: 16:03
 */

namespace app\admin\controller;


use app\admin\model\AdminAdmin;
use app\admin\model\AdminRole;
use app\admin\model\AdminUser;
use app\admin\model\RecoverPrice;

class Admin extends Base
{
	/**
	 * 企业列表
	 * @return mixed
	 */
	public function index()
	{
		$list = AdminAdmin::getInstance()->getList();
		$this->assign('list', $list);
		return $this->fetch();
	}

	/**
	 * 新增企业
	 * @param string $id
	 * @return mixed|\think\response\Json
	 */
	public function add($id = '')
	{
		if(request()->isPost()) {
			$post = input("post.");
			$post = clearArray($post);//清除数组空键值对
			if(AdminAdmin::getInstance()->editAdmin($post)) {
				return json(['code' => 1, 'data' => '', 'msg' => '提交成功!']);
			} else {
				return json(['code' => 0, 'data' => '', 'msg' => '提交失败!请稍后再试!']);
			}
		} else {
			$admin = [];
			if($id > 0) {
				$admin = AdminAdmin::getInstance()->getInfo(['id' => $id]);
			}
			$this->assign('admin', $admin);
			return $this->fetch();
		}
	}

	/**
	 * 删除企业
	 * @return \think\response\Json
	 */
	public function down()
	{
		if(request()->isPost()) {
			$post = input("post.");
			$data['id'] = $post['id'];
			$data['status'] = '0';
			$res = AdminAdmin::getInstance()->editAdmin($data);
			if ($res > 0) {
				return json(['code' => 1, 'data' => '', 'msg' => '操作成功!']);
			}
			return json(['code' => 0, 'data' => '', 'msg' => '操作失败!请稍后再试!']);
		} else {
			$this->redirect('/admin/admin');
		}
	}

	/**
	 * 新增/编辑员工
	 * @param string $id
	 * @return mixed
	 */
	public function admin_add($id = '')
	{
		if(request()->isPost()) {
			$post = input("post.");
			$data = clearArray($post);//清除数组空键值对
			$data = $this->getUserData($data);
			if(AdminUser::getInstance()->editUser($data)) {
				$this->success('操作成功！', 'admin/admin/user');
			}
		} else {
			$user = [];
			if($id > 0) {
				$user = AdminUser::getInstance()->getFind(['id' => $id]);
			}
			$this->assign('user', $user);
			$list = AdminAdmin::getInstance()->getList(['status' => 1]);
			$roleList = AdminRole::getInstance()->getList(['status' => 1]);
			$userList = AdminUser::getInstance()->getList(['aa.status' => 1]);
			$this->assign('list', $list);
			$this->assign('roleList', $roleList);
			$this->assign('userList', $userList);
			return $this->fetch('admin-add');
		}
	}

	public function admin_down()
	{
		if(request()->isPost()) {
			$post = input("post.");
			$data['id'] = $post['id'];
			$data['status'] = '0';
			$res = AdminUser::getInstance()->editUser($data);
			if ($res > 0) {
				return json(['code' => 1, 'data' => '', 'msg' => '操作成功!']);
			}
			return json(['code' => 0, 'data' => '', 'msg' => '操作失败!请稍后再试!']);
		} else {
			$this->redirect('/admin/admin/user');
		}
	}

	/**
	 * 员工列表
	 * @return mixed
	 */
	public function user()
	{
		$list = AdminUser::getInstance()->getList();
		$this->assign('list', $list);
		return $this->fetch();
	}

	/**
	 * 处理并返回员工数据
	 * @param $data
	 * @return mixed
	 */
	public function getUserData($data)
	{
		if(!isset($data['name']) || !isset($data['mobile']) || !isset($data['idcard']) || !isset($data['address'])) {
			$this->error('姓名、电话、身份证号、地址均不能为空！');
		}
		if(isset($data['joinday'])) {
			$data['joinday'] = adminLTEToTime($data['joinday']);
		} else {
			$data['joinday'] = time();
		}
		if(isset($data['birthday'])) {$data['birthday'] = adminLTEToTime($data['birthday']);}
		$password = substr($data['mobile'], -6);
		$data['password'] = md5(md5($password) . config('data_auth_key'));
		$data['create_time'] = time();
		return $data;
	}

    /**
     * 回收价格列表
     * @return mixed
     */
	public function price($id = '')
    {
        if($id) {
            $where = ['pid' => $id];
        } else {
            $where = ['pid' => 0];
        }
        $list = RecoverPrice::getInstance()->getList($where);
        $this->assign('pid', $where['pid']);
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 新增回收类型及价格
     * @param string $id
     * @return mixed
     */
    public function price_add($id = '')
    {
        if(request()->isPost()) {
            $post = input("post.");
            $data = clearArray($post);
            if(RecoverPrice::getInstance()->edit($data)) {
                return json(['code' => 1, 'data' => '', 'msg' => '成功!']);
            } else {
                return json(['code' => 0, 'data' => '', 'msg' => '失败!']);
            }
        }
        if($id) {
            $data = RecoverPrice::getInstance()->getInfo(['id' => $id]);
            $this->assign('pageTitle', '编辑类型价格');
        } else {
            $data['id'] = 0;
            $data['pid'] = 0;
            $data['name'] = '';
            $data['min'] = 0;
            $data['max'] = 0;
            $data['price'] = 0;
            $data['status'] = 1;
            $this->assign('pageTitle', '新增类型价格');
        }
        $list = RecoverPrice::getInstance()->getList(['pid' => 0]);
        $this->assign('list', $list);
        $this->assign('info', $data);
        return $this->fetch('price-add');
    }

    /**
     * 删除价格
     * @return \think\response\Json
     */
    public function price_del()
    {
        $post = input("post.");
        $data['id'] = $post['id'];
        $data['status'] = '0';
        $res = RecoverPrice::getInstance()->edit($data);
        if ($res > 0){
            return json(['code' => 1, 'data' => '', 'msg' => '操作成功!']);
        }
        return json(['code' => 0, 'data' => '', 'msg' => '操作失败!请稍后再试!']);
    }
}