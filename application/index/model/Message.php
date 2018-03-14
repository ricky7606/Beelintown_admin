<?php
namespace app\index\model;

//导入模型类
use think\Model;
use app\index\model\Users;
use app\index\model\MessageDetails;

class Message extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'BLT_message';

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

	public function getMessageDetailsByUserId($userid){
		$message = new MessageDetails;
		$message_list = $message->where('userid', $userid)
		->order('create_date', 'desc')
		->paginate(20);
		return $message_list;
	}
	
	public function saveNewMessage($userid, $message, $from_userid = NULL){
		$this->messageid = uuid();
		$this->userid = $userid;
		$this->from_userid = $from_userid;
		$this->message = $message;
		$this->create_date = date('Y-m-d H:i:s',time());
		$result = $this->isUpdate(false)->save();
		if($result){
			return "ok";
		}else{
			return "发生错误";
		}
	}
	
	public function users()
    {
        return $this->belongsTo('Users','userid');
    }
}