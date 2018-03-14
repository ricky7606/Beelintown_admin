<?php
namespace app\index\model;

//导入模型类
use think\Model;
use app\index\model\QnasPending;
use app\index\model\Transactions;
use app\index\model\Users;
use app\index\model\Message;

class QnasReplyDetails extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'view_QnaReplyDetails';

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
	
	public function getReplyDetailsByUserId($userid){
        $new_reply = $this->where('userid', $userid)
		->order('create_date','desc')
		->select();          // 查询所有用户的所有字段资料
        if (empty($new_reply)) {                 // 判断是否出错
            return false;
        }
        return $new_reply;   // 返回修改后的数据
	}

	public function getReplyDetails($valuable = false, $limit = NULL){
		if($limit == NULL){
			$limit = 100000;
		}
		if($valuable){
			$reply = $this->where('valuable_answer', 1)
			->limit($limit)
			->order('create_date','desc')
			->paginate(10);          // 查询所有用户的所有字段资料
		}else{
			$reply = $this->limit($limit)
			->order('create_date','desc')
			->paginate(10);          // 查询所有用户的所有字段资料
		}
        if (empty($reply)) {                 // 判断是否出错
            return false;
        }
        return $reply;   // 返回修改后的数据
	}

	public function getReplyDetailsByQnaId($qnaid, $limit = NULL, $valuable = false){
		if($limit == NULL){
			$limit = 100000;
		}
		if($valuable){
			$reply = $this->where('qnaid',$qnaid)
			->where('valuable_answer', 1)
			->limit($limit)
			->order('create_date','desc')
			->paginate(5,false,['query'=>request()->param()]);          
		}else{
			$reply = $this->where('qnaid',$qnaid)
			->limit($limit)
			->order('create_date','desc')
			->paginate(5,false,['query'=>request()->param()]);          
		}
        if (empty($reply)) {                 // 判断是否出错
            return false;
        }
        return $reply;   // 返回修改后的数据
	}

	public function getTopReplyDetailsByReplyId($replyid, $valuable = false){
		if($valuable){
			$reply = $this->where('replyid',$replyid)
			->where('valuable_answer', 1)
			->limit(1)
			->order('create_date','desc')
			->find();          // 查询所有用户的所有字段资料
		}else{
			$reply = $this->where('replyid',$replyid)
			->limit(1)
			->order('create_date','desc')
			->find();          // 查询所有用户的所有字段资料
		}
        if (empty($reply)) {                 // 判断是否出错
            return false;
        }
        return $reply;   // 返回修改后的数据
	}

	public function getReplyDetailsByReplyId($replyid){
        $reply = $this->where('replyid',$replyid)
		->find();          // 查询所有用户的所有字段资料
        if (empty($reply)) {                 // 判断是否出错
            return false;
        }
        return $reply;   // 返回修改后的数据
	}

	public function users()
    {
        return $this->belongsTo('Users','userid');
    }

	public function getUpdateReplyDetails($valuable = false, $limit = 10){
		if($valuable){
			$qna_reply_list = $this->where('valuable_answer', 1)
			->where('share', 1)
			->field('replyid,qnaid,qna_userid,userid,qna_username,reply_username,qna_coins,content,content_text,title,create_date,qna_personal_pic,reply_personal_pic')
			->group('qnaid')
			->order('max(create_date)', 'desc')
			->paginate($limit);
		}else{
			$qna_reply_list = $this->group('qnaid')
			->field('replyid,qnaid,qna_userid,userid,qna_username,reply_username,qna_coins,content,content_text,title,create_date,qna_personal_pic,reply_personal_pic')
			->order('max(create_date)', 'desc')
			->paginate($limit);
		}
        if (empty($qna_reply_list)) {                 // 判断是否出错
            return false;
        }
        return $qna_reply_list;   // 返回修改后的数据
	}

	public function getUpdateReplyDetailsByUserId($userid, $vauable = false, $limit = 10){
		if($vauable){
			$qna_reply_list = $this->where('userid', $userid)
			->where('valuable_answer', 1)
			->field('replyid,qnaid,qna_userid,userid,qna_username,reply_username,qna_coins,content,content_text,title,create_date,qna_personal_pic,reply_personal_pic')
			->group('qnaid')
			->order('max(create_date)', 'desc')
			->paginate($limit);
		}else{
			$qna_reply_list = $this->where('userid', $userid)
			->group('qnaid')
			->paginate($limit);
		}
        if (empty($qna_reply_list)) {                 // 判断是否出错
            return false;
        }
        return $qna_reply_list;   // 返回修改后的数据
	}
	
	public function getArbitrateList($status){
		$arbitrate_list = $this->where('pending_status', $status)
		->order('update_date', 'desc')
		->paginate(20);
        if (empty($arbitrate_list)) {                 // 判断是否出错
            return false;
        }
        return $arbitrate_list;   // 返回修改后的数据
	}
	
	public function getPendingArbitrateCount(){
		$arbitrate_count = $this->where('pending_status', 7)
		->field('count(*) as arbitrate_count')
		->find();
        if (empty($arbitrate_count)) {                 // 判断是否出错
            return false;
        }
        return $arbitrate_count;   // 返回修改后的数据
	}
	
	public function doArbitrate($replyid, $user_pay, $system_pay, $comment){
		if($user_pay==''){$user_pay=0;}
		if($system_pay==''){$system_pay=0;}
		$pay_coins = bcadd($user_pay,$system_pay,8);
		$qna_user_commission = bcmul($user_pay, 0.1,8);
		$reply_user_commission = bcmul($pay_coins, 0.05,8);
		$tran = new Transactions;
		$message = new Message;
		$reply_info = $this->getReplyDetailsByReplyId($replyid);
		$this->startTrans();
		$error_flag = false;
		
		//提问者处理部分
		$message_text = "您的问题 <a href=\"/index/qnareply?id=".$reply_info->replyid."\" target=\"_blank\">“".$reply_info->title."”</a> 已仲裁处理完毕。";
		//如果提问者需要支付
		if($user_pay>0){
			//仲裁支付
			$result = $tran->saveTransaction($reply_info->qna_userid, $user_pay, 11, $reply_info->qnaid, $reply_info->userid);
			if(!$result){$error_flag=true;}
			//悬赏佣金支付
			$result = $tran->saveTransaction($reply_info->qna_userid, $qna_user_commission, 6, $reply_info->qnaid, $reply_info->userid);
			if(!$result){$error_flag=true;}
			$message_text .= "仲裁结果：您需要支付".floatval($user_pay)."比邻币给回答者。详情请查看<a href=\"\\index\\usercoins\">比邻币交易记录</a>。";
		}else{
			$message_text .= "仲裁结果：您不需要支付给回答者。";
		}
		//如果提问者是部分支付或者无需支付
		if(bcsub($reply_info->qna_coins,$user_pay,8)>0){
			//剩余冻结悬赏解冻退还
			$result = $tran->saveTransaction($reply_info->qna_userid, bcsub($reply_info->qna_coins,$user_pay,8), 3, $reply_info->qnaid, $reply_info->userid);
			if(!$result){$error_flag=true;}
			//剩余冻结佣金解冻退还
			$result = $tran->saveTransaction($reply_info->qna_userid, bcmul(bcsub($reply_info->qna_coins,$user_pay,8),0.1,8), 4, $reply_info->qnaid, $reply_info->userid);
			if(!$result){$error_flag=true;}
		}
		if($comment != ''){
			$message_text .= "仲裁说明：“".$comment."”";
		}
		$message_text .= " 如有疑问请联系平台。";
		$result_message = $message->saveNewMessage($reply_info->qna_userid, $message_text);
		if(!$result_message){$error_flag=true;}
		
		//回答者处理部分
		$message_text = "您关于问题 <a href=\"/index/qnareply?id=".$reply_info->replyid."\" target=\"_blank\">“".$reply_info->title."”</a> 的仲裁申请已处理完毕。";
		//如果回答者有悬赏入账
		if($pay_coins>0){
			//仲裁悬赏入账
			$result = $tran->saveTransaction($reply_info->userid, $pay_coins, 12, $reply_info->qnaid, $reply_info->qna_userid);
			if(!$result){$error_flag=true;}
			//获得悬赏佣金扣除
			$result = $tran->saveTransaction($reply_info->userid, $reply_user_commission, 7, $reply_info->qnaid, $reply_info->qna_userid);
			if(!$result){$error_flag=true;}
			$message_text .= "仲裁结果：您获得悬赏".floatval($pay_coins)."，并已为您扣除相应佣金（%5），详情请查看<a href=\"\\index\\usercoins\">比邻币交易记录</a>。";
		}else{
			$message_text .= "仲裁结果：您未能获得悬赏。";
		}
		if($comment != ''){
			$message_text .= "仲裁说明：“".$comment."”";
		}
		$message_text .= " 如有疑问请联系平台。";
		$result_message = $message->saveNewMessage($reply_info->userid, $message_text);
		if(!$result_message){$error_flag=true;}
		 
		//更新Pending记录状态
		$pending = new QnasPending;
		$result = $pending->update(['pendingid' => $reply_info->pendingid, 'status' => 8, 'arbitrate_coins' => $pay_coins, 'arbitrate_qnauser_coins' => $user_pay, 'arbitrate_comment' => $comment]);
		if(!$result){$error_flag=true;}
		
		if($error_flag){
			$tran->rollBack();
			return "仲裁处理失败";
		}else{
			$tran->commit();
			return "ok";
		}
	}
}