<?php
namespace app\index\model;

//导入模型类
use think\Model;
use app\index\model\QnasPending;

class ReplyAdditionDetails extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'BLT_ReplyAddition';

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
	
	public function saveAdditionReply($userid, $replyid, $pendingid, $content, $content_text, $thumb_img, $addition_type){
		$this->startTrans();
		$error_flag = false;
		if($addition_type == '1'){
			$pending_status = 31;
		}else{
			$pending_status = 33;
		}
		$this->additionid = uuid();
		$this->userid = $userid;
		$this->replyid = $replyid;
		$this->content = $content;
		$this->content_text = $content_text;
		$this->thumb_img = $thumb_img;
		$this->addition_type = $addition_type;
		$this->create_date = date('Y-m-d H:i:s',time());
		$result = $this->isUpdate(false)->save();
		if($result){
			$pending = new QnasPending;
			$pending_result = $pending->update(['pendingid' => $pendingid, 'status' => $pending_status]);
			if(!$pending_result){
				$error_flag = true;
			}
		}else{
			$error_flag = true;
		}
		if($error_flag){
			$this->rollBack();
			return "发生错误";
		}else{
			$user = new Users;
			$user->chkReminder($userid);
			$this->commit();
			return "ok";
		}
	}
}