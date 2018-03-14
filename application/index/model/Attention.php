<?php
namespace app\index\model;

//导入模型类
use think\Model;
use app\index\model\Transactions;
use app\index\model\Users;
use app\index\model\AttentionDetails;

class Attention extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'BLT_attention';

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

	public function getAttentionDetailsByUserId($userid){
		$attention = new AttentionDetails;
		$attention_list = $attention->where('userid', $userid)
		->order('attention_date', 'desc')
		->paginate(20);
		return $attention_list;
	}

	public function getBeAttentionDetailsByUserId($userid){
		$attention = new AttentionDetails;
		$attention_list = $attention->where('attention_userid', $userid)
		->order('attention_date', 'desc')
		->paginate(20);
		return $attention_list;
	}

	public function getAttentionByUserId($qna_userid, $userid){
		$result = $this->where('userid', $userid)
		->where('attention_userid', $qna_userid)
		->limit(1)
		->find();
		return $result;
	}

	public function saveNewAttention($userid, $attention_userid){
		$att = $this->where('userid', $userid)
		->where('attention_userid', $attention_userid)
		->limit(1)
		->find();
		if(!$att){
			$this->attentionid = uuid();
			$this->userid = $userid;
			$this->attention_userid = $attention_userid;
			$this->attention_date = date('Y-m-d H:i:s',time());
			$result = $this->isUpdate(false)->save();
			if($result){
				$user = new Users;
				$userinfo = $user->getUserDetails($userid);
				$user->addNewAtt($attention_userid);
				$message_text = "用户“<a href=\"\\index\\userreplydetail?userid=".$userid."\" target=\"_blank\">".$userinfo->username."</a>”刚刚关注了您。";
				$message = new Message;
				$message_result = $message->saveNewMessage($attention_userid, $message_text);
				return "ok";
			}else{
				return "发生错误";
			}
		}else{
			return "已经关注了该用户";
		}
	}
	
	public function deleteAttention($attentionid){
		$result = $this->where('attentionid', $attentionid)
		->delete();
		return $result;
	}

	public function users()
    {
        return $this->belongsTo('Users','userid');
    }
}