<?php
namespace app\index\model;

//导入模型类
use think\Model;
use app\index\model\QnasPending;
use app\index\model\QnasReplyDetails;
use app\index\model\Transactions;
use app\index\model\Users;
use app\index\model\Qnas;

class QnasReply extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'BLT_qna_reply';

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

	public function getNewReply(){
        $new_qnas = $this->with('users')->limit(10)
		->order('create_date','desc')
		->select();          // 查询所有用户的所有字段资料
        if (empty($new_qnas)) {                 // 判断是否出错
            return false;
        }
        return $new_qnas;   // 返回修改后的数据
	}
	
	public function saveReplyQna($userid, $content, $content_text, $thumb_img, $qnaid, $pendingid = ''){
		//开启事务
		$this->startTrans();
		$valuable = 0;
		$error_flag = false;
		$this->userid = $userid;
		$this->content = $content;
		if(mb_strlen($content) > 400){
			$valuable = 1;
		}
		$this->content_text = $content_text;
		$this->thumb_img = $thumb_img;
		$this->create_date = date('Y-m-d H:i:s',time());
		$new_replyid = uuid();
		$this->replyid = $new_replyid;
		$this->qnaid = $qnaid;
		$this->pendingid = $pendingid;
		$this->valuable_answer = $valuable;
		$result = $this->save();
		if($result === false){
			$error_flag = true;
		}
		
		if($pendingid != ''){
			$pending = new QnasPending;
			$result_pending = $pending->update(['pendingid' => $pendingid, 'status' => 3]);
			if($result_pending === false){
				$error_flag = true;
			}
		}
		
		$user = new Users;
		$userinfo = $user->getUserDetails($userid);
		$qna = new Qnas;
		$qnainfo = $qna->getQnaDetailsByQnaId($qnaid);
		$message_text = "用户“<a href=\"\\index\\userreplydetail?userid=".$userid."\" target=\"_blank\">".$userinfo->username."</a>”刚刚回答了您的问题：“<a href=\"\\index\\qnadetails?id=".$qnaid."\" target=\"_blank\">".$qnainfo->title."</a>”。";
		$message = new Message;
		$result_message = $message->saveNewMessage($qnainfo->userid, $message_text);
		
		if($error_flag){
			$this->rollBack();
			return "error";
		}else{
			$user = new Users;
			$user->chkReminder($userid);
			$this->commit();
			return "ok";
		}
	}

	//处理回答
//	回答流程(pending status)状态码说明：
//	0 - 待处理，通常是申请回答；
//	1 - 通过；如邀请回答以及申请通过；
//	2 - 拒绝；如邀请被拒绝以及申请被拒绝或申请通过后拒绝回答；
//	3 - 已回答；
//	31 - 要求补充回答
//	32 - 拒绝补充回答
//	4 - 回答被采纳；
//	5 - 回答被拒绝；
//	6 - 回答被拒绝后进入待处理状态；
//	7 - 进入仲裁状态；
//	8 - 仲裁结束；
//	交易记录(Transaction)状态码说明：
//	0 - 注册奖励；
//	1 - 问答冻结；
//	2 - 问答佣金冻结；
//	3 - 问答解冻；
//	4 - 问答佣金解冻；
//	5 - 问答支付；
//	6 - 问答悬赏佣金扣除；
//	7 - 问答入账佣金扣除；
//	8 - 问答分享奖励入账；
//	9 - 问答入账；
//	10 - 问答仲裁处理解冻；
//	11 - 问答仲裁处理支付；
//	12 - 问答仲裁处理入账；
//	13 - 提现冻结；
//	14 - 提现成功（扣除）；
//	15 - 提现失败（解冻返回）；
//	99 - 其它；
	public function updateReply($replyid, $reply_status, $share){
		$error_flag = false;
		$this->startTrans();
		
		$reply = new QnasReplyDetails;
		$reply_info = $reply->getReplyDetailsByReplyId($replyid);
		if($reply_info){
			$payer_userid = $reply_info->qna_userid;
			$payee_userid = $reply_info->userid;
			$qnaid = $reply_info->qnaid;
			$coins = $reply_info->qna_coins;
			$pendingid = $reply_info->pendingid;
			$payee_coins_before = $reply_info->reply_user_coins;
			$pending_status_before = $reply_info->pending_status;
			
					
			$transaction = new Transactions;
			if($reply_status == 1){
				//接受回答创建支付记录及入账记录，pending状态更新为4，拒绝回答将pending状态更新为6，等待回答者处理；
				$pending_status = 4;
				//问答支付
				if($pending_status_before == 3){	//检查pending状态
				$result_trans = $transaction->saveTransaction($payer_userid, $coins, 5, $qnaid, $payee_userid, $pendingid);
				if($result_trans === false){
					$error_flag = true;
				}
				}
				//问答悬赏佣金支付
				if($pending_status_before == 3){	//检查pending状态
				$commission = bcmul($coins, 0.1, 8);
				$result_trans = $transaction->saveTransaction($payer_userid, $commission, 6, $qnaid, $payee_userid, $pendingid);
				if($result_trans === false){
					$error_flag = true;
				}
				}
				//如果分享答案，则返回9.5%佣金
				if($share==1){
				if($pending_status_before == 3){	//检查pending状态
				$refund = bcmul($coins, 0.095, 8);
				$result_trans = $transaction->saveTransaction($payer_userid, $refund, 8, $qnaid, $payee_userid, $pendingid);
				if($result_trans === false){
					$error_flag = true;
				}
				}
				}
				//问答入账
				if($pending_status_before == 3){	//检查pending状态
				$coins_after = bcadd($payee_coins_before, $coins, 8);
				$result_trans = $transaction->saveTransaction($payee_userid, $coins, 9, $qnaid, $payer_userid, $pendingid);
				//通知回答者
				$message_text = "用户“<a href=\"\\index\\userreplydetail?userid=".$payer_userid."\" target=\"_blank\">".$reply_info->qna_username."</a>”刚刚接受了您关于问题：“<a href=\"\\index\\qnadetails?id=".$reply_info->qnaid."\" target=\"_blank\">".$reply_info->title."</a>”的答案，并支付给您悬赏：".floatval($reply_info->qna_coins)."比邻币。注意：系统将扣除0.5%的佣金，请在<a href=\"\\index\\usercoins\">我的比邻币</a>中查看详情。";
				$message = new Message;
				$result_message = $message->saveNewMessage($payee_userid, $message_text);
				if($result_trans === false || $result_message === false){
					$error_flag = true;
				}
				}
				//问答入账佣金支付
				if($pending_status_before == 3){	//检查pending状态
				$commission = bcmul($coins, 0.005, 8);
				$result_trans = $transaction->saveTransaction($payee_userid, $commission, 7, $qnaid, $payer_userid, $pendingid);
				if($result_trans === false){
					$error_flag = true;
				}
				}
			}else{
				//接受回答创建支付记录及入账记录，pending状态更新为4，拒绝回答将pending状态更新为6，等待回答者处理；
				$pending_status = 6;
				//问答解冻
				if($pending_status_before == 3){	//检查pending状态
				$result_trans = $transaction->saveTransaction($payer_userid, $coins, 3, $qnaid, $payee_userid, $pendingid);
				if($result_trans === false){
					$error_flag = true;
				}
				}
				//问答佣金解冻
				if($pending_status_before == 3){	//检查pending状态
				$commission = bcmul($coins, 0.1, 8);
				$result_trans = $transaction->saveTransaction($payer_userid, $commission, 4, $qnaid, $payee_userid, $pendingid);
				if($result_trans === false){
					$error_flag = true;
				}
				}
				//通知回答者
				$message_text = "用户“<a href=\"\\index\\userreplydetail?userid=".$payer_userid."\" target=\"_blank\">".$reply_info->qna_username."</a>”刚刚拒绝了您关于问题：“<a href=\"\\index\\qnadetails?id=".$reply_info->qnaid."\" target=\"_blank\">".$reply_info->title."</a>”的答案。如有异议，您可以在十天内提出仲裁。";
				$message = new Message;
				$result_message = $message->saveNewMessage($payee_userid, $message_text);
			}
			if($pendingid != ''){
				if($pending_status_before == 3){	//检查pending状态
				$pending = new QnasPending;
				$result_pending = $pending->update(['pendingid' => $pendingid, 'status' => $pending_status, 'share' => $share]);
				if($result_pending === false){
					$error_flag = true;
				}
				}
			}
		}else{
			$error_flag = true;
		}

		if($error_flag){
			$this->rollBack();
			return "error";
		}else{
			$this->commit();
			$user = new Users;
			$user->chkReminder($payer_userid);
			$user->addAnswerReminder($payee_userid);
			return "ok";
		}
	}
	
	public function shareReply($replyid){
		$error_flag = false;
		$this->startTrans();
		
		$reply = new QnasReplyDetails;
		$reply_info = $reply->getReplyDetailsByReplyId($replyid);
		if($reply_info){
			$payer_userid = $reply_info->qna_userid;
			$payee_userid = $reply_info->userid;
			$qnaid = $reply_info->qnaid;
			$coins = $reply_info->qna_coins;
			$pendingid = $reply_info->pendingid;
			$payee_coins_before = $reply_info->reply_user_coins;
			$pending_status_before = $reply_info->pending_status;
			
					
			$transaction = new Transactions;
			if($pending_status_before == 4){	//如果已接受回答
				$refund = bcmul($coins, 0.095, 8);
				$result_trans = $transaction->saveTransaction($payer_userid, $refund, 8, $qnaid, $payee_userid, $pendingid);
				if($result_trans === false){
					$error_flag = true;
				}
			}else{
				$error_flag = true;
			}
			if($pendingid != ''){
				if($pending_status_before == 4){	//检查pending状态
					$pending = new QnasPending;
					$result_pending = $pending->update(['pendingid' => $pendingid, 'share' => 1]);
					if($result_pending === false){
						$error_flag = true;
					}
				}
			}
		}
		if($error_flag){
			$this->rollBack();
			return "发生错误";
		}else{
			$this->commit();
			return "ok";
		}
	}

	public function getReplyDetailsByReplyId($replyid){
        $reply = $this->where('replyid',$replyid)
		->field('replyid,qnaid,userid,content,content_text,thumb_img,status')
		->limit(1)
		->find();          // 查询用户
        if (empty($reply)) {                 // 判断是否出错
            return false;
        }
        return $reply;   // 返回修改后的数据
	}

	public function users()
    {
        return $this->belongsTo('Users','userid');
    }
}