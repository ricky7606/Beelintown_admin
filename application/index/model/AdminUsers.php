<?php
namespace app\index\model;

//导入模型类
use think\Model;
use think\Cookie;
use app\index\model\Transactions;
use app\index\model\UserTags;
use app\index\model\QnasPending;
use app\index\model\AttentionDetails;

class AdminUsers extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'BLT_admin_user';
	
	//protected $resultSetType = 'collection';

	//在子类重写父类的初始化方法initialize()
	protected function initialize(){
		
	  //继承父类中的initialize()
		parent::initialize();
		
	   //初始化数据表字段信息	
	   $this->field = $this->db()->getTableInfo('', 'fields');  
	
	   //初始化数据表字段类型
	   $this->type = $this->db()->getTableInfo('', 'type'); 
	
	   
	   $this->pk = $this->db()->getTableInfo('', 'pk');     
		}

	public function getUserDetails($adminid){
        $admin = $this->where('adminid',$adminid)
		->find();          
        if (empty($admin)) {                 // 判断是否出错
            return false;
        }
        return $admin;   // 返回修改后的数据
	}
	
	public function getAdminList(){
		$list = $this->order('username','asc')
		->paginate(20);
		return $list;
	}
	
	public function getLogin($username, $password){
		// 验证用户Bcrypt加密后的密码
		$chkPwd = $this->where('username',$username)
			->field('password,adminid,username,realname,admin_type,enable')
			->limit(1)
			->find();
		if($chkPwd){
			if(password_verify($password,$chkPwd->password)){
				if($chkPwd->enable==0){
					return "disabled";
				}else{
					Cookie::set('adminid',$chkPwd->adminid,86400);
					Cookie::set('admin_username',$chkPwd->username,86400);
					Cookie::set('admin_realname',$chkPwd->realname,86400);
					Cookie::set('admin_type',(string)$chkPwd->admin_type,86400);
					return "ok";
				}
			}else{
				return "password error";
			}
		}else{
			return "username error";
		}
	}
	
	public function changePassword($adminid, $password, $new_password){
		$result = $this->where('adminid', $adminid)
		->limit(1)
		->find();
		if($result){
			$current_pwd = $result->password;
			if(password_verify($password,$current_pwd)){
				$new_password = password_hash($new_password,PASSWORD_BCRYPT);
				$result_pwd = $this->update(['adminid' => $adminid, 'password' => $new_password]);
				if($result_pwd){
					return "ok";
				}else{
					return "密码修改失败";
				}
			}else{
				return "登录密码错误";
			}
		}else{
			return "数据错误";
		}
	}
	
	public function updateUser($adminid, $realname, $username, $password, $admin_type, $enable){
		if($username != ''){
			$result = $this->where('adminid','neq',$adminid)
			->where('username', $username)
			->limit(1)
			->find();
			if($result){
				return "该用户名已经被使用！";
			}else{
				$this->username = $username;
			}
		}
		$this->adminid = $adminid;
		if($realname != ''){
			$this->realname = $realname;
		}
		if($password != ''){
			$new_password = password_hash($password,PASSWORD_BCRYPT);
			$this->password = $new_password;
		}
		if($admin_type != ''){
			$this->admin_type = $admin_type;
		}
		if($enable != ''){
			$this->enable = $enable;
		}
		$result = $this->isUpdate(true)->save();
		if($result === false){
			return "数据错误";
		}else{
			return "ok";
		}
	}

	public function createUser($realname, $username, $password, $admin_type, $enable){
		$result = $this->where('username', $username)
		->limit(1)
		->find();
		if($result){
			return "该用户名已经被使用！";
		}else{
			$this->username = $username;
		}
		$this->adminid = uuid();
		$this->realname = $realname;
		$this->username = $username;
		$new_password = password_hash($password,PASSWORD_BCRYPT);
		$this->password = $new_password;
		$this->admin_type = $admin_type;
		$this->enable = $enable;
		$this->create_date = date('Y-m-d H:i:s',time());
		$result = $this->isUpdate(false)->save();
		if($result){
			return "ok";
		}else{
			return "数据错误";
		}
	}
}