<?php
namespace app\index\model;
use app\index\model\AdminLogDetails;

//导入模型类
use think\Model;

class AdminLogs extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'BLT_admin_log';

	//在子类重写父类的初始化方法initialize()
	protected function initialize(){
		
	  //继承父类中的initialize()
		parent::initialize();
		
	   //初始化数据表字段信息	
	   $this->field = $this->db()->getTableInfo('', 'fields');  
	
	   //初始化数据表字段类型
	   $this->type = $this->db()->getTableInfo('', 'type'); 
	
	   //初始化数据表主键
	   $this->pk = $this->db()->getTableInfo('', 'pk');     
		
		
		}

	public function getLogs($adminid = '', $export_flag = false){
		$detail = new AdminLogDetails;
		if(!$export_flag){
			if($adminid != ''){
				$log_list = $detail->where('adminid', $adminid)
				->order('log_date', 'desc')
				->paginate(20,false,['query'=>request()->param()]);
			}else{
				$log_list = $detail->order('log_date', 'desc')
				->paginate(20,false,['query'=>request()->param()]);
			}
		}else{
			if($adminid != ''){
				$log_list = $detail->where('adminid', $adminid)
				->order('log_date', 'desc')
				->select();
			}else{
				$log_list = $detail->order('log_date', 'desc')
				->select();
			}
		}
		if($log_list){
			foreach($log_list as $log){
				$log->log_type_desc = $this->getLogTypeDesc($log->log_type);
			}
		}
		return $log_list;
	}
	
	public function saveLog($adminid, $log_ip, $log_type, $log_detail){
		$this->logid = uuid();
		$this->adminid = $adminid;
		$this->log_ip = $log_ip;
		$this->log_type = $log_type;
		$this->log_detail = $log_detail;
		$this->log_date = date('Y-m-d H:i:s',time());
		$result = $this->isUpdate(false)->save();
		if($result){
			return "ok";
		}else{
			return "发生错误";
		}
	}

//操作类型：
//1 - 登录；
//2 - 仲裁处理；
//3 - 举报处理；
//4 - 提现处理；
//5 - 广告管理；
//6 - 账户管理；
//7 - 其它；	
	public function getLogTypeDesc($log_type){
		switch($log_type){
			case 1:
				return "登录";
				break;
			case 2:
				return "仲裁处理";
				break;
			case 3:
				return "举报处理";
				break;
			case 4:
				return "提现处理";
				break;
			case 5:
				return "广告管理";
				break;
			case 6:
				return "账户管理";
				break;
			case 7:
				return "其它";
				break;
		}
	}
}