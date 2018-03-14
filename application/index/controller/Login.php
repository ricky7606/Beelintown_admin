<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Db;
use think\Cookie;
use app\index\model\AdminUsers;
use app\index\model\AdminLogs;

class Login extends Controller
{
    public function index()
    {
        $this->assign('header_type','login');
        return $this->fetch();
	}

	public function getLogin(){
		$username = Request::instance()->post('username');
		$password = Request::instance()->post('password');
		if($username!="" && $password!=""){
//			//实例化模型后调用查询
			$admin = new AdminUsers;
//			//登录验证
			$result = $admin->getLogin($username, $password);
			if($result == "ok"){
				$log = new AdminLogs;
				$ip = Request::instance()->ip();
				$log->saveLog(Cookie::get('adminid'),$ip,1,"登录系统");
			}
			return $result;
		}else{
			return "数据有误，请检查后重试";
		}
	}
	
	public function Logout(){
		if(!Cookie::has('adminid')){
			return $this->redirect('/index');
		}
		$log = new AdminLogs;
		$ip = Request::instance()->ip();
		$log->saveLog(Cookie::get('adminid'),$ip,1,"退出登录");
		Cookie::delete('adminid');
		Cookie::delete('admin_username');
		Cookie::delete('admin_type');
		return $this->redirect('/index');
	}
}
